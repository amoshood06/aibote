<?php
session_start();
include '../db/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<form action="make_payment.php" method="POST">
    <label for="amount">Enter Amount (USD):</label>
    <input type="text" name="amount" required>
    <button type="submit">Make Payment</button>
</form>
