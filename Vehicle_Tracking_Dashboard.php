

<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
$car_db_id = intval($_GET['car_id'] ?? 0); // Get car id from URL

// Verify the owner owns this car
$stmt = $conn->prepare("SELECT * FROM cars WHERE id=? AND owner_id=?");
$stmt->bind_param("ii", $car_db_id, $owner_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if(!$car){
    die("Unauthorized or car not found");
}
?>
<script>
    var car_db_id = <?php echo $car_db_id; ?>;
</script>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vehicle Tracking Dashboard</title>
<link rel="stylesheet" href="Vehicle_Tracking_Dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2>Live Tracking</h2>
        <ul>
            <li class="active"><i class="fa fa-home"></i> Dashboard</li>
            <li><i class="fa fa-clock"></i> Trip History</li>
            <li><i class="fa fa-file"></i> Reports</li>
            <li><i class="fa fa-user"></i> Log Data</li>
            <li><i class="fa fa-sign-out-alt"></i> Logout</li>
        </ul>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <header class="topbar">
            <h3>Individual Vehicle Tracking</h3>
        </header>

        <section class="content">

            <!-- MAP -->
            <div class="map-box">
                <div id="map" style="width: 100%; height: 280px; border-radius: 16px;"></div>
                <div class="speed" id="speed">0 km/h</div>
            </div>

            <!-- VEHICLE INFO -->
            <div class="info-box">
                <h4>Vehicle Info</h4>
                <ul>
                    <li><strong>Vehicle:</strong> <span id="vehicle_id">-</span></li>
                    <li><strong>Location:</strong> <span id="location">-</span></li>
                    <li><strong>Speed:</strong> <span id="speed_info">0</span> km/h</li>
                    <li><strong>Fuel:</strong> <span id="fuel">0</span>%</li>
                    <li><strong>Battery:</strong> <span id="battery">0</span> V</li>
                    <li><strong>Ignition:</strong> <span id="ignition">-</span></li>
                    <li><strong>Engine:</strong> <span id="engine">-</span></li>
                    <li><strong>Seatbelt:</strong> <span id="seatbelt">-</span></li>
                    <li><strong>Last Update:</strong> <span id="timestamp">-</span></li>
                </ul>
            </div>

        </section>

        <!-- BOTTOM -->
        <section class="bottom">

            <div class="card">
                <h4>Trip Information</h4>
                <p>Odometer: <span id="odometer">0</span> km</p>
                <p>Segment Distance: <span id="segment_distance">0</span> km</p>
            </div>

            <div class="card" id="alerts">
                <h4>Alerts</h4>
            </div>

            <div class="card">
                <h4>History Logs</h4>
                <p id="history">-</p>
            </div>

        </section>

    </main>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Custom JS -->
<script src="Vehicle_Tracking_Dashboard.js"></script>
</body>
</html>

