<?php
// Simulated user data - in a real application, this would come from a database
$user = [
    'username' => 'cryptotrader123',
    'balance' => 5000.00,
];

// Portfolio details
$portfolio = [
    'name' => 'Starter Portfolio',
    'min_investment' => 1000,
    'max_investment' => 10000,
    'weekly_return' => 2.5,
    'features' => [
        'Access to Blue-chip Stocks',
        'Weekly Market Reports',
        'Basic Portfolio Rebalancing'
    ]
];

// Simulated calculation function
function calculateReturns($investment, $weeks) {
    global $portfolio;
    $rate = $portfolio['weekly_return'] / 100;
    return $investment * pow(1 + $rate, $weeks);
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starter Portfolio - Investment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-black text-white h-full flex flex-col">
    <div class="min-h-screen bg-black text-white flex flex-col">
        <header class="flex justify-between items-center p-4">
            <h1 class="text-xl font-semibold">Starter Portfolio</h1>
            <button class="bg-[#FBC531] text-black pl-4 pr-4 pt-2 pb-2 rounded-full flex items-center">
                Logout
                <i class="ri-logout-box-r-line ml-2"></i>
            </button>
        </header>

        <main class="flex-grow p-4 space-y-6">
            <div class="bg-zinc-800 p-6 rounded-xl">
                <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($portfolio['name']); ?></h2>
                <p class="text-xl mb-4">$<?php echo number_format($portfolio['min_investment']); ?> - $<?php echo number_format($portfolio['max_investment']); ?></p>
                <p class="text-lg mb-4">Weekly Return: <?php echo $portfolio['weekly_return']; ?>%</p>
                <ul class="list-disc list-inside mb-4">
                    <?php foreach ($portfolio['features'] as $feature): ?>
                        <li><?php echo htmlspecialchars($feature); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-zinc-800 p-6 rounded-xl">
                    <h3 class="text-xl font-semibold mb-4">Invest in Starter Portfolio</h3>
                    <form id="investForm" class="space-y-4">
                        <div>
                            <label for="investmentAmount" class="block text-sm font-medium text-gray-400">Investment Amount ($)</label>
                            <input type="number" id="investmentAmount" name="investmentAmount" min="<?php echo $portfolio['min_investment']; ?>" max="<?php echo $portfolio['max_investment']; ?>" step="100" class="mt-1 block w-full rounded-md bg-zinc-700 border-transparent focus:border-gray-500 focus:bg-zinc-600 focus:ring-0 text-white" required>
                        </div>
                        <div>
                            <label for="investmentPeriod" class="block text-sm font-medium text-gray-400">Investment Period (Weeks)</label>
                            <input type="number" id="investmentPeriod" name="investmentPeriod" min="1" max="52" class="mt-1 block w-full rounded-md bg-zinc-700 border-transparent focus:border-gray-500 focus:bg-zinc-600 focus:ring-0 text-white" required>
                        </div>
                        <button type="submit" class="w-full bg-[#FBC531] text-black font-bold py-2 px-4 rounded hover:bg-yellow-500 transition duration-300">
                            Invest Now
                        </button>
                    </form>
                </div>
                <div class="bg-zinc-800 p-6 rounded-xl">
                    <h3 class="text-xl font-semibold mb-4">Projected Returns</h3>
                    <canvas id="returnsChart"></canvas>
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

    <script>
        const ctx = document.getElementById('returnsChart').getContext('2d');
        let chart;

        function updateChart(investment, weeks) {
            const labels = Array.from({length: weeks}, (_, i) => `Week ${i + 1}`);
            const data = labels.map((_, i) => calculateReturns(investment, i + 1));

            if (chart) {
                chart.destroy();
            }

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Projected Returns',
                        data: data,
                        borderColor: '#FBC531',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return '$' + value.toFixed(2);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        document.getElementById('investForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const investment = parseFloat(document.getElementById('investmentAmount').value);
            const weeks = parseInt(document.getElementById('investmentPeriod').value);
            updateChart(investment, weeks);
        });

        // Initialize chart with default values
        updateChart(1000, 12);

        function calculateReturns(investment, weeks) {
            const rate = <?php echo $portfolio['weekly_return']; ?> / 100;
            return investment * Math.pow(1 + rate, weeks);
        }
    </script>
</body>
</html>

