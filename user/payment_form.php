<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bitcoin Payment</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['full_name']; ?>!</h2>
    <h3>Your Balance: $<?php echo number_format($_SESSION['balance'], 2); ?></h3>
    
    <h2>Deposit Bitcoin</h2>
    <form action="process_payment.php" method="POST">
        <label>Enter Amount (USD):</label>
        <input type="number" name="amount" step="0.01" required>
        <br><br>
        <button type="submit">Proceed to Pay</button>
    </form>
</body>
</html>
