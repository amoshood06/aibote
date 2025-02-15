<?php
// Database configuration
$host = '';
$dbname = 'mfyokfmh_aibot';
$db_username = 'mfyokfmh_aibot';
$db_password = 'mfyokfmh_aibot';

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Start a transaction
    $pdo->beginTransaction();

    // Get all active investments
    $stmt = $pdo->query("SELECT i.id, i.user_id, i.amount, i.weekly_return_rate, u.balance 
                         FROM investments i 
                         JOIN users u ON i.user_id = u.id");

    while ($investment = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $return_amount = $investment['amount'] * ($investment['weekly_return_rate'] / 100);
        $new_balance = $investment['balance'] + $return_amount;

        // Update user balance
        $update_stmt = $pdo->prepare("UPDATE users SET balance = :balance WHERE id = :user_id");
        $update_stmt->execute([
            ':balance' => $new_balance,
            ':user_id' => $investment['user_id']
        ]);

        // Log the return
        $log_stmt = $pdo->prepare("INSERT INTO return_logs (investment_id, user_id, return_amount, date) 
                                   VALUES (:investment_id, :user_id, :return_amount, NOW())");
        $log_stmt->execute([
            ':investment_id' => $investment['id'],
            ':user_id' => $investment['user_id'],
            ':return_amount' => $return_amount
        ]);

        echo "Credited user ID {$investment['user_id']} with $" . number_format($return_amount, 2) . 
             ". New balance: $" . number_format($new_balance, 2) . "\n";
    }

    // Commit the transaction
    $pdo->commit();

    echo "Weekly returns credited successfully.\n";

} catch (PDOException $e) {
    // Rollback the transaction if something failed
    $pdo->rollback();
    echo "ERROR: Could not credit weekly returns. " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

