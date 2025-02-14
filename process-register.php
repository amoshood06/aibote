<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: user/index.php"); // Redirect logged-in users to the dashboard
    exit();
}

require 'db/db_connection.php';

$message = '';
$message_type = '';

// Function to generate a unique referral code
function generateReferralCode($pdo, $length = 8) {
    do {
        $code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$code]);
    } while ($stmt->rowCount() > 0);
    return $code;
}

// Get referral code from URL if available
$referred_by = isset($_GET['ref']) ? $_GET['ref'] : NULL;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email address."]);
        exit();
    }

    // Basic form validation
    if (empty($full_name) || empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit();
    } 

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "error", "message" => "Email is already registered."]);
        exit();
    }

    // Generate unique referral code for the new user
    $referral_code = generateReferralCode($pdo);

    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, referral_code, referred_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, password_hash($password, PASSWORD_BCRYPT), $referral_code, $referred_by]);

    echo json_encode(["status" => "success", "message" => "Registration successful! Redirecting to login..."]);
    exit();
}
?>
