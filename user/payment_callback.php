<?php
include '../db/db_connection.php';

// Read incoming JSON
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $invoice_id = $data['invoice_id'];
    $status = $data['status']; // 'Paid' or 'Failed'
    $user_id = $data['user_id']; // User ID (assumed to be sent with the callback)
    $amount = $data['amount']; // Amount paid (assumed to be sent with the callback)

    // If payment is successful, update the user's balance
    if ($status === 'Paid') {
        // Update user balance
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);

        // Record the successful transaction in the transactions table
        $transaction_type = 'Payment Received'; // Example: Can be customized as needed
        $transaction_status = 'Success'; // Transaction successful
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_type, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $amount, $transaction_type, $transaction_status]);

        echo json_encode(["message" => "Transaction updated and balance updated successfully."]);
    } else {
        // If the payment failed, record it as a failed transaction
        $transaction_status = 'Failed';
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_type, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $amount, 'Payment Failed', $transaction_status]);

        echo json_encode(["message" => "Transaction failed"]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
