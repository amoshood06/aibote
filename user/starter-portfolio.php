<?php
session_start();

// Database configuration
$host = '';
$dbname = 'mfyokfmh_aibot';
$db_username = 'mfyokfmh_aibot';
$db_password = 'mfyokfmh_aibot';

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page if not logged in
        header("Location: ./login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Prepare SQL statement to select username and balance
    $stmt = $pdo->prepare("SELECT full_gname, balance FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found");
    }

    // Create investments table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS investments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        portfolio_name VARCHAR(100) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        weekly_return_rate DECIMAL(5, 2) NOT NULL,
        investment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Portfolio details
    $portfolio = [
        'name' => 'Starter Portfolio',
        'min_investment' => 1000,
        'max_investment' => 10000,
        'weekly_return' => 2.5,
        'features' => [
            'Access to Blue-chip Stocks',
            'Weekly Market Reports',
            'Basic Portfolio Rebalancing'
        ]
    ];

    // Process investment form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invest'])) {
        $investment_amount = floatval($_POST['investmentAmount']);
        
        // Validate investment amount
        if ($investment_amount < $portfolio['min_investment'] || $investment_amount > $portfolio['max_investment']) {
            $error = "Invalid investment amount";
        } elseif ($investment_amount > $user['balance']) {
            $error = "Insufficient balance";
        } else {
            // Start transaction
            $pdo->beginTransaction();

            try {
                // Insert into investments table
                $stmt = $pdo->prepare("INSERT INTO investments (user_id, portfolio_name, amount, weekly_return_rate) VALUES (:user_id, :portfolio_name, :amount, :weekly_return_rate)");
                $stmt->execute([
                    'user_id' => $user_id,
                    'portfolio_name' => $portfolio['name'],
                    'amount' => $investment_amount,
                    'weekly_return_rate' => $portfolio['weekly_return']
                ]);

                // Update user balance
                $new_balance = $user['balance'] - $investment_amount;
                $stmt = $pdo->prepare("UPDATE users SET balance = :balance WHERE id = :user_id");
                $stmt->execute([
                    'balance' => $new_balance,
                    'user_id' => $user_id
                ]);

                // Commit transaction
                $pdo->commit();

                // Update user data in memory
                $user['balance'] = $new_balance;

                $success = "Investment of $" . number_format($investment_amount, 2) . " successful!";
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $error = "Investment failed: " . $e->getMessage();
            }
        }
    }

} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch(Exception $e) {
    die("Error: " . $e->getMessage());
}

// Calculation function
function calculateReturns($investment, $weeks, $rate) {
    return $investment * pow(1 + $rate, $weeks);
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starter Portfolio - Investment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-black text-white h-full flex flex-col">
    <div class="min-h-screen bg-black text-white flex flex-col">
        <header class="flex justify-between items-center p-4">
            <h1 class="text-xl font-semibold">Starter Portfolio</h1>
            <div class="flex items-center">
                <span class="mr-4">Welcome, <?= htmlspecialchars($user['username']) ?></span>
                <form action="logout.php" method="post">
                    <button type="submit" class="bg-[#FBC531] text-black pl-4 pr-4 pt-2 pb-2 rounded-full flex items-center">
                        Logout
                        <i class="ri-logout-box-r-line ml-2"></i>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-grow p-4 space-y-6">
            <?php if (isset($error)): ?>
                <div class="bg-red-500 text-white p-4 rounded-xl"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="bg-green-500 text-white p-4 rounded-xl"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="bg-zinc-800 p-6 rounded-xl">
                <h2 class="text-2xl font-bold mb-4"><?= htmlspecialchars($portfolio['name']) ?></h2>
                <p class="text-xl mb-4">$<?= number_format($portfolio['min_investment']) ?> - $<?= number_format($portfolio['max_investment']) ?></p>
                <p class="text-lg mb-4">Weekly Return: <?= $portfolio['weekly_return'] ?>%</p>
                <ul class="list-disc list-inside mb-4">
                    <?php foreach ($portfolio['features'] as $feature): ?>
                        <li><?= htmlspecialchars($feature) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-zinc-800 p-6 rounded-xl">
                    <h3 class="text-xl font-semibold mb-4">Invest in Starter Portfolio</h3>
                    <p class="mb-4">Your current balance: $<?= number_format($user['balance'], 2) ?></p>
                    <form id="investForm" method="post" class="space-y-4">
                        <div>
                            <label for="investmentAmount" class="block text-sm font-medium text-gray-400">Investment Amount ($)</label>
                            <input type="number" id="investmentAmount" name="investmentAmount" min="<?= $portfolio['min_investment'] ?>" max="<?= min($portfolio['max_investment'], $user['balance']) ?>" step="100" class="mt-1 block w-full rounded-md bg-zinc-700 border-transparent focus:border-gray-500 focus:bg-zinc-600 focus:ring-0 text-white" required>
                        </div>
                        <div>
                            <label for="investmentPeriod" class="block text-sm font-medium text-gray-400">Investment Period (Weeks)</label>
                            <input type="number" id="investmentPeriod" name="investmentPeriod" min="1" max="52" class="mt-1 block w-full rounded-md bg-zinc-700 border-transparent focus:border-gray-500 focus:bg-zinc-600 focus:ring-0 text-white" required>
                        </div>
                        <button type="submit" name="invest" class="w-full bg-[#FBC531] text-black font-bold py-2 px-4 rounded hover:bg-yellow-500 transition duration-300">
                            Invest Now
                        </button>
                    </form>
                </div>
                <div class="bg-zinc-800 p-6 rounded-xl">
                    <h3 class="text-xl font-semibold mb-4">Projected Returns</h3>
                    <canvas id="returnsChart"></canvas>
                </div>
            </div>
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
    </div>

    <script>
        const ctx = document.getElementById('returnsChart').getContext('2d');
        let chart;

        function updateChart(investment, weeks) {
            const labels = Array.from({length: weeks}, (_, i) => `Week ${i + 1}`);
            const data = labels.map((_, i) => calculateReturns(investment, i + 1));

            if (chart) {
                chart.destroy();
            }

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Projected Returns',
                        data: data,
                        borderColor: '#FBC531',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return '$' + value.toFixed(2);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        document.getElementById('investForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const investment = parseFloat(document.getElementById('investmentAmount').value);
            const weeks = parseInt(document.getElementById('investmentPeriod').value);
            updateChart(investment, weeks);
        });

        // Initialize chart with default values
        updateChart(1000, 12);

        function calculateReturns(investment, weeks) {
            const rate = <?= $portfolio['weekly_return'] ?> / 100;
            return investment * Math.pow(1 + rate, weeks);
        }
    </script>
</body>
</html>

