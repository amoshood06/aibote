<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
            <!-- Key icon -->
            <div class="mx-auto w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            
            <h2 class="text-2xl font-semibold text-gray-900">Forgot password?</h2>
            <p class="mt-2 text-gray-600">No worries, we'll send you reset instructions.</p>
        </div>

        <form action="send_reset_link.php" method="POST" class="mt-8 space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    placeholder="Enter your email">
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Reset password
            </button>
        </form>

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