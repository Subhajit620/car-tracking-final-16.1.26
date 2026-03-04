<?php
session_start();
include 'db_connect.php';

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
$car_id = intval($_GET['car_id'] ?? 0);

if(!$car_id){
    die("Car ID is required.");
}

// Verify car ownership
$stmt = $conn->prepare("SELECT * FROM cars WHERE id=? AND owner_id=?");
$stmt->bind_param("ii", $car_id, $owner_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if(!$car){
    die("Unauthorized or car not found.");
}

// ================================
// CALCULATE LAST 7 DAYS
// ================================
$week_start = date('Y-m-d 00:00:00', strtotime('-6 days')); // 7 days ago
$week_end   = date('Y-m-d 23:59:59'); // today

// Fetch weekly data from gps_logs table
$stmt = $conn->prepare("
    SELECT 
        SUM(segment_distance) AS total_distance,
        MAX(COALESCE(fuel_level,0)) - MIN(COALESCE(fuel_level,0)) AS fuel_used,
        CASE WHEN SUM(segment_distance) > 0 
             THEN SUM(segment_distance) / NULLIF(MAX(COALESCE(fuel_level,0)) - MIN(COALESCE(fuel_level,0)),0)
             ELSE 0 END AS avg_mileage,
        MAX(segment_distance) AS best_mileage
    FROM gps_logs
    WHERE car_id = ?
      AND timestamp BETWEEN ? AND ?
");
$stmt->bind_param("iss", $car_id, $week_start, $week_end);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

// Safe defaults
$total_distance = $report['total_distance'] ?? 0;
$fuel_used      = $report['fuel_used'] ?? 0;
$avg_mileage    = $report['avg_mileage'] ?? 0;
$best_mileage   = $report['best_mileage'] ?? 0;

// ================================
// GET DAILY DISTANCE FOR CHART
// ================================
$chart_labels = [];
$chart_data   = [];

$stmt = $conn->prepare("
    SELECT DATE(timestamp) AS day, SUM(segment_distance) AS distance
    FROM gps_logs
    WHERE car_id = ?
      AND timestamp BETWEEN ? AND ?
    GROUP BY DATE(timestamp)
    ORDER BY DATE(timestamp)
");
$stmt->bind_param("iss", $car_id, $week_start, $week_end);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
    $chart_labels[] = $row['day'];
    $chart_data[] = round($row['distance'], 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Weekly Report - <?php echo htmlspecialchars($car['car_name']); ?></title>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<link rel="stylesheet" href="Vehicle_Mileage_Report.css">
</head>
<body class="flex min-h-screen">

<aside class="sidebar w-64 border-r p-4">
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

<main class="flex-1 p-8">
    <header class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($car['car_name']); ?> - Weekly Report</h1>
        <p class="text-sm text-gray-400">Week: <?php echo $week_start; ?> to <?php echo $week_end; ?></p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="kpi-card">
            <p class="label"><i data-lucide="navigation"></i> Total Distance</p>
            <p class="value text-blue-400"><?php echo round($total_distance, 2); ?> km</p>
        </div>
        <div class="kpi-card">
            <p class="label"><i data-lucide="fuel"></i> Fuel Used</p>
            <p class="value text-green-400"><?php echo round($fuel_used, 2); ?> L</p>
        </div>
        <div class="kpi-card">
            <p class="label"><i data-lucide="gauge"></i> Avg Mileage</p>
            <p class="value text-orange-400"><?php echo round($avg_mileage, 2); ?> km/L</p>
        </div>
        <div class="kpi-card">
            <p class="label"><i data-lucide="trending-up"></i> Best Mileage</p>
            <p class="value text-red-400"><?php echo round($best_mileage, 2); ?> km/L</p>
        </div>
    </div>

    <!-- ================= CHART ================= -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <canvas id="weeklyDistanceChart"></canvas>
    </div>
</main>

<script>
lucide.createIcons();

const ctx = document.getElementById('weeklyDistanceChart').getContext('2d');
const weeklyDistanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Distance (km)',
            data: <?php echo json_encode($chart_data); ?>,
            backgroundColor: 'rgba(59, 130, 246, 0.7)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Daily Distance for Last 7 Days'
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>
