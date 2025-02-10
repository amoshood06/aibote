<?php
require "./db/db_connection.php"; 

$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

file_put_contents("ipn_log.txt", $payload . "\n", FILE_APPEND);

if (isset($data['payment_status']) && $data['payment_status'] === "finished") {
    $orderID = $data['order_id'];
    $amount = $data['price_amount'];

    $stmt = $conn->prepare("SELECT user_id FROM transactions WHERE txn_id = ?");
    $stmt->execute([$orderID]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        $userID = $transaction['user_id'];

        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $userID]);

        $stmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE txn_id = ?");
        $stmt->execute([$orderID]);

        file_put_contents("payment_success.txt", "Payment received for Order ID: $orderID (User ID: $userID)\n", FILE_APPEND);
    }
}
?>
