<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: user/index.php"); // Redirect logged-in users to the dashboard
    exit();
}

require 'db/db_connection.php';

$message = '';
$message_type = '';

// Function to generate a unique referral code
function generateReferralCode($length = 8) {
    return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length);
}

// Get referral code from URL if available
$referred_by = isset($_GET['ref']) ? $_GET['ref'] : NULL;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic form validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
        $message_type = "error";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $message = "Email is already registered.";
            $message_type = "error";
        } else {
            // Generate unique referral code for the new user
            $referral_code = generateReferralCode();

            // Insert user into database
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, referral_code, referred_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, password_hash($password, PASSWORD_BCRYPT), $referral_code, $referred_by]);

            $message = "Registration successful! Redirecting to login...";
            $message_type = "success";

            // Redirect to login after 3 seconds
            echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 3000);</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-black flex justify-center items-center min-h-screen">

    <div class="bg-white p-8 rounded-[50px] shadow-md w-full max-w-md">
        <h1 class="text-xl font-semibold mb-2">Create an account</h1>
        <p class="text-gray-600 text-sm mb-6">Sign up and get started</p>

        <form method="post" action="" class="space-y-4">
            <input type="hidden" name="referred_by" value="<?php echo htmlspecialchars($referred_by); ?>">

            <div>
                <label for="full_name" class="block text-sm text-gray-600 mb-1.5">Full name</label>
                <input type="text" id="full_name" name="full_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none text-sm" required>
            </div>

            <div>
                <label for="email" class="block text-sm text-gray-600 mb-1.5">Email</label>
                <input type="email" id="email" name="email" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none text-sm" required>
            </div>

            <div>
                <label for="password" class="block text-sm text-gray-600 mb-1.5">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none text-sm" required>
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full bg-amber-300 hover:bg-amber-400 text-black py-2 rounded-lg transition duration-200 text-sm font-medium">
                Register
            </button>

            <p class="text-gray-600 text-xs text-center">
                Have an account? <a href="login.php" class="text-black hover:underline">Sign in</a>
            </p>
        </form>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.querySelector('#password + button i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
    <?php if ($message): ?>
        toastr.<?= $message_type ?>("<?= $message ?>");
    <?php endif; ?>
    </script>
</body>
</html>
