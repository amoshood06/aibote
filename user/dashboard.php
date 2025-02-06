<?php
// Start the session
session_start();

// Include the database connection file
include('../db/db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: ../login.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch user details including balance and referral code
$stmt = $pdo->prepare("SELECT full_name, balance, referral_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Ensure referral_code is set before using it
$referral_code = !empty($user['referral_code']) ? trim($user['referral_code']) : '';
$referral_link = $referral_code ? "https://bothighstock.com/register.php?ref=" . urlencode($referral_code) : '#';

// Function to fetch BTC rate from API and cache it
function getBtcRate() {
    $cache_file = "btc_rate_cache.json"; // Cache file location
    $cache_time = 300; // 5 minutes (300 seconds)

    // Check if the cache file exists and is still valid
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        // Use cached BTC rate if it's still valid
        $cached_data = json_decode(file_get_contents($cache_file), true);
        return $cached_data['btc_rate'] ?? 0;
    }

    // Fetch new BTC rate from API
    $api_url = "https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd";
    $response = @file_get_contents($api_url);

    // Check if the API response is valid
    if ($response !== false) {
        $data = json_decode($response, true);
        $btc_rate = $data['bitcoin']['usd'] ?? 0;

        // Save new BTC rate to cache file
        file_put_contents($cache_file, json_encode(['btc_rate' => $btc_rate, 'timestamp' => time()]));

        return $btc_rate;
    }

    return 0; // Return 0 if API fails
}

// Get the latest BTC/USD rate
$btc_rate = getBtcRate();

// Convert user's balance (USD) to BTC
$user_balance = $user['balance'] ?? 0;
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
                <button class="bg-[#FBC531] pl-[20px] pr-[20px] pt-[10px] pb-[10px] flex rounded-[20px]">
                    Logout
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw w-6 h-6">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                        <path d="M21 3v5h-5"></path>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                        <path d="M8 16H3v5"></path>
                    </svg>
                </button>
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
                        <div class="w-full h-[400px] rounded-xl overflow-hidden border border-zinc-800">
                            <div class="w-full h-[400px]">
                                <div class="tradingview-widget-container" style="width: 100%; height: 100%;">
                                    <style>
                                    .tradingview-widget-copyright {
                                        font-size: 13px !important;
                                        line-height: 32px !important;
                                        text-align: center !important;
                                        vertical-align: middle !important;
                                        /* @mixin sf-pro-display-font; */
                                        font-family: -apple-system, BlinkMacSystemFont, 'Trebuchet MS', Roboto, Ubuntu, sans-serif !important;
                                        color: #B2B5BE !important;
                                    }
    
                                    .tradingview-widget-copyright .blue-text {
                                        color: #2962FF !important;
                                    }
                                
                                    .tradingview-widget-copyright a {
                                        text-decoration: none !important;
                                        color: #B2B5BE !important;
                                    }
                                
                                    .tradingview-widget-copyright a:visited {
                                        color: #B2B5BE !important;
                                    }
                                
                                    .tradingview-widget-copyright a:hover .blue-text {
                                        color: #1E53E5 !important;
                                    }
                                
                                    .tradingview-widget-copyright a:active .blue-text {
                                        color: #1848CC !important;
                                    }
                                
                                    .tradingview-widget-copyright a:visited .blue-text {
                                        color: #2962FF !important;
                                    }
                                    </style>
        <iframe scrolling="no" allowtransparency="true" frameborder="0" src="https://www.tradingview-widget.com/embed-widget/advanced-chart/?locale=en#%7B%22autosize%22%3Atrue%2C%22symbol%22%3A%22BTCUSD%22%2C%22interval%22%3A%22D%22%2C%22timezone%22%3A%22Etc%2FUTC%22%2C%22theme%22%3A%22dark%22%2C%22style%22%3A%221%22%2C%22hide_top_toolbar%22%3Atrue%2C%22hide_legend%22%3Atrue%2C%22save_image%22%3Afalse%2C%22backgroundColor%22%3A%22rgba(0%2C%200%2C%200%2C%201)%22%2C%22width%22%3A%22100%25%22%2C%22height%22%3A%22100%25%22%2C%22utm_source%22%3A%22kzmgdrlxe20gwr2p23xk.lite.vusercontent.net%22%2C%22utm_medium%22%3A%22widget%22%2C%22utm_campaign%22%3A%22advanced-chart%22%2C%22page-uri%22%3A%22kzmgdrlxe20gwr2p23xk.lite.vusercontent.net%2F%22%7D" title="advanced chart TradingView widget" lang="en" style="user-select: none; box-sizing: border-box; display: block; height: 100%; width: 100%;"></iframe>
    </div>
    </div>
    </div>
    <div class="flex gap-2 text-sm">
        <button class="px-4 py-1 rounded-full bg-zinc-800 text-white">7d</button>
        <button class="px-4 py-1 rounded-full text-gray-400">30d</button>
        <button class="px-4 py-1 rounded-full text-gray-400">90d</button>
        <button class="px-4 py-1 rounded-full text-gray-400">180d</button>
    </div>
    <div class="text-xs text-gray-400">Last Updated: 2025-01-29 04:22 (UTC)</div>
    </div>
    <div class="p-4">
        <div class="bg-gradient-to-r from-gray-100 to-gray-300 p-4 rounded-xl flex justify-between items-center">
            <div class="text-black">
                <div class="font-medium">My Card</div>
                <div class="text-sm opacity-50">•••• 5967</div>
            </div>
            <img alt="Mastercard" class="h-8" src="/mastercard.svg"></div>
        </div>
        <div class="grid grid-cols-4 gap-4 p-4">
            <button class="flex flex-col items-center gap-2">
                <div class="bg-zinc-800 p-3 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-to-line w-6 h-6"><path d="M12 17V3"></path><path d="m6 11 6 6 6-6"></path><path d="M19 21H5"></path></svg></div><span class="text-sm">Deposit</span></button><button class="flex flex-col items-center gap-2"><div class="bg-zinc-800 p-3 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-to-line w-6 h-6"><path d="M5 3h14"></path><path d="m18 13-6-6-6 6"></path><path d="M12 7v14"></path></svg></div><span class="text-sm">Withdraw</span>
                    </button>
                    <button class="flex flex-col items-center gap-2">
                        <div class="bg-zinc-800 p-3 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-left w-6 h-6">
                                <path d="m16 3 4 4-4 4"></path>
                                <path d="M20 7H4"></path>
                                <path d="m8 21-4-4 4-4">

                                </path><path d="M4 17h16"></path>
                            </svg>
                        </div>
                        <span class="text-sm">Transfer</span>
                    </button>
                    <button class="flex flex-col items-center gap-2">
                        <div class="bg-zinc-800 p-3 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw w-6 h-6">
                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                <path d="M21 3v5h-5"></path>
                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                <path d="M8 16H3v5"></path></svg></div><span class="text-sm">Convert</span>
                            </button>
                        </div>
                        <div dir="ltr" data-orientation="horizontal" class="p-4">
                            <div role="tablist" aria-orientation="horizontal" class="inline-flex h-10 items-center rounded-md p-1 text-muted-foreground bg-transparent border-b border-zinc-800 w-full justify-start gap-8" tabindex="0" data-orientation="horizontal" style="outline: none;">
                                <button type="button" role="tab" aria-selected="true" aria-controls="radix-:r0:-content-account" data-state="active" id="radix-:r0:-trigger-account" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:shadow-sm text-white data-[state=active]:text-white" tabindex="-1" data-orientation="horizontal" data-radix-collection-item="">Account</button>
                                <button type="button" role="tab" aria-selected="false" aria-controls="radix-:r0:-content-asset" data-state="inactive" id="radix-:r0:-trigger-asset" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:shadow-sm text-gray-400 data-[state=active]:text-white" tabindex="-1" data-orientation="horizontal" data-radix-collection-item="">Asset</button>
                            </div>
                        </div>
                        <div class="p-4 pb-[100px]">
                            <div class="flex justify-between items-center bg-zinc-900 p-4 rounded-xl">
                                <div>
                                    <div class="text-sm text-gray-400">Funding</div>
                                    <div class="font-medium">0.00 USD</div>
                                </div>
                                <div class="text-gray-400">›

                                </div>
                            </div>
                        </div>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw w-5 h-5 text-gray-400">
                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                <path d="M21 3v5h-5"></path>
                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                <path d="M8 16H3v5"></path>
                            </svg>
                            <span class="text-xs text-gray-400">Earn</span>
                        </button>
                        <button class="flex flex-col items-center gap-1 py-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wallet w-5 h-5 text-amber-500">
                                <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"></path>
                                <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"></path>
                            </svg>
                            <span class="text-xs text-amber-500">Assets</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
</body>
</html>