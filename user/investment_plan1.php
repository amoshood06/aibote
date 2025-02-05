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
    // Validate investment input
    $investment = filter_input(INPUT_POST, 'investment', FILTER_VALIDATE_FLOAT);

    if ($investment === false || $investment < 1000) {
        $message = "<p class='text-red-500'>Invalid amount! Enter between $1,000 - $100,000.</p>";
    } else {
        // Determine Investment Plan based on the amount
        if ($investment >= 1000 && $investment <= 10000) {
            $plan_name = "Starter Portfolio";
            $weekly_return = $investment * (2.5 / 100);
        } elseif ($investment > 10000 && $investment <= 50000) {
            $plan_name = "Growth Portfolio";
            $weekly_return = $investment * (4 / 100);
        } elseif ($investment > 50000 && $investment <= 100000) {
            $plan_name = "Advanced Portfolio";
            $weekly_return = $investment * (6 / 100);
        } else {
            $message = "<p class='text-red-500'>Maximum investment limit is $100,000.</p>";
        }

        if (!isset($message)) { // Only continue if no error has occurred
            if ($current_balance >= $investment) {
                // Deduct the investment amount from the user's balance
                $new_balance = $current_balance - $investment;
                try {
                    $update_sql = "UPDATE users SET balance = ? WHERE id = ?";
                    $stmt = $pdo->prepare($update_sql);
                    $stmt->execute([$new_balance, $user_id]);

                    // Set start and end dates for the investment
                    $start_date = date('Y-m-d');
                    $end_date = date('Y-m-d', strtotime('+30 days'));

                    // Insert investment record into the investments table
                    $insert_sql = "INSERT INTO investments (user_id, plan_name, amount, weekly_return, start_date, end_date) 
                                   VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($insert_sql);
                    $stmt->execute([$user_id, $plan_name, $investment, $weekly_return, $start_date, $end_date]);

                    // Success message
                    $_SESSION['success_message'] = "Investment successful! Weekly Return: $$weekly_return. Ends on $end_date";
                    header("Location: investment.php");
                    exit();
                } catch (Exception $e) {
                    // Handle any errors that occur during the database operations
                    $message = "<p class='text-red-500'>Error: " . $e->getMessage() . "</p>";
                    error_log($e->getMessage()); // Log the error message for further debugging
                }
            } else {
                $message = "<p class='text-red-500'>Insufficient balance. Please deposit more funds.</p>";
            }
        }
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
                    <a href="logout.php" class="inline-flex items-center justify-center rounded-full bg-[#FBC531] px-4 py-2 text-sm font-medium text-black hover:bg-neon/90">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-8">
        <section class="py-20">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-700 mb-4">Investment Plans</h2>
                <p class="text-gray-500 mb-12">Choose a plan based on your investment amount and get started.</p>
            </div>

            <?php
            // Define the investment plans
            $plans = [
                ["Starter Portfolio", 1000, 10000, 2.5],
                ["Growth Portfolio", 10000, 50000, 4],
                ["Advanced Portfolio", 50000, 100000, 6],
                ["Premium Portfolio", 100000, 999999999, 8]
            ];

            // Display each investment plan
            foreach ($plans as $plan):
            ?>
                <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg mb-8 mx-auto">
                    <h3 class="text-2xl font-semibold text-gray-700 mb-4"><?= htmlspecialchars($plan[0]) ?></h3>
                    <p class="text-gray-600 mb-4">Investment: $<?= number_format($plan[1]) ?> - $<?= number_format($plan[2]) ?></p>
                    <p class="text-gray-600 mb-4">Weekly Return: <?= $plan[3] ?>%</p>
                    <form  method="POST">
                        <input type="number" name="investment" min="<?= $plan[1] ?>" max="<?= $plan[2] ?>" step="100" required
                            class="w-full p-3 border rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
                            placeholder="Enter investment amount">
                        <button type="submit" class="w-full bg-[#FBC531] hover:bg-blue-700 text-white font-bold py-2 rounded-lg">
                            Invest Now
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>

            <!-- Display error or success messages -->
            <?php if (!empty($message)): ?>
                <div class="mt-8 text-center text-red-500 font-bold">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mt-8 text-center text-green-500 font-bold">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const closeMenuButton = document.getElementById('close-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && closeMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', () => {
                    mobileMenu.classList.toggle('-translate-x-full');
                });

                closeMenuButton.addEventListener('click', () => {
                    mobileMenu.classList.toggle('-translate-x-full');
                });
            }
        });
    </script>
</body>
</html>
