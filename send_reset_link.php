<?php
include '../db/db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate Reset Token
        $token = bin2hex(random_bytes(50));

        // Insert token into password_resets table
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE token = ?");
        $stmt->execute([$email, $token, $token]);

        // Create Reset Link
        $resetLink = "https://bothighstock.com/reset_password.php?token=$token";

        // Send Email
        sendEmail($email, "Password Reset", "
            <p>Click the link below to reset your password:</p>
            <a href='$resetLink'>Reset Password</a>
        ");

        echo "A reset link has been sent to your email.";
    } else {
        echo "Email not found!";
    }
}

// Function to send email
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.bothighstock.com'; // Use correct SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@bothighstock.com'; // Correct email
        $mail->Password   = 'AjoseKola123'; // Directly assigned (Consider using an environment variable)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL
        $mail->Port       = 465; // Use correct SMTP port

        $mail->setFrom('noreply@bothighstock.com', 'Bothigh Stock');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent to $to. Error: {$mail->ErrorInfo}");
    }
}
?>
