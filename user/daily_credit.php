<?php
include '../db/db_connection.php';

$current_date = date('Y-m-d');

// Get all active investments that have not expired
$sql = "SELECT investments.id, investments.user_id, investments.weekly_return, investments.end_date, users.balance 
        FROM investments 
        JOIN users ON investments.user_id = users.id
        WHERE investments.end_date >= ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$current_date]);
$investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($investments as $investment) {
    $investment_id = $investment['id'];
    $user_id = $investment['user_id'];
    $weekly_return = $investment['weekly_return'];
    $current_balance = $investment['balance'];

    // Calculate daily return
    $daily_return = $weekly_return / 7;

    // Update user's balance
    $new_balance = $current_balance + $daily_return;
    $update_balance_sql = "UPDATE users SET balance = ? WHERE id = ?";
    $stmt = $pdo->prepare($update_balance_sql);
    $stmt->execute([$new_balance, $user_id]);

    // Log transaction
    $log_sql = "INSERT INTO transactions (user_id, investment_id, amount, transaction_type, created_at) 
                VALUES (?, ?, ?, 'Daily Investment Credit', NOW())";
    $stmt = $pdo->prepare($log_sql);
    $stmt->execute([$user_id, $investment_id, $daily_return]);
}

echo "Daily investment credit process completed.";
?>
