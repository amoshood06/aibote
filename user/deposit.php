<?php
// Start the session
session_start(); // Starts the session and enables session variables

include '../db/db_connection.php'; // Include database connection

// Email Configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';


// Start the session
session_start();  // Make sure this line is inside the PHP opening tag

// Get the logged-in user ID (assume user session is set)
$user_id = $_SESSION['user_id'];

// Check if an error message is passed via query string
$error_message = '';
if (isset($_GET['error']) && $_GET['error'] == 'low_balance') {
    $error_message = 'You do not have enough balance to make this deposit.';
}

// Fetch user balance from the database
$sql = "SELECT balance FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_balance = $user ? $user['balance'] : 0;

// Process the deposit form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deposit_amount = $_POST['amount'];
    $currency = $_POST['currency'];

    // Check if the deposit amount is valid
    if ($deposit_amount <= 0) {
        $error_message = "Deposit amount must be greater than zero.";
    } else {
        // Get the exchange rate for the selected currency (BTC or USDC) to USD
        $usd_amount = 0;
        if ($currency == 'BTC') {
            $usd_amount = getBtcToUsdRate() * $deposit_amount;
        } elseif ($currency == 'USDC') {
            $usd_amount = $deposit_amount;  // 1 USDC is assumed to be 1 USD
        }

        // Assuming the user has enough balance for the deposit (you can add a custom check for this)
        if ($user_balance >= $deposit_amount) {
            // Process the deposit here
            // Update balance in USD
            $update_balance_sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $stmt = $pdo->prepare($update_balance_sql);
            $stmt->execute([$usd_amount, $user_id]);

            // Record the deposit in the transaction history (optional)
            $insert_transaction_sql = "INSERT INTO transactions (user_id, amount, currency, type, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($insert_transaction_sql);
            $stmt->execute([$user_id, $usd_amount, $currency, 'deposit', 'successful']);

            // Redirect or show success message
            header("Location: deposit_success.php"); // Or simply display a success message
            exit;
        } else {
            $error_message = 'Insufficient balance to make this deposit.';
            header("Location: deposit.php?error=low_balance");
            exit;
        }
    }
}

// Function to get the BTC to USD exchange rate
function getBtcToUsdRate() {
    $api_url = "https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd";
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    return $data['bitcoin']['usd'] ?? 0;
}
?>
