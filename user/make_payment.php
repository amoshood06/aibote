<?php
session_start();
include '../db/db_connection.php';
include_once './vendor/autoload.php';
use CoinRemitter\CoinRemitter;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];
    $coin = 'BTC'; // Change this to your preferred cryptocurrency

    // CoinRemitter API Key (Use your secure API key)
    $api_key = 'wkey_ErFBuYPp9XQ33xw';
    $password = 'Kolade123@';

    // API Request to CoinRemitter
    $data = [
        'api_key' => $api_key,
        'password' => $password,
        'amount' => $amount,
        'currency' => 'USD',
        'name' => 'User-' . $user_id,
        'expire_time' => 1440, // 1-hour expiry
        'notify_url' => 'https://bothighstock.com/payment_callback.php',
    ];

    $ch = curl_init('https://coinremitter.com/api/v3/' . strtolower($coin) . '/create-invoice');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['flag']) && $result['flag'] === 1) {
        // Insert transaction as 'Pending'
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_type, status) VALUES (?, ?, ?, ?)");
        $transaction_type = "Crypto Payment";
        $status = "Pending";
        $stmt->execute([$user_id, $amount, $transaction_type, $status]);

        echo "Payment request created. Pay using this link: <a href='" . $result['data']['url'] . "' target='_blank'>Click here to pay</a>";
    } else {
        echo "Error: " . $result['msg'];
    }
}
?>
