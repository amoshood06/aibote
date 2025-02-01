<?php 
session_start();
include('../db/db_connection.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user balance
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Welcome message logic
$welcome_message = isset($_SESSION['welcome_message']) ? $_SESSION['welcome_message'] : '';
unset($_SESSION['welcome_message']); // Remove after displaying

// Fetch daily returns
$sql = "SELECT dr.*, u.full_name, i.amount 
        FROM daily_returns dr
        JOIN users u ON dr.user_id = u.id
        JOIN investments i ON dr.investment_id = i.id
        WHERE dr.user_id = ?
        ORDER BY dr.created_at DESC
        LIMIT 5";  // Fetch only the latest 5 records
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$dailyReturns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total investment
$totalInvestmentStmt = $pdo->prepare("SELECT SUM(amount) AS total_investment FROM investments WHERE user_id = ?");
$totalInvestmentStmt->execute([$user_id]);
$totalInvestment = $totalInvestmentStmt->fetch(PDO::FETCH_ASSOC)['total_investment'];

// Calculate total profit
$totalProfitStmt = $pdo->prepare("SELECT SUM(return_amount) AS total_profit FROM daily_returns WHERE user_id = ?");
$totalProfitStmt->execute([$user_id]);
$totalProfit = $totalProfitStmt->fetch(PDO::FETCH_ASSOC)['total_profit'];
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Trade Bot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"}
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-900 text-white">
    <header class="bg-gray-800 shadow-lg">
        <nav class="container mx-auto px-4 sm:px-6 py-3">
            <div class="flex items-center justify-between">
                <div class="text-xl font-bold">AI Trade Bot</div>
                <div class="hidden md:flex space-x-4 item-center justify-center">
                    <a href="#" class="hover:text-primary-400">Dashboard</a>
                    <a href="#" class="hover:text-primary-400">Trades</a>
                    <a href="#" class="hover:text-primary-400">Deposit</a>
                    <a href="investment.php" class="hover:text-primary-400">Investment Plan</a>
                    <a href="#" class="hover:text-primary-400">Settings</a>
                    <a href="logout.php" class="inline-flex items-center justify-center rounded-full bg-[#FBC531] px-4 py-2 text-sm font-medium text-black hover:bg-neon/90 focus:outline-none focus:ring-2 focus:ring-neon/50">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-8">
        <div class="grid md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <!-- Trading chart -->
                <div class="bg-gray-800 h-[450px] pb-[70px] rounded-lg shadow-xl pt-4 pl-4 pr-4 sm:p-6 mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold mb-4">Trading Chart</h2>
                    <!-- TradingView Widget -->
                    <div class="tradingview-widget-container w-full h-64 sm:h-96 rounded-lg mb-4">
                        <div class="tradingview-widget-container__widget h-full w-full"></div>
                        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js" async>
                            {
                                "autosize": true,
                                "symbol": "NASDAQ:AAPL",
                                "timezone": "Etc/UTC",
                                "theme": "dark",
                                "style": "1",
                                "locale": "en",
                                "withdateranges": true,
                                "range": "YTD",
                                "hide_side_toolbar": false,
                                "allow_symbol_change": true,
                                "details": true,
                                "hotlist": true,
                                "calendar": false,
                                "show_popup_button": true,
                                "popup_width": "1000",
                                "popup_height": "650",
                                "support_host": "https://www.tradingview.com"
                            }
                        </script>
                    </div>
                </div>

                <!-- Daily Investment Returns -->
                <div class="bg-gray-800 rounded-lg shadow-xl p-4 sm:p-6 mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold mb-4">Daily Investment Returns</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-400">
                            <thead class="text-xs uppercase bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 sm:px-6">Investor</th>
                                    <th class="px-4 py-3 sm:px-6">Investment Amount</th>
                                    <th class="px-4 py-3 sm:px-6">Daily Return</th>
                                    <th class="px-4 py-3 sm:px-6">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dailyReturns as $return): ?>
                                    <tr class="border-b border-gray-700">
                                        <td class="px-4 py-3 sm:px-6"><?= htmlspecialchars($return['full_name']) ?></td>
                                        <td class="px-4 py-3 sm:px-6">$<?= number_format($return['amount'], 2) ?></td>
                                        <td class="px-4 py-3 sm:px-6 text-green-500">$<?= number_format($return['return_amount'], 2) ?></td>
                                        <td class="px-4 py-3 sm:px-6"><?= date('M d, Y', strtotime($return['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="md:col-span-1">
                <!-- Performance Overview -->
                <div class="bg-gray-800 rounded-lg shadow-xl p-4 sm:p-6 mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold mb-4">Performance</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-400">💰 Balance</span>
                            <span class="font-bold text-green-500">$<?php echo number_format($user['balance'], 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Investment</span>
                            <span class="font-bold text-green-500">$<?php echo number_format($totalInvestment, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Profit</span>
                            <span class="font-bold text-green-500">+$<?php echo number_format($totalProfit, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Win Rate</span>
                            <span class="font-bold">68%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Trades</span>
                            <span class="font-bold">1,234</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 mt-12">
        <div class="container mx-auto px-4 sm:px-6 py-4">
            <p class="text-center text-gray-400">&copy; 2025 AI Trade Bot. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const closeMenuButton = document.getElementById('close-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.remove('hidden');
        });

        closeMenuButton.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
        });
    </script>
</body>
</html>
