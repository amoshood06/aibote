<?php
include '../db/db_connection.php';

if (!isset($_GET['token'])) {
    die("Invalid Token!");
}

$token = $_GET['token'];

// Verify token
$stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ?");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    die("Invalid or Expired Token!");
}
?>

<form action="update_password.php" method="POST">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <label>New Password:</label>
    <input type="password" name="password" required>
    <button type="submit">Reset Password</button>
</form>
