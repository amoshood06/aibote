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
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment Plans</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-black text-white h-full flex flex-col">
    <div class="min-h-screen bg-black text-white flex flex-col">
        <header class="flex justify-between items-center p-4">
            <h1 class="text-xl font-semibold">Investment Plans</h1>
            <button class="bg-[#FBC531] text-black pl-4 pr-4 pt-2 pb-2 rounded-full flex items-center">
                Logout
                <i class="ri-logout-box-r-line ml-2"></i>
            </button>
        </header>

        <main class="flex-grow p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Plan 1 -->
                <div class="bg-zinc-800 p-6 rounded-xl flex flex-col">
                    <h2 class="text-xl font-bold mb-2">Starter Portfolio</h2>
                    <p class="text-gray-400 mb-4">$1,000 - $10,000</p>
                    <ul class="flex-grow mb-4">
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Weekly Return: 2.5%</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Access to Blue-chip Stocks</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Weekly Market Reports</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Basic Portfolio Rebalancing</li>
                    </ul>
                    <button class="bg-[#FBC531] text-black font-bold py-2 px-4 rounded-full hover:bg-yellow-500 transition duration-300">
                        Start Investing
                    </button>
                </div>

                <!-- Plan 2 -->
                <div class="bg-zinc-800 p-6 rounded-xl flex flex-col">
                    <h2 class="text-xl font-bold mb-2">Growth Portfolio</h2>
                    <p class="text-gray-400 mb-4">$10,000 - $50,000</p>
                    <ul class="flex-grow mb-4">
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Weekly Return: 4%</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Diversified Stock Selection</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Bi-weekly Strategy Calls</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Dividend Reinvestment Option</li>
                    </ul>
                    <button class="bg-[#FBC531] text-black font-bold py-2 px-4 rounded-full hover:bg-yellow-500 transition duration-300">
                        Start Investing
                    </button>
                </div>

                <!-- Plan 3 -->
                <div class="bg-zinc-800 p-6 rounded-xl flex flex-col">
                    <h2 class="text-xl font-bold mb-2">Advanced Portfolio</h2>
                    <p class="text-gray-400 mb-4">$50,000 - $100,000</p>
                    <ul class="flex-grow mb-4">
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Weekly Return: 6%</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Access to IPOs</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Personalized Investment Strategy</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Quarterly 1-on-1 Advisor Meeting</li>
                    </ul>
                    <button class="bg-[#FBC531] text-black font-bold py-2 px-4 rounded-full hover:bg-yellow-500 transition duration-300">
                        Start Investing
                    </button>
                </div>

                <!-- Plan 4 -->
                <div class="bg-zinc-800 p-6 rounded-xl flex flex-col">
                    <h2 class="text-xl font-bold mb-2">Premium Portfolio</h2>
                    <p class="text-gray-400 mb-4">$100,000 and above</p>
                    <ul class="flex-grow mb-4">
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Weekly Return: 8%</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Exclusive High-growth Stocks</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>24/7 Dedicated Account Manager</li>
                        <li class="mb-2"><i class="ri-check-line text-green-500 mr-2"></i>Tax-efficient Investment Structuring</li>
                    </ul>
                    <button class="bg-[#FBC531] text-black font-bold py-2 px-4 rounded-full hover:bg-yellow-500 transition duration-300">
                        Start Investing
                    </button>
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
</body>
</html>

