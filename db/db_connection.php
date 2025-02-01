<?php
// Database connection configuration
$host = 'localhost'; // Replace with your MySQL host, e.g., '127.0.0.1'
$dbName = 'aibotrade'; // Replace with your database name
$username = 'root'; // Replace with your MySQL username
$password = ''; // Replace with your MySQL password

try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "";
} catch (PDOException $e) {
    // Handle connection errors
    echo "Connection failed: " . $e->getMessage();
}
?>
