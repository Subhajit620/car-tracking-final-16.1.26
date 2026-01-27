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
    $status = $_POST['status'];

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
<title>Edit Car</title>
<style>
* {margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif;}
body{min-height:100vh; display:flex; justify-content:center; align-items:center; background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);}
.card{width:420px; padding:30px; background:rgba(255,255,255,0.12); backdrop-filter:blur(12px); border-radius:16px; box-shadow:0 20px 40px rgba(0,0,0,0.4); color:#fff;}
.card h2{text-align:center; margin-bottom:25px; letter-spacing:1px;}
.card input, .card select{width:100%; padding:12px; margin-bottom:15px; border-radius:10px; border:none; outline:none; background:rgba(255,255,255,0.2); color:#fff; font-size:14px;}
.card input::placeholder{color:#ddd;}
.card select option{color:#000;}
.card button{width:100%; padding:12px; border:none; border-radius:10px; background:linear-gradient(135deg,#00c6ff,#0072ff); color:#fff; font-size:15px; cursor:pointer; transition:0.3s;}
.card button:hover{transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,0.3);}
.cancel{display:block; text-align:center; margin-top:15px; color:#ffb3b3; text-decoration:none;}
.cancel:hover{text-decoration:underline;}
</style>
</head>
<body>

<div class="card">
    <h2>Edit Vehicle</h2>
    <form method="post">
        <input type="text" name="car_name" value="<?= htmlspecialchars($car['car_name']); ?>" placeholder="Car Name" required>
        <input type="text" name="car_model" value="<?= htmlspecialchars($car['car_model']); ?>" placeholder="Car Model" required>
        <input type="text" name="driver_name" value="<?= htmlspecialchars($car['driver_name']); ?>" placeholder="Driver Name" required>
        <input type="text" name="gps_device_id" value="<?= htmlspecialchars($car['gps_device_id']); ?>" placeholder="GPS Device ID" required>

        <select name="status" required>
            <option value="Online" <?= $car['status']=="Online" ? "selected" : "" ?>>Online</option>
            <option value="Offline" <?= $car['status']=="Offline" ? "selected" : "" ?>>Offline</option>
        </select>

        <button type="submit" name="update_car">Update Vehicle</button>
    </form>

    <a href="dashboard.php" class="cancel">Cancel</a>
</div>

</body>
</html>

