<?php
session_start();
include('../db/db_connection.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

// Fetch user balance
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$welcome_message = isset($_SESSION['welcome_message']) ? $_SESSION['welcome_message'] : '';
unset($_SESSION['welcome_message']); // Remove after displaying
?>


<?php
// Simulated data for top cryptocurrencies
$topCryptos = [
    ['name' => 'Bitcoin', 'symbol' => 'BTC', 'price' => 50000, 'change' => 2.5],
    ['name' => 'Ethereum', 'symbol' => 'ETH', 'price' => 3000, 'change' => -1.2],
    ['name' => 'Cardano', 'symbol' => 'ADA', 'price' => 1.5, 'change' => 5.7],
    ['name' => 'Solana', 'symbol' => 'SOL', 'price' => 100, 'change' => 3.8],
    ['name' => 'Polkadot', 'symbol' => 'DOT', 'price' => 25, 'change' => 0.5],
];
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Chart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
</head>
<body class="bg-black text-white h-full flex flex-col">
    <div class="min-h-screen bg-black text-white flex flex-col">
        <header class="flex justify-between items-center p-4">
            <h1 class="text-xl font-semibold">Market Chart</h1>
            <button class="bg-[#FBC531] text-black pl-4 pr-4 pt-2 pb-2 rounded-full flex items-center">
                Logout
                <i class="ri-logout-box-r-line ml-2"></i>
            </button>
        </header>

        <main class="flex-grow p-4 space-y-6">
            <div class="w-full h-[400px] rounded-xl overflow-hidden border border-zinc-800">
                <div id="tradingview_chart" class="w-full h-full"></div>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-4">Top Cryptocurrencies</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($topCryptos as $crypto): ?>
                        <div class="bg-zinc-800 p-4 rounded-xl">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($crypto['name']); ?></h3>
                                <span class="text-sm text-gray-400"><?php echo htmlspecialchars($crypto['symbol']); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl">$<?php echo number_format($crypto['price'], 2); ?></span>
                                <span class="text-sm <?php echo $crypto['change'] >= 0 ? 'text-green-500' : 'text-red-500'; ?>">
                                    <?php echo $crypto['change'] > 0 ? '+' : ''; ?><?php echo $crypto['change']; ?>%
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>

        <footer class="bg-zinc-900 border-t border-zinc-800 p-2">
            <div class="grid grid-cols-5 gap-2">
                <button class="flex flex-col items-center text-gray-400 hover:text-white">
                    <i class="ri-home-line text-xl"></i>
                    <span class="text-xs">Home</span>
                </button>
                <a href="market-chart.php">
                    <button class="flex flex-col items-center text-amber-500">
                        <i class="ri-line-chart-line text-xl"></i>
                        <span class="text-xs">Markets</span>
                    </button>
                </a>
                <button class="flex flex-col items-center text-gray-400 hover:text-white">
                    <i class="ri-exchange-line text-xl"></i>
                    <span class="text-xs">Trade</span>
                </button>
                <button class="flex flex-col items-center text-gray-400 hover:text-white">
                    <i class="ri-wallet-3-line text-xl"></i>
                    <span class="text-xs">Earn</span>
                </button>
                <a href="investment.php">
                    <button class="flex flex-col items-center text-gray-400 hover:text-white">
                        <i class="ri-funds-box-line text-xl"></i>
                        <span class="text-xs">Invest</span>
                    </button>
                </a>
            </div>
        </footer>
    </div>

    <script type="text/javascript">
        new TradingView.widget({
            "width": "100%",
            "height": "100%",
            "symbol": "BTCUSD",
            "interval": "D",
            "timezone": "Etc/UTC",
            "theme": "dark",
            "style": "1",
            "locale": "en",
            "toolbar_bg": "#f1f3f6",
            "enable_publishing": false,
            "allow_symbol_change": true,
            "container_id": "tradingview_chart"
        });
    </script>
</body>
</html>

