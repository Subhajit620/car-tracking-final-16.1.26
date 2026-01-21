<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
$car_id = intval($_GET['car_id'] ?? 0);

if(!$car_id){
    die("Car ID is required.");
}

// Verify this car belongs to the logged-in owner
$stmt = $conn->prepare("SELECT * FROM cars WHERE id=? AND owner_id=?");
$stmt->bind_param("ii", $car_id, $owner_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if(!$car){
    die("Unauthorized or car not found.");
}

// Fetch latest weekly report for this car
$stmt = $conn->prepare("
    SELECT * 
    FROM weekly_reports 
    WHERE car_id = ? 
    ORDER BY week_end DESC 
    LIMIT 1
");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

// If no report yet, set defaults
$total_distance = $report['total_distance'] ?? 0;
$fuel_used      = $report['fuel_used'] ?? 0;
$avg_mileage    = $report['avg_mileage'] ?? 0;
$best_mileage   = $report['best_mileage'] ?? 0;
$week_start     = $report['week_start'] ?? '-';
$week_end       = $report['week_end'] ?? '-';

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Vehicle Weekly Report - <?php echo htmlspecialchars($car['car_name']); ?></title>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Custom CSS -->
<link rel="stylesheet" href="Vehicle_Mileage_Report.css">
</head>
<body class="flex min-h-screen">

<!-- ================= SIDEBAR ================= -->
<aside class="sidebar w-64 border-r border-slate-800 p-4">
    <div class="flex items-center gap-2 py-6 px-2">
        <div class="bg-blue-600 p-2 rounded-lg">
            <i data-lucide="map-pin" class="w-5 h-5 text-white"></i>
        </div>
        <span class="text-xl font-bold">Fleet Reports</span>
    </div>

    <nav class="space-y-2">
        <a href="Vehicle_Tracking_Dashboard.php" class="nav-link">
            <i data-lucide="layout-dashboard"></i> Dashboard
        </a>
    </nav>
</aside>

<!-- ================= MAIN ================= -->
<main class="flex-1 p-8">

    <!-- HEADER -->
    <header class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($car['car_name']); ?> - Weekly Mileage Report</h1>
        <p class="text-sm text-gray-400">Week: <?php echo $week_start; ?> to <?php echo $week_end; ?></p>
    </header>

    <!-- KPI CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="kpi-card">
            <p class="label"><i data-lucide="navigation"></i> Total Distance</p>
            <p class="value text-blue-400"><?php echo $total_distance; ?> <span>km</span></p>
        </div>

        <div class="kpi-card">
            <p class="label"><i data-lucide="fuel"></i> Fuel Used</p>
            <p class="value text-green-400"><?php echo $fuel_used; ?> <span>L</span></p>
        </div>

        <div class="kpi-card">
            <p class="label"><i data-lucide="gauge"></i> Avg Mileage</p>
            <p class="value text-orange-400"><?php echo $avg_mileage; ?> <span>km/L</span></p>
        </div>

        <div class="kpi-card">
            <p class="label"><i data-lucide="trending-up"></i> Best Mileage</p>
            <p class="value text-red-400"><?php echo $best_mileage; ?> <span>km/L</span></p>
        </div>
    </div>

    <!-- CHART -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="chart-container lg:col-span-2">
            <canvas id="mainChart"></canvas>
        </div>

        <div class="chart-container flex flex-col items-center justify-center">
            <canvas id="gaugeChart"></canvas>
        </div>
    </div>

</main>

<script>
lucide.createIcons();

/* ======================
   BAR + LINE CHART (REAL DATA)
====================== */

const labels = ["<?php echo $week_start . ' - ' . $week_end; ?>"];
const distanceData = [<?php echo $total_distance; ?>];
const mileageData = [<?php echo $avg_mileage; ?>];

const mainCtx = document.getElementById('mainChart');

new Chart(mainCtx, {
    data: {
        labels: labels,
        datasets: [
            {
                type: 'bar',
                label: 'Distance (km)',
                data: distanceData,
                backgroundColor: 'rgba(59,130,246,0.6)',
                borderRadius: 8,
                yAxisID: 'y'
            },
            {
                type: 'line',
                label: 'Mileage (km/L)',
                data: mileageData,
                borderColor: '#10b981',
                borderWidth: 3,
                tension: 0.4,        // ✅ CURVED LINE
                pointRadius: 6,
                pointBackgroundColor: '#10b981',
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#cbd5f5' } }
        },
        scales: {
            y: {
                ticks: { color: '#94a3b8' },
                grid: { color: '#1e293b' }
            },
            y1: {
                position: 'right',
                ticks: { color: '#10b981' },
                grid: { drawOnChartArea: false }
            },
            x: {
                ticks: { color: '#94a3b8' }
            }
        }
    }
});


/* ======================
   GAUGE CHART (REAL DATA)
====================== */

const mileage = <?php echo $avg_mileage; ?>;
const gaugeCtx = document.getElementById('gaugeChart');

new Chart(gaugeCtx, {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [mileage, 25 - mileage],
            backgroundColor: ['#f59e0b', '#1e293b'],
            borderWidth: 0,
            circumference: 180,
            rotation: 270,
            cutout: '85%'
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
        }
    },
    plugins: [{
        id: 'text',
        beforeDraw(chart) {
            const { ctx, width, height } = chart;
            ctx.save();
            ctx.font = 'bold 22px Inter';
            ctx.fillStyle = '#f8fafc';
            ctx.textAlign = 'center';
            ctx.fillText(`${mileage} km/L`, width / 2, height / 1.4);
            ctx.restore();
        }
    }]
});
</script>

</body>
</html>

