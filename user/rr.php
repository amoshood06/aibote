<?php
session_start();

// Database credentials
$host = 'localhost';
$dbname = 'mfyokfmh_aibot';
$db_username = 'mfyokfmh_aibot';
$db_password = 'mfyokfmh_aibot';

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ./login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch user details
    $stmt = $pdo->prepare("SELECT full_name, balance FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found");
    }

    // Ensure investments table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS investments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        portfolio_name VARCHAR(100) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        weekly_return_rate DECIMAL(5,2) NOT NULL,
        investment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Portfolio details
    $portfolio = [
        'name' => 'Starter Portfolio',
        'min_investment' => 1000,
        'max_investment' => 10000,
        'weekly_return' => 2.5
    ];

    // Handle investment submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invest'])) {
        $investment_amount = floatval($_POST['investmentAmount']);

        // Validate investment amount
        if ($investment_amount < $portfolio['min_investment'] || $investment_amount > $portfolio['max_investment']) {
            $error = "Investment amount must be between $" . number_format($portfolio['min_investment']) . " and $" . number_format($portfolio['max_investment']) . ".";
        } elseif ($investment_amount > $user['balance']) {
            $error = "Insufficient balance. Please fund your wallet.";
        } else {
            // Start transaction
            $pdo->beginTransaction();

            try {
                // Insert investment record
                $stmt = $pdo->prepare("INSERT INTO investments (user_id, portfolio_name, amount, weekly_return_rate) VALUES (:user_id, :portfolio_name, :amount, :weekly_return_rate)");
                $stmt->execute([
                    'user_id' => $user_id,
                    'portfolio_name' => $portfolio['name'],
                    'amount' => $investment_amount,
                    'weekly_return_rate' => $portfolio['weekly_return']
                ]);

                // Deduct amount from user balance
                $new_balance = $user['balance'] - $investment_amount;
                $stmt = $pdo->prepare("UPDATE users SET balance = :balance WHERE id = :user_id");
                $stmt->execute([
                    'balance' => $new_balance,
                    'user_id' => $user_id
                ]);

                // Commit transaction
                $pdo->commit();

                // Update user balance for UI
                $user['balance'] = $new_balance;

                // Success message
                $success = "Investment of $" . number_format($investment_amount, 2) . " successful!";
            } catch (Exception $e) {
                // Rollback in case of failure
                $pdo->rollBack();
                $error = "Investment failed: " . $e->getMessage();
            }
        }
    }

    // Fetch investment history
    $stmt = $pdo->prepare("SELECT amount, investment_date FROM investments WHERE user_id = :user_id ORDER BY investment_date DESC LIMIT 5");
    $stmt->execute(['user_id' => $user_id]);
    $investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starter Portfolio - Invest Now</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-white">
<header class="flex justify-between items-center p-4">
            <h1 class="text-xl font-semibold">Starter Portfolio</h1>
            <div class="flex items-center">
            <p>Your Balance: <span class="font-bold">$<?= number_format($user['balance'], 2) ?></span></p>
                <span class="mr-4">Welcome, <?= htmlspecialchars($user['username']) ?></span>
                <form action="logout.php" method="post">
                    <button type="submit" class="bg-[#FBC531] text-black pl-4 pr-4 pt-2 pb-2 rounded-full flex items-center">
                        Logout
                        <i class="ri-logout-box-r-line ml-2"></i>
                    </button>
                </form>
            </div>
        </header>
    <!-- <header class="p-4">
        <h1 class="text-2xl font-semibold">Starter Portfolio</h1>
        <p>Welcome, <?= htmlspecialchars($user['full_name']) ?></p>
        <p>Your Balance: <span class="font-bold">$<?= number_format($user['balance'], 2) ?></span></p>
    </header> -->

    <main class="p-4">
        <?php if (isset($error)): ?>
            <div class="bg-red-500 text-white p-3 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="bg-green-500 text-white p-3 rounded"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="bg-gray-800 p-6 rounded">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($portfolio['name']) ?></h2>
            <p>Investment Range: $<?= number_format($portfolio['min_investment']) ?> - $<?= number_format($portfolio['max_investment']) ?></p>
            <p>Weekly Return Rate: <?= $portfolio['weekly_return'] ?>%</p>
        </div>

        <form method="post" class="mt-4 bg-gray-800 p-6 rounded">
            <label for="investmentAmount" class="block text-sm">Investment Amount ($)</label>
            <input type="number" id="investmentAmount" name="investmentAmount" min="<?= $portfolio['min_investment'] ?>" max="<?= min($portfolio['max_investment'], $user['balance']) ?>" step="100" class="w-full p-2 rounded bg-gray-700 text-white" required>

            <button type="submit" name="invest" class="w-full mt-4 bg-yellow-500 text-black font-bold py-2 rounded">
                Invest Now
            </button>
        </form>

        <h2 class="mt-6 text-xl font-semibold">Investment History</h2>
        <canvas id="investmentChart" class="bg-gray-800 p-4 rounded"></canvas>
    </main>
    <footer class="bg-zinc-900 border-t border-zinc-800 p-2">
            <div class="grid grid-cols-5 gap-2">
                <button class="flex flex-col items-center text-gray-400 hover:text-white">
                    <i class="ri-home-line text-xl"></i>
                    <span class="text-xs">Home</span>
                </button>
                <button class="flex flex-col items-center text-gray-400 hover:text-white">
                    <i class="ri-line-chart-line text-xl"></i>
                    <span class="text-xs">Markets</span>
                </button>
                <button class="flex flex-col items-center text-gray-400 hover:text-white">
                    <i class="ri-exchange-line text-xl"></i>
                    <span class="text-xs">Trade</span>
                </button>
                <button class="flex flex-col items-center text-gray-400 hover:text-white">
                    <i class="ri-wallet-3-line text-xl"></i>
                    <span class="text-xs">Earn</span>
                </button>
                <button class="flex flex-col items-center text-amber-500">
                    <i class="ri-funds-box-line text-xl"></i>
                    <span class="text-xs">Invest</span>
                </button>
            </div>
        </footer>
    <script>
        const ctx = document.getElementById('investmentChart').getContext('2d');
        const investmentData = {
            labels: [<?php foreach ($investments as $inv) { echo "'" . date("M d", strtotime($inv['investment_date'])) . "', "; } ?>],
            datasets: [{
                label: 'Investment Amount ($)',
                data: [<?php foreach ($investments as $inv) { echo $inv['amount'] . ", "; } ?>],
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 2
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: investmentData,
            options: { responsive: true }
        });
    </script>

</body>
</html>
