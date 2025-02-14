<?php
require 'db/db_connection.php';
session_start();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['welcome_message'] = "Welcome back, " . $_SESSION['full_name'] . "!";

        echo json_encode(["status" => "success", "message" => "Login successful"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid email or password"]);
        exit();
    }
}
?>
