<?php
include '../db/db_connection.php';

// Email Configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Start a database transaction
$pdo->beginTransaction();

try {
    // Fetch active investments
    $sql = "SELECT i.*, u.email, u.full_name FROM investments i
            JOIN users u ON i.user_id = u.id
            WHERE i.status = 'active' AND i.days_completed < 7";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($investments as $investment) {
        $investment_id = $investment['id'];
        $user_id = $investment['user_id'];
        $amount_invested = $investment['amount_invested'];
        $weekly_return = $investment['weekly_return'];
        $daily_return = $weekly_return / 7; // Calculate daily return
        $total_return = $investment['total_return'] + $daily_return;
        $days_completed = $investment['days_completed'] + 1;
        $email = $investment['email'];
        $full_name = $investment['full_name'];

        // Credit user balance
        $update_balance_sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
        $stmt = $pdo->prepare($update_balance_sql);
        $stmt->execute([$daily_return, $user_id]);

        // Insert into daily return history
        $insert_return_sql = "INSERT INTO daily_returns (investment_id, user_id, return_amount) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($insert_return_sql);
        $stmt->execute([$investment_id, $user_id, $daily_return]);

        // Update investment progress
        if ($days_completed == 7) {
            $status = 'completed'; // Mark investment as completed after 7 days
        } else {
            $status = 'active';
        }

        $update_investment_sql = "UPDATE investments SET total_return = ?, days_completed = ?, status = ? WHERE id = ?";
        $stmt = $pdo->prepare($update_investment_sql);
        $stmt->execute([$total_return, $days_completed, $status, $investment_id]);

        // Send Email Notification
        $subject = "Your Daily Investment Profit";
        $message = "
            <h3>Hello $full_name,</h3>
            <p>You have received a daily return of <strong>\$$daily_return</strong> from your investment.</p>
            <p>Total earned so far: <strong>\$$total_return</strong>.</p>
            <p>Thank you for investing with us!</p>
            <p>Best Regards,<br>AIboTrade Team</p>
        ";

        sendEmail($email, $subject, $message);
    }

    // Commit the transaction
    $pdo->commit();
    echo "Daily profits processed successfully.";
} catch (Exception $e) {
    // Rollback transaction if an error occurs
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}

// Function to send email
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME');
        $mail->Password   = getenv('SMTP_PASSWORD');
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
