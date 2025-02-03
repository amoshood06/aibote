<?php
require 'db/db_connection.php';
session_start();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['welcome_message'] = "Welcome back, " . $_SESSION['full_name'] . "!"; // Set welcome message
        header("Location: user/index.php"); // Redirect to dashboard
        exit();
    } else {
        $message = "Invalid email or password.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-black justify-center items-center">
    <!--login-->
        <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8">
            <div class="hidden lg:block w-[400px] justify-center items-center p-8 pr-[100px]">
                <!--image-->
                <img src="assets/image/pngwing.com (20).png" alt="" class="w-[400px] animate-bounce">
            </div>
            <div class="mt-6 sm:mt-8 lg:mt-12 bg-white p-8 rounded-[50px]">
                    <h1 class="text-xl sm:text-2xl font-semibold mb-1 sm:mb-2">Login an account</h1>
                    <p class="text-gray-600 text-sm sm:text-base mb-6 sm:mb-8">Get started</p>

                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST"  class="space-y-4 sm:space-y-6">
                       
                        <div>
                            <label for="email" class="block text-sm text-gray-600 mb-1.5 sm:mb-2">Email</label>
                            <input type="email" id="email" name="email" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl border border-gray-200 focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none transition text-sm sm:text-base" placeholder="amélielaurent7622@gmail.com">
                        </div>

                        <div>
                            <label for="password" class="block text-sm text-gray-600 mb-1.5 sm:mb-2">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl border border-gray-200 focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none transition text-sm sm:text-base" placeholder="******************">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-amber-300 hover:bg-amber-400 text-black py-2.5 sm:py-3 rounded-lg sm:rounded-xl transition duration-200 text-sm sm:text-base font-medium">
                            Login
                        </button>

                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <button class="flex-1 flex items-center justify-center gap-2 border border-gray-200 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl hover:bg-gray-50 transition text-sm sm:text-base">
                                <a href="forgot_password.php">
                                <i class="fa fa-lock"></i>
                                Forget Password
                                </a>
                            </button>
                            <button class="flex-1 flex items-center justify-center gap-2 border border-gray-200 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl hover:bg-gray-50 transition text-sm sm:text-base">
                                <i class="fab fa-google"></i>
                                Google
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0 text-xs sm:text-sm">
                        <p class="text-gray-600">
                            Have any account? <a href="register.php" class="text-black hover:underline">Sign in</a>
                        </p>
                        <a href="#" class="text-gray-600 hover:underline">Terms & Conditions</a>
                    </div>
                </div>
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