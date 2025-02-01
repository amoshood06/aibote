<?php
session_start();
include '../db/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user balance
$sql = "SELECT balance FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$current_balance = $user['balance'];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $investment = $_POST['investment'];
    
    if ($investment >= 1000 && $investment <= 10000) {
        $plan_name = "Starter Portfolio";
        $weekly_return = $investment * (2.5 / 100);
    } elseif ($investment > 10000 && $investment <= 50000) {
        $plan_name = "Growth Portfolio";
        $weekly_return = $investment * (4 / 100);
    } elseif ($investment > 50000 && $investment <= 100000) {
        $plan_name = "Advanced Portfolio";
        $weekly_return = $investment * (6 / 100);
    } elseif ($investment > 100000) {
        $plan_name = "Premium Portfolio";
        $weekly_return = $investment * (8 / 100);
    } else {
        $message = "<p class='text-red-500'>Invalid amount! Enter between $1,000 - $100,000.</p>";
        return;
    }

    if ($current_balance >= $investment) {
        // Deduct from user balance
        $new_balance = $current_balance - $investment;
        $update_sql = "UPDATE users SET balance = ? WHERE id = ?";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$new_balance, $user_id]);

        // Insert investment record
        $insert_sql = "INSERT INTO investments (user_id, plan_name, amount, weekly_return) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([$user_id, $plan_name, $investment, $weekly_return]);

        $message = "<p class='text-green-500 font-bold'>Investment successful! Weekly Return: <strong>$$weekly_return</strong></p>";
    } else {
        header("Location: deposit.php?error=low_balance");
        exit();
    }
}
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
       <!--investment plans-->
        <section class="py-20">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-700 mb-4">Investment Plans</h2>
                <p class="text-gray-500 mb-12">Choose a plan based on your investment amount and get started.</p>
            </div>

            <!-- Plan 1: Starter Portfolio -->
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg mb-8 mx-auto">
                <h3 class="text-2xl font-semibold text-gray-700 mb-4">Plan 1: Starter Portfolio</h3>
                <p class="text-gray-600 mb-4">Investment: $1,000 - $10,000</p>
                <p class="text-gray-600 mb-4">Weekly Return: 2.5%</p>
                <form method="POST">
                    <input type="number" name="investment" min="1000" max="10000" step="100" required
                        class="w-full p-3 border rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
                        placeholder="Enter investment amount">
                    <button type="submit" class="w-full bg-[#FBC531] hover:bg-blue-700 text-white font-bold py-2 rounded-lg">
                        Invest Now
                    </button>
                </form>
            </div>

            <!-- Plan 2: Growth Portfolio -->
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg mb-8 mx-auto">
                <h3 class="text-2xl font-semibold text-gray-700 mb-4">Plan 2: Growth Portfolio</h3>
                <p class="text-gray-600 mb-4">Investment: $10,000 - $50,000</p>
                <p class="text-gray-600 mb-4">Weekly Return: 4%</p>
                <form method="POST">
                    <input type="number" name="investment" min="10000" max="50000" step="100" required
                        class="w-full p-3 border rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
                        placeholder="Enter investment amount">
                    <button type="submit" class="w-full bg-[#FBC531] hover:bg-blue-700 text-white font-bold py-2 rounded-lg">
                        Invest Now
                    </button>
                </form>
            </div>

            <!-- Plan 3: Advanced Portfolio -->
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg mb-8 mx-auto">
                <h3 class="text-2xl font-semibold text-gray-700 mb-4">Plan 3: Advanced Portfolio</h3>
                <p class="text-gray-600 mb-4">Investment: $50,000 - $100,000</p>
                <p class="text-gray-600 mb-4">Weekly Return: 6%</p>
                <form method="POST">
                    <input type="number" name="investment" min="50000" max="100000" step="100" required
                        class="w-full p-3 border rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
                        placeholder="Enter investment amount">
                    <button type="submit" class="w-full bg-[#FBC531] hover:bg-blue-700 text-white font-bold py-2 rounded-lg">
                        Invest Now
                    </button>
                </form>
            </div>

            <!-- Plan 4: Premium Portfolio -->
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg mb-8 mx-auto">
                <h3 class="text-2xl font-semibold text-gray-700 mb-4">Plan 4: Premium Portfolio</h3>
                <p class="text-gray-600 mb-4">Investment: $100,000 and above</p>
                <p class="text-gray-600 mb-4">Weekly Return: 8%</p>
                <form method="POST">
                    <input type="number" name="investment" min="100000" required
                        class="w-full p-3 border rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
                        placeholder="Enter investment amount">
                    <button type="submit" class="w-full bg-[#FBC531] hover:bg-blue-700 text-white font-bold py-2 rounded-lg">
                        Invest Now
                    </button>
                </form>
            </div>

            <!-- Message Display -->
            <?php if (!empty($message)): ?>
                <div class="mt-8 text-center">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
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
