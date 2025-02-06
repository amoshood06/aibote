<?php
include '../db/db_connection.php';

// Read incoming JSON
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $invoice_id = $data['invoice_id'];
    $status = $data['status']; // 'Paid' or 'Failed'

    // Update transaction record
    $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
    $stmt->execute([$status, $invoice_id]);

    echo json_encode(["message" => "Transaction updated successfully"]);
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
