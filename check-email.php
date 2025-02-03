<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Your Email</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FBBF24' // Purple color from the design
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-8">
        <div class="text-center">
            <!-- Email icon -->
            <div class="mx-auto w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            
            <h2 class="text-2xl font-semibold text-gray-900">Check your email</h2>
            <p class="mt-2 text-gray-600">We sent a password reset link to<br/>your email</p>
        </div>

        <button 
            onclick="window.location.href='mailto:'" 
            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            Open email app
        </button>


        <div class="text-center text-sm">
            <span class="text-gray-600">Didn't receive the email?</span>
            <a href="forgot_password.php">
                <button class="text-primary hover:text-primary/90 font-medium ml-1">
                    Click to resend
                </button>
            </a>
            
        </div>

        <div class="text-center">
            <a href="login.php" class="text-sm text-gray-600 hover:text-primary flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to log in
            </a>
        </div>
    </div>
</body>
</html>