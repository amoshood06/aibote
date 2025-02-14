<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="./asset/toast/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</head>

<body class="bg-black flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h1 class="text-xl font-semibold mb-2 text-center">Login</h1>
        <p class="text-gray-600 text-sm text-center mb-6">Welcome back! Please enter your credentials.</p>

        <!-- LOGIN FORM -->
        <form id="loginForm" class="space-y-4">
            <div>
                <label for="email" class="block text-sm text-gray-600 mb-1">Email</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none text-sm" required placeholder="Enter your email">
            </div>

            <div>
                <label for="password" class="block text-sm text-gray-600 mb-1">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none text-sm" required placeholder="Enter your password">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full bg-amber-300 hover:bg-amber-400 text-black py-3 rounded-lg transition duration-200 text-sm font-medium">
                Login
            </button>

            <div class="text-center mt-4">
                <a href="forgot_password.php" class="text-gray-600 hover:underline">Forgot Password?</a>
            </div>
        </form>
    </div>

    <!-- TOGGLE PASSWORD VISIBILITY -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

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

    <!-- AJAX LOGIN REQUEST -->
    <script>
        $(document).ready(function () {
            $("#loginForm").on("submit", function (e) {
                e.preventDefault(); // Prevent normal form submission

                var formData = $(this).serialize(); // Get form data

                $.ajax({
                    type: "POST",
                    url: "process-login.php", // PHP script handling login
                    data: formData,
                    dataType: "json",
                    success: function (response) {
                        toastr.options = {
                            "closeButton": true,
                            "progressBar": true,
                            "positionClass": "toast-top-right",
                            "timeOut": "3000"
                        };

                        if (response.status === "success") {
                            toastr.success(response.message, "Login Successful");
                            setTimeout(function () {
                                window.location.href = "user/index.php"; // Redirect to dashboard
                            }, 2000);
                        } else {
                            toastr.error(response.message, "Login Failed");
                        }
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
