<?php
session_start();
include('../db/db_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user details including balance and referral code
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, balance, referral_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Ensure referral_code is set before using it
$referral_code = isset($user['referral_code']) ? trim($user['referral_code']) : '';

// Debugging: Check if referral code is being fetched correctly
if (empty($referral_code)) {
    echo "Referral code is missing.";
} else {
    //echo "Referral Code: " . htmlspecialchars($referral_code); // Securely display referral code
}

// Generate referral link
$referral_link = !empty($referral_code) ? "https://bothighstock.com/register.php?ref=" . urlencode($referral_code) : '#';

// Display the referral link (for debugging)
//echo "<br>Referral Link: " . $referral_link;

// Fetch daily returns
$sql = "SELECT dr.*, u.full_name, i.amount 
        FROM daily_returns dr
        JOIN users u ON dr.user_id = u.id
        JOIN investments i ON dr.investment_id = i.id
        ORDER BY dr.created_at DESC
        LIMIT 5";  // Fetch only the latest 5 records
$stmt = $pdo->prepare($sql);
$stmt->execute();
$dailyReturns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total investment
$totalInvestmentStmt = $pdo->prepare("SELECT SUM(amount) AS total_investment FROM investments WHERE user_id = ?");
$totalInvestmentStmt->execute([$user_id]);
$totalInvestment = $totalInvestmentStmt->fetch(PDO::FETCH_ASSOC)['total_investment'];

// Calculate total profit
$totalProfitStmt = $pdo->prepare("SELECT SUM(return_amount) AS total_profit FROM daily_returns WHERE user_id = ?");
$totalProfitStmt->execute([$user_id]);
$totalProfit = $totalProfitStmt->fetch(PDO::FETCH_ASSOC)['total_profit'];

// Calculate total trades and successful trades
$totalTradesStmt = $pdo->prepare("SELECT COUNT(*) AS total_trades FROM daily_returns WHERE user_id = ?");
$totalTradesStmt->execute([$user_id]);
$totalTrades = $totalTradesStmt->fetch(PDO::FETCH_ASSOC)['total_trades'];

$successfulTradesStmt = $pdo->prepare("SELECT COUNT(*) AS successful_trades FROM daily_returns WHERE user_id = ? AND return_amount > 0");
$successfulTradesStmt->execute([$user_id]);
$successfulTrades = $successfulTradesStmt->fetch(PDO::FETCH_ASSOC)['successful_trades'];

// Calculate win rate
$winRate = $totalTrades > 0 ? round(($successfulTrades / $totalTrades) * 100) : 0;

?>


<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Trade Bot</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-300 hover:text-white focus:outline-none focus:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="fixed inset-y-0 left-0 w-64 bg-gray-800 z-50 transform -translate-x-full transition duration-200 ease-in-out md:hidden">
        <div class="p-6">
            <button id="close-menu-button" class="absolute top-3 right-3 text-gray-300 hover:text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <nav class="mt-8 space-y-4">
                <a href="#" class="block text-gray-300 hover:text-white">Dashboard</a>
                <a href="#" class="block text-gray-300 hover:text-white">Trades</a>
                <a href="#" class="block text-gray-300 hover:text-white">Deposit</a>
                <a href="investment.php" class="block text-gray-300 hover:text-white">Investment Plan</a>
                <a href="#" class="block text-gray-300 hover:text-white">Settings</a>
                <a href="logout.php" class="inline-flex items-center justify-center rounded-full bg-[#FBC531] px-4 py-2 text-sm font-medium text-black hover:bg-neon/90 focus:outline-none focus:ring-2 focus:ring-neon/50">Logout</a>
            </nav>
        </div>
    </div>

    <main class="container mx-auto px-4 sm:px-6 py-8">
        <div class="grid md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <div class="chat-mobile bg-gray-800 h-[450px] pb-[70px] rounded-lg shadow-xl pt-4 pl-4 pr-4 sm:p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl sm:text-2xl font-bold">Trading Chart</h2>
                    </div>
                    <!-- TradingView Widget BEGIN -->
                     <div style="width: 100%; height: 90%;">
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
                    <!-- TradingView Widget END -->
                    <!-- <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-gray-400">Current Price</p>
                            <p class="text-xl sm:text-2xl font-bold">$50,234.21</p>
                        </div>
                        <div>
                            <p class="text-gray-400">24h High</p>
                            <p class="text-xl sm:text-2xl font-bold text-green-500">$51,456.78</p>
                        </div>
                        <div>
                            <p class="text-gray-400">24h Change</p>
                            <p class="text-xl sm:text-2xl font-bold text-green-500">+2.45%</p>
                        </div>
                    </div> -->
                </div>
                <div class="bg-gray-800 rounded-lg shadow-xl p-4 sm:p-6">
    <h2 class="text-xl sm:text-2xl font-bold mb-4 text-white">Daily Investment Returns</h2>
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
                    <td class="px-4 py-3 sm:px-6">$<?= number_format($return['investment_amount'], 2) ?></td>
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
                <div class="bg-gray-800 rounded-lg shadow-xl p-4 sm:p-6 mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold mb-4">AI Bot Plan</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Trading Pair</label>
                            <select class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary-600">
                                <option>BTC/USDT</option>
                                <option>ETH/USDT</option>
                                <option>XRP/USDT</option>
                            </select>
                        </div>
                        <div>
                            <p>Share your referral link:</p>
                            <input id="referralLink" type="text" value="<?php echo $referral_link; ?>" disabled>
                            <button class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded" onclick="copyReferral()">Copy Link</button>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800 rounded-lg shadow-xl p-4 sm:p-6">
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
                            <span class="font-bold"><?php echo $winRate; ?>%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Trades</span>
                            <span class="font-bold"><?php echo $totalTrades; ?></span>
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
            mobileMenu.classList.remove('-translate-x-full');
        });

        closeMenuButton.addEventListener('click', () => {
            mobileMenu.classList.add('-translate-x-full');
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
    <?php if ($welcome_message): ?>
        toastr.success("<?= $welcome_message ?>");
    <?php endif; ?>
    </script>
    <script>
        function copyReferral() {
            var copyText = document.getElementById("referralLink");

            // Ensure the text is selected correctly
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices

            // Try to execute the copy command, and handle any errors
            try {
                document.execCommand("copy");
                alert("Referral link copied!");
            } catch (err) {
                alert("Oops! Unable to copy the link.");
            }
        }
    </script>
    <!-- AJAX Logout Script -->
    <script>
        $(document).ready(function () {
            $("#logoutBtn").click(function () {
                $.ajax({
                    type: "POST",
                    url: "../logout.php",
                    success: function (response) {
                        toastr.success("You have been logged out!", "Logout Successful");
                        setTimeout(() => {
                            window.location.href = "../login.php";
                        }, 2000);
                    },
                    error: function () {
                        toastr.error("Something went wrong!", "Error");
                    }
                });
            });
        });
    </script>
</body>
</html>