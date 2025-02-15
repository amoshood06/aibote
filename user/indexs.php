<?php
session_start();
include('../db/db_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user balance
$stmt = $pdo->prepare("SELECT full_name, balance, referral_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_balance = $user['balance'] ?? 0; // User's balance in USD
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

// Function to fetch BTC rate using cURL
function getBtcRate() {
    $api_url = "https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification if needed

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response !== false) {
        $data = json_decode($response, true);
        return $data['bitcoin']['usd'] ?? 0;
    }

    return 0; // Return 0 if API fails
}

// Get BTC/USD rate
$btc_rate = getBtcRate();

// Convert user balance (USD to BTC)
$btc_value = ($btc_rate > 0) ? ($user_balance / $btc_rate) : 0;
?>

<?php
// Simulated user data - in a real application, this would come from a database
$user = [
    'username' => 'cryptotrader123',
    'balance' => 10000.00,
    'daily_profit' => 250.50,
    'referral_link' => 'https://example.com/ref/cryptotrader123'
];

// Simulated transaction history
$transactions = [
    ['type' => 'Deposit', 'amount' => 1000, 'date' => '2025-02-10'],
    ['type' => 'Withdraw', 'amount' => 500, 'date' => '2025-02-08'],
    ['type' => 'Trade', 'amount' => 250, 'date' => '2025-02-07'],
];
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./asset/toast/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
</head>
<body class="bg-black text-white h-full flex flex-col">
    <div class="min-h-screen bg-black text-white flex flex-col">
        <header class="flex justify-between items-center p-4">
            <h1 class="text-xl font-semibold">Crypto Dashboard</h1>
            <button class="bg-[#FBC531] text-black pl-4 pr-4 pt-2 pb-2 rounded-full flex items-center">
                Logout
                <i class="ri-logout-box-r-line ml-2"></i>
            </button>
        </header>

        <main class="flex-grow p-4 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-zinc-800 p-4 rounded-xl">
                    <h2 class="text-lg font-semibold mb-2">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                    <p class="text-gray-400">Your Referral Link:</p>
                    <p class="text-sm break-all"><?php echo htmlspecialchars($user['referral_link']); ?></p>
                </div>
                <div class="bg-zinc-800 p-4 rounded-xl">
                    <h2 class="text-lg font-semibold mb-2">Account Overview</h2>
                    <p>Balance: $<?php echo number_format($user['balance'], 2); ?></p>
                    <p>Daily Profit: $<?php echo number_format($user['daily_profit'], 2); ?></p>
                </div>
            </div>

            <div class="w-full h-[400px] rounded-xl overflow-hidden border border-zinc-800">
                <div id="tradingview_chart" class="w-full h-full"></div>
            </div>

            <div class="flex justify-center space-x-4">
                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Deposit
                </button>
                <button class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Withdraw
                </button>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-4">Transaction History</h2>
                <div class="bg-zinc-800 rounded-xl overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-zinc-700">
                                <th class="p-2 text-left">Type</th>
                                <th class="p-2 text-left">Amount</th>
                                <th class="p-2 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr class="border-t border-zinc-700">
                                    <td class="p-2"><?php echo htmlspecialchars($transaction['type']); ?></td>
                                    <td class="p-2">$<?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td class="p-2"><?php echo htmlspecialchars($transaction['date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <footer class="bg-zinc-900 border-t border-zinc-800 p-2">
            <div class="grid grid-cols-5 gap-2">
                <button class="flex flex-col items-center text-amber-500">
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
                <button class="flex flex-col items-center text-gray-400 hover:text-white">
                    <i class="ri-funds-box-line text-xl"></i>
                    <span class="text-xs">Invest</span>
                </button>
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
    <script src="../asset/toast/jquery-3.7.1.min.js"></script>
    <script src="../asset/toast/toastr.min.js"></script>
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

