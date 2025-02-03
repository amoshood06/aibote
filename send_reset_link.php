<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './db/db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        die("Email field is required.");
    }

    $email = trim($_POST['email']);

    try {
        // Check if the email exists (case-insensitive search)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate Reset Token
            $token = bin2hex(random_bytes(50));

            // Insert or update token in password_resets table
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, created_at) 
                                   VALUES (?, ?, NOW()) 
                                   ON DUPLICATE KEY UPDATE token = ?, created_at = NOW()");
            if (!$stmt->execute([$email, $token, $token])) {
                die("Failed to store reset token.");
            }

            // Create Reset Link
            $resetLink = "https://bothighstock.com/reset_password.php?token=$token";

            // Send Email
            if (sendEmail($email, "Password Reset Request", "
                <p>Hello,</p>
                <p>You have requested to reset your password. Click the link below to proceed:</p>
                <p><a href='$resetLink' style='color: blue; font-weight: bold;'>Reset Password</a></p>
                <p>If you did not request this, please ignore this email.</p>
                <p>Best Regards,<br>Bothigh Stock Team</p>
            ")) {
                echo "A password reset link has been sent to your email.";
            } else {
                echo "Failed to send email. Please try again later.";
            }
        } else {
            echo "Email not found!";
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo "An error occurred. Please try again later.";
    }
}

// Function to send email
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.bothighstock.com'; // SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('noreply@bothighstock.com'); // Use environment variable
        $mail->Password   = getenv('AjoseKola123'); // Use environment variable
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL encryption
        $mail->Port       = 465;

        $mail->setFrom('noreply@bothighstock.com', 'Bothigh Stock');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent to $to. Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>