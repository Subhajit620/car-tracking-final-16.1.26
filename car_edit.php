<?php
session_start();
include 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Redirect if car ID not provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$car_id = intval($_GET['id']);

// Fetch car data
$stmt = $conn->prepare("SELECT * FROM cars WHERE id=? AND owner_id=?");
$stmt->bind_param("ii", $car_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if (!$car) {
    die("Car not found or you don't have permission.");
}

// Handle form submission
if (isset($_POST['update_car'])) {
    $car_name = trim($_POST['car_name']);
    $car_model = trim($_POST['car_model']);
    $driver_name = trim($_POST['driver_name']);
    $gps_device_id = trim($_POST['gps_device_id']);
    $status = $car['status']; // keep existing status (no logic change)

    $update = $conn->prepare(
        "UPDATE cars SET car_name=?, car_model=?, driver_name=?, gps_device_id=?, status=? 
         WHERE id=? AND owner_id=?"
    );
    $update->bind_param(
        "sssssii",
        $car_name,
        $car_model,
        $driver_name,
        $gps_device_id,
        $status,
        $car_id,
        $user_id
    );
    $update->execute();

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Vehicle</title>

<!-- FontAwesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: radial-gradient(circle at top left, #1e293b, #0b1120);
    color:#f1f5f9;
}

/* Breadcrumb */
.breadcrumb{
    position:absolute;
    top:30px;
    left:40px;
    font-size:14px;
    color:#94a3b8;
}

.breadcrumb span{
    color:#38bdf8;
}

/* Card */
.card{
    width:480px;
    padding:35px;
    background:#111827;
    border-radius:18px;
    box-shadow:0 25px 60px rgba(0,0,0,0.6);
    border:1px solid rgba(255,255,255,0.05);
    transition:0.3s ease;
}

.card:hover{
    box-shadow:0 0 40px rgba(56,189,248,0.3);
    transform:translateY(-3px);
}

/* Header */
.card h2{
    text-align:center;
    margin-bottom:15px;
    font-size:22px;
    font-weight:600;
    color:#38bdf8;
}

/* Status Badge */
.status-container{
    text-align:center;
    margin-bottom:20px;
}

.status-badge{
    display:inline-block;
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:500;
}

.active{
    background:#16a34a;
}

.inactive{
    background:#dc2626;
}

/* Divider */
.divider{
    height:1px;
    background:#1f2937;
    margin:20px 0;
}

/* Input Group */
.input-group{
    position:relative;
    margin-bottom:18px;
}

.input-group i{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:#94a3b8;
}

.input-group input{
    width:100%;
    padding:13px 15px 13px 40px;
    border-radius:10px;
    border:1px solid #1f2937;
    background:#0f172a;
    color:#fff;
    font-size:14px;
    transition:0.3s;
}

.input-group input:focus{
    border-color:#38bdf8;
    box-shadow:0 0 0 2px rgba(56,189,248,0.2);
    outline:none;
}

/* Button */
.card button{
    width:100%;
    padding:13px;
    border:none;
    border-radius:10px;
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#fff;
    font-size:15px;
    font-weight:500;
    cursor:pointer;
    transition:0.3s ease;
}

.card button:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(37,99,235,0.4);
}

/* Cancel */
.cancel{
    display:block;
    text-align:center;
    margin-top:18px;
    color:#94a3b8;
    text-decoration:none;
    font-size:14px;
    transition:0.3s;
}

.cancel:hover{
    color:#ef4444;
}

/* Responsive */
@media(max-width:500px){
    .card{
        width:90%;
        padding:25px;
    }
}
</style>
</head>
<body>

<div class="breadcrumb">
    Dashboard > Vehicles > <span>Edit Vehicle</span>
</div>

<div class="card">

    <h2><i class="fa fa-car"></i> Edit Vehicle</h2>

    <div class="status-container">
<?php
    // Fetch last GPS log for this car
    $stmt = $conn->prepare("SELECT timestamp FROM gps_logs WHERE car_id=? ORDER BY timestamp DESC LIMIT 1");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $lastLog = $res->fetch_assoc();

    $live_status = 'Offline';

    if ($lastLog) {
        $lastTime = strtotime($lastLog['timestamp']);
        $now = time();

        if (($now - $lastTime) <= 20) { 
            $live_status = 'Online';
        }
    }

    if ($live_status === 'Online'):
?>
        <span class="status-badge active">● Online</span>
<?php else: ?>
        <span class="status-badge inactive">● Offline</span>
<?php endif; ?>
</div>
    <div class="divider"></div>

    <form method="post">

        <div class="input-group">
            <i class="fa fa-car"></i>
            <input type="text" name="car_name"
                value="<?= htmlspecialchars($car['car_name']); ?>"
                placeholder="Car Name" required>
        </div>

        <div class="input-group">
            <i class="fa fa-tag"></i>
            <input type="text" name="car_model"
                value="<?= htmlspecialchars($car['car_model']); ?>"
                placeholder="Car Model" required>
        </div>

        <div class="input-group">
            <i class="fa fa-user"></i>
            <input type="text" name="driver_name"
                value="<?= htmlspecialchars($car['driver_name']); ?>"
                placeholder="Driver Name" required>
        </div>

        <div class="input-group">
            <i class="fa fa-satellite-dish"></i>
            <input type="text" name="gps_device_id"
                value="<?= htmlspecialchars($car['gps_device_id']); ?>"
                placeholder="GPS Device ID" required>
        </div>

        <button type="submit" name="update_car">
            <i class="fa fa-save"></i> Update Vehicle
        </button>
    </form>

    <a href="dashboard.php" class="cancel">Cancel</a>

</div>

</body>
</html>
