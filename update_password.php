<?php
include './db/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        die("Error: Passwords do not match!");
    }

    // Ensure password is secure
    if (strlen($password) < 8) {
        die("Error: Password must be at least 8 characters long!");
    }

    // Fetch email from password_resets table
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        die("Error: Invalid or expired token!");
    }

    $email = $reset['email'];
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Update user password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashedPassword, $email]);

    // Delete the reset token after successful reset
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);

    //echo "Password successfully updated! You can now <a href='login.php'>log in</a>.";
    header("Location: password-reset-success.php");
}
?>
