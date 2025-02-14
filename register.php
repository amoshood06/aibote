<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black flex justify-center items-center min-h-screen">

    <div class="bg-white p-8 rounded-[50px] shadow-md w-full max-w-md">
        <h1 class="text-xl font-semibold mb-2">Create an account</h1>
        <p class="text-gray-600 text-sm mb-6">Sign up and get started</p>

        <form id="registerForm" class="space-y-4">
            <div>
                <label for="full_name" class="block text-sm text-gray-600 mb-1.5">Full name</label>
                <input type="text" id="full_name" name="full_name" class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none text-sm" required>
            </div>

            <div>
                <label for="email" class="block text-sm text-gray-600 mb-1.5">Email</label>
                <input type="email" id="email" name="email" class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none text-sm" required>
            </div>

            <div>
                <label for="password" class="block text-sm text-gray-600 mb-1.5">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none text-sm" required>
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

    $(document).ready(function () {
        $("#registerForm").on("submit", function (e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                type: "POST",
                url: "process-register.php",
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
                        toastr.success(response.message);
                        setTimeout(() => { window.location.href = "login.php"; }, 2000);
                    } else {
                        toastr.error(response.message);
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
