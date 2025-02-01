<?php
include '../db/db_connection.php';

// Email Configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Fetch all active investments
$sql = "SELECT i.*, u.email, u.full_name FROM investments i
        JOIN users u ON i.user_id = u.id
        WHERE i.status = 'active'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($investments as $investment) {
    $investment_id = $investment['id'];
    $user_id = $investment['user_id'];
    $weekly_return = $investment['weekly_return'];
    $email = $investment['email'];
    $full_name = $investment['full_name'];

    // Credit user balance
    $update_balance_sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
    $stmt = $pdo->prepare($update_balance_sql);
    $stmt->execute([$weekly_return, $user_id]);

    // Insert return history
    $insert_return_sql = "INSERT INTO investment_returns (investment_id, user_id, return_amount) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($insert_return_sql);
    $stmt->execute([$investment_id, $user_id, $weekly_return]);

    // Send Email Notification
    $subject = "Your Weekly Investment Return";
    $message = "
        <h3>Hello $full_name,</h3>
        <p>Your weekly return of <strong>\$$weekly_return</strong> has been credited to your balance.</p>
        <p>Thank you for investing with us!</p>
        <p>Best Regards,<br>AIboTrade Team</p>
    ";

    sendEmail($email, $subject, $message);
}

echo "Weekly returns processed and emails sent successfully.";

// Function to send email
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com'; // Replace with your email
        $mail->Password   = 'your-email-password'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('your-email@gmail.com', 'AIboTrade');
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
