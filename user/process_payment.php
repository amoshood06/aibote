<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require "../db/db_connection.php"; 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Session error: User not logged in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_SESSION['user_id'];
    $amount = $_POST['amount'];

    // Validate amount
    if ($amount <= 0) {
        die("Invalid amount entered.");
    }

    $apiKey = "5Q7WF9H-K4QM9AY-JYY1W3M-N430XMQ"; 
    $url = "https://api.nowpayments.io/v1/payment";
    $orderID = uniqid(); 
    $ipnUrl = "https://bothighstock.com/ipn.php"; // Your IPN callback URL

    // Data to send to NOWPayments API
    $data = [
        "price_amount" => $amount,
        "price_currency" => "USD",
        "pay_currency" => "BTC",
        "order_id" => $orderID,
        "ipn_callback_url" => $ipnUrl,
    ];

    // Send request to NOWPayments API
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-api-key: $apiKey",
        "Content-Type: application/json",
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $responseData = json_decode($response, true);

    // Check if the request was successful
    if ($httpCode != 201) {
        echo "API Error: HTTP Code " . $httpCode . "<br>";
        echo "Response: " . $response;
        exit();
    }

    // Check if pay_address is in the response
    if (isset($responseData['pay_address'])) { 
        $paymentAddress = $responseData['pay_address']; 
        $paymentID = $responseData['payment_id']; // Get payment ID from response

        // Insert transaction into database using `$pdo`
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, currency, status, txn_id, pay_address, payment_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userID, $amount, "BTC", "waiting", $orderID, $paymentAddress, $paymentID]);

        // Redirect user to the BTC payment address
        header("Location: " . $paymentAddress);
        exit();
    } else {
        echo "Error: Payment address not found.";
    }
}
?>
