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




<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
</head>
<body class="bg-black text-white h-full flex flex-col">
    <div class="contents">
        <div class="min-h-screen bg-black text-white">
            <div class="flex justify-between items-center p-4">
                <h1 class="text-xl font-semibold">My Assets</h1>
                <a href="logout.php">
                    <button class="bg-[#FBC531] pl-[20px] pr-[20px] pt-[10px] pb-[10px] flex rounded-[20px]">
                        Logout
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 0 0 6 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5h6a1.5 1.5 0 0 0 1.5-1.5V15a.75.75 0 0 1 1.5 0v3.75a3 3 0 0 1-3 3h-6a3 3 0 0 1-3-3V5.25a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3V9A.75.75 0 0 1 15 9V5.25a1.5 1.5 0 0 0-1.5-1.5h-6Zm10.72 4.72a.75.75 0 0 1 1.06 0l3 3a.75.75 0 0 1 0 1.06l-3 3a.75.75 0 1 1-1.06-1.06l1.72-1.72H9a.75.75 0 0 1 0-1.5h10.94l-1.72-1.72a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>

                    </button>
                </a>
            </div>
            <div class="p-4 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400">Total Assets</span>
                    <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent h-8 w-8 text-gray-400 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye h-4 w-4">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-4xl font-bold"><?php echo number_format($user['balance'], 2); ?></span>
                        <span class="text-gray-400">USD ▾</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-400">
                        <span>= <?php echo number_format($btc_value, 8); ?> BTC</span>
                    </div>
                </div>
                    </div>
                    <div class="p-4 space-y-4">
                        <!-- TradingView Widget BEGIN -->
<div class="tradingview-widget-container">
  <div class="tradingview-widget-container__widget"></div>
  <a href="https://www.tradingview.com/" rel="noopener nofollow" target="_blank"><span class="blue-text">Track all markets on TradingView</span></a></div>
  <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-financials.js" async>
  {
  "isTransparent": false,
  "largeChartUrl": "",
  "displayMode": "regular",
  "width": 400,
  "height": 550,
  "colorTheme": "dark",
  "symbol": "NASDAQ:AMZN",
  "locale": "en"
}
  </script>

<!-- TradingView Widget END -->
                       
    </div>
    </div>
    </div>
    
        <div class="grid grid-cols-4 gap-4 p-4">
                        <div class="fixed bottom-0 left-0 right-0 bg-zinc-900 border-t border-zinc-800">
                            <div class="grid grid-cols-5 p-2">
                                <button class="flex flex-col items-center gap-1 py-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home w-5 h-5 text-gray-400">
                                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                    </svg>
                                <span class="text-xs text-gray-400">Home</span>
                            </button>
                            <button class="flex flex-col items-center gap-1 py-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-line-chart w-5 h-5 text-gray-400">
                                    <path d="M3 3v18h18"></path>
                                    <path d="m19 9-5 5-4-4-3 3"></path>
                                </svg>
                                <span class="text-xs text-gray-400">Markets</span>
                            </button>
                            <button class="flex flex-col items-center gap-1 py-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-left w-5 h-5 text-gray-400">
                                <path d="m16 3 4 4-4 4"></path>
                                <path d="M20 7H4"></path>
                                <path d="m8 21-4-4 4-4"></path>
                                <path d="M4 17h16"></path>
                            </svg>
                            <span class="text-xs text-gray-400">Trade</span>
                        </button>
                        <button class="flex flex-col items-center gap-1 py-1">
                            <img src="../assets/image/wallet.png" class="w-[24px] h-[24px]" alt="">
                            <span class="text-xs text-gray-400">Earn</span>
                        </button>
                        <button class="flex flex-col items-center gap-1 py-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wallet w-5 h-5 text-amber-500">
                                <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"></path>
                                <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"></path>
                            </svg>
                            <span class="text-xs text-amber-500">Investment</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
</body>
</html>