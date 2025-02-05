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
                        neon: '#C1FF00',
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
                    <a href="index.php" class="hover:text-primary-400">Dashboard</a>
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
                <a href="index.php" class="block text-gray-300 hover:text-white">Dashboard</a>
                <a href="#" class="block text-gray-300 hover:text-white">Trades</a>
                <a href="#" class="block text-gray-300 hover:text-white">Deposit</a>
                <a href="#" class="block text-gray-300 hover:text-white">Investment Plan</a>
                <a href="#" class="block text-gray-300 hover:text-white">Settings</a>
                <a href="logout.php" class="inline-flex items-center justify-center rounded-full bg-[#FBC531] px-4 py-2 text-sm font-medium text-black hover:bg-neon/90 focus:outline-none focus:ring-2 focus:ring-neon/50">Logout</a>
            </nav>
        </div>
    </div>

    <main class="container mx-auto px-4 sm:px-6 py-8">
       <!--investment plan-->
        <!--plan section price-->
        
        <section class="py-20">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                        Choose Your <span class="text-primary">Stock Investment</span> Plan
                    </h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">
                        Select the plan that aligns with your investment goals. Our tiered system offers increasing weekly returns based on your investment amount, backed by expert stock market analysis.
                    </p>
                </div>
    
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-gray-900 rounded-lg shadow-md p-6 border border-gray-200">
                        <h3 class="text-2xl font-bold mb-2">Plan 1</h3>
                        <p class="text-lg text-gray-600 mb-4">Starter Portfolio</p>
                        <div class="text-3xl font-bold mb-4">$1,000 - $10,000</div>
                        <div class="text-xl mb-4">Weekly Return: <span class="font-semibold text-primary">2.5%</span></div>
                        <ul class="mb-6 space-y-2">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Access to Blue-chip Stocks
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Weekly Market Reports
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Basic Portfolio Rebalancing
                            </li>
                        </ul>
                        <a href="investment_plan1.php" class="block w-full text-center py-2 px-4 bg-white text-neon border border-neon rounded hover:bg-neon hover:text-white transition duration-300">Start Investing</a>
                    </div>
    
                    <div class="bg-gray-900 rounded-lg shadow-md p-6 border border-gray-200">
                        <h3 class="text-2xl font-bold mb-2">Plan 2</h3>
                        <p class="text-lg text-gray-600 mb-4">Growth Portfolio</p>
                        <div class="text-3xl font-bold mb-4">$10,000 - $50,000</div>
                        <div class="text-xl mb-4">Weekly Return: <span class="font-semibold text-primary">4%</span></div>
                        <ul class="mb-6 space-y-2">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Diversified Stock Selection
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Bi-weekly Strategy Calls
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Dividend Reinvestment Option
                            </li>
                        </ul>
                        <a href="investment_plan1.php" class="block w-full text-center py-2 px-4 bg-white text-neon border border-neon rounded hover:bg-neon hover:text-white transition duration-300">Start Investing</a>
                    </div>
    
                    <div class="bg-gray-900 rounded-lg shadow-lg p-6 border-2 border-neon">
                        <h3 class="text-2xl font-bold mb-2">Plan 3</h3>
                        <p class="text-lg text-gray-600 mb-4">Advanced Portfolio</p>
                        <div class="text-3xl font-bold mb-4">$50,000 - $100,000</div>
                        <div class="text-xl mb-4">Weekly Return: <span class="font-semibold text-primary">6%</span></div>
                        <ul class="mb-6 space-y-2">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Access to IPOs
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Personalized Investment Strategy
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Quarterly 1-on-1 Advisor Meeting
                            </li>
                        </ul>
                        <a href="investment_plan1.php" class="block w-full text-center py-2 px-4 bg-neon text-white rounded hover:bg-neon-dark transition duration-300">Start Investing</a>
                    </div>
    
                    <div class="bg-gray-900 rounded-lg shadow-md p-6 border border-gray-200">
                        <h3 class="text-2xl font-bold mb-2">Plan 4</h3>
                        <p class="text-lg text-gray-600 mb-4">Premium Portfolio</p>
                        <div class="text-3xl font-bold mb-4">$100,000 and above</div>
                        <div class="text-xl mb-4">Weekly Return: <span class="font-semibold text-primary">8%</span></div>
                        <ul class="mb-6 space-y-2">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Exclusive High-growth Stocks
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                24/7 Dedicated Account Manager
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Tax-efficient Investment Structuring
                            </li>
                        </ul>
                        <a href="investment_plan1.php" class="block w-full text-center py-2 px-4 bg-white text-neon border border-neon rounded hover:bg-neon hover:text-white transition duration-300">Start Investing</a>
                    </div>
                </div>
    </section>
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
</body>
</html>