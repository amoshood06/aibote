<?php
session_start();
include '../db/db_connection.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM users WHERE referred_by = ?");
$stmt->execute([$user['referral_code']]);
$referrals = $stmt->fetchAll();
?>

<h2>Your Referred Users</h2>
<table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Joined</th>
    </tr>
    <?php foreach ($referrals as $ref): ?>
    <tr>
        <td><?php echo $ref['full_name']; ?></td>
        <td><?php echo $ref['email']; ?></td>
        <td><?php echo $ref['created_at']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>
