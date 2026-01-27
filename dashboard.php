<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';

// ================= USER AUTH CHECK =================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_photo = (isset($_SESSION['user_photo']) && file_exists($_SESSION['user_photo'])) 
    ? $_SESSION['user_photo'] 
    : 'uploads/default.png';

// ================= HANDLE REMOVE CAR =================
if (isset($_GET['remove_id'])) {
    $remove_id = intval($_GET['remove_id']); // sanitize input

    // Delete only if this car belongs to logged-in user
    $stmt = $conn->prepare("DELETE FROM cars WHERE id=? AND owner_id=?");
    $stmt->bind_param("ii", $remove_id, $user_id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}

// ================= ADD NEW CAR =================
if (isset($_POST['add_car'])) {
    $car_name = trim($_POST['car_name']);
    $car_model = trim($_POST['car_model']);
    $driver_name = trim($_POST['driver_name']);
    $gps_device_id = trim($_POST['gps_device_id']);

    $stmt = $conn->prepare("INSERT INTO cars (owner_id, car_name, car_model, driver_name, gps_device_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'Offline', NOW())");
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $car_name, $car_model, $driver_name, $gps_device_id);
        $stmt->execute();
        header("Location: dashboard.php");
        exit();
    } else {
        die("Add car failed: " . $conn->error);
    }
}

// ================= FETCH CARS =================
$carsResult = $conn->query("SELECT * FROM cars WHERE owner_id=$user_id");

// Initialize stats
$totalCars = 0;
$carsOnline = 0;
$carsOffline = 0;

// Calculate live status based on last GPS log
$carsData = [];
if ($carsResult && $carsResult->num_rows > 0) {
    while ($car = $carsResult->fetch_assoc()) {
        $totalCars++;

        // Fetch last GPS log timestamp
        $stmt = $conn->prepare("SELECT timestamp FROM gps_logs WHERE car_id=? ORDER BY timestamp DESC LIMIT 1");
        $stmt->bind_param("i", $car['id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $lastLog = $res->fetch_assoc();

        $status = 'Offline';
        if ($lastLog) {
            $lastTime = strtotime($lastLog['timestamp']);
            $now = time();
            if (($now - $lastTime) <= 15) { // last 15 seconds
                $status = 'Online';
                $carsOnline++;
            } else {
                $carsOffline++;
            }
        } else {
            $carsOffline++;
        }

        $car['live_status'] = $status;
        $carsData[] = $car;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Car Tracking Dashboard</title>
<style>
/* ===== RESET ===== */
* {margin:0; padding:0; box-sizing:border-box; font-family:"Segoe UI",sans-serif; color:#fff;} 
body{ min-height:100vh; background: linear-gradient(135deg,#0f0c29,#302b63,#24243e); } 

/* ===== SIDEBAR ===== */ 
.sidebar{ width:220px; background:rgba(0,0,0,0.7); height:100vh; position:fixed; padding-top:30px; border-right:1px solid rgba(255,255,255,0.2); backdrop-filter:blur(6px); } 
.sidebar .logo{ color:#00ffff; font-size:22px; text-align:center; margin-bottom:30px; text-shadow:0 0 10px #ff00cc,0 0 10px #00ffff; } 
.sidebar a{ display:block; color:#ccc; padding:12px 20px; text-decoration:none; margin-bottom:6px; border-radius:8px; transition:0.3s; } 
.sidebar a.active, .sidebar a:hover{ background:#2a3a50; color:#fff; box-shadow:0 0 8px #ff00cc,0 0 8px #00ffff; } 
.sidebar .logout{ background:#e74c3c; color:#fff; margin-top:20px; text-align:center; border-radius:8px; box-shadow:0 0 8px #ff0000; } 

/* ===== MAIN ===== */ 
.main{ margin-left:240px; padding:30px; } 
.main h1{ text-align:center; color:#00ffff; text-shadow:0 0 10px #ff00cc,0 0 15px #00ffff; margin-bottom:25px; } 

/* ===== PROFILE SECTION ===== */ 
.user-info{ text-align:center; margin-bottom:25px; } 
.profile-pic{ width:100px; height:100px; border-radius:50%; border:2px solid #00ffff; box-shadow:0 0 20px #ff00cc,0 0 20px #00ffff; margin:0 auto 10px; } 
.user-name{ font-weight:bold; margin-bottom:10px; color:#00ffff; text-shadow:0 0 10px #ff00cc; } 
.edit-profile-btn{ background:linear-gradient(135deg,#ff00cc,#00ffff); color:#fff; border:none; padding:8px 18px; border-radius:25px; font-size:14px; cursor:pointer; box-shadow:0 0 15px #ff00cc,0 0 15px #00ffff; transition:0.3s; } 
.edit-profile-btn:hover{ transform:scale(1.05); box-shadow:0 0 25px #ff00cc,0 0 25px #00ffff; } 

/* ===== CARDS ===== */ 
.cards{ display:flex; gap:20px; margin-bottom:25px; flex-wrap:wrap; } 
.card{ flex:1; text-align:center; font-weight:bold; font-size:18px; padding:20px; border-radius:12px; background: rgba(255,255,255,0.05); backdrop-filter: blur(6px); border:1px solid rgba(255,255,255,0.15); transition:0.3s; } 
.card:hover{ transform:translateY(-5px); box-shadow:0 0 20px #ff00cc,0 0 20px #00ffff; } 
.card.total{background: rgba(255,255,255,0.07);} 
.card.online{background:#2ecc71;} 
.card.offline{background:#e74c3c;} 

/* ===== CAR LIST & ADD CAR ===== */ 
.car-list, .add-car{ background: rgba(255,255,255,0.05); padding:20px; border-radius:12px; margin-bottom:20px; backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,0.15); } 
.car-list table{ width:100%; border-collapse:collapse; } 
.car-list table th, .car-list table td{ padding:10px; border-bottom:1px solid rgba(255,255,255,0.2); text-align:left; } 
button{padding:6px 12px; border:none; border-radius:6px; cursor:pointer;} 
button.view{background:#3498db;color:#fff;} 
button.edit{background:#2ecc71;color:#fff;} 
button.remove{background:#e74c3c;color:#fff;} 
.add-car form input{ padding:8px; margin-right:10px; margin-bottom:10px; border-radius:6px; border:1px solid #555; background:rgba(0,0,0,0.25); color:#fff; } 
.add-car form button{ background:#3498db;color:#fff; padding:8px 15px; border:none; border-radius:6px; cursor:pointer; } 
input::placeholder{color:#ccc;} 
.car-list input[type="text"]{ width:100%; padding:8px; margin-bottom:10px; border-radius:6px; border:1px solid #555; background:rgba(0,0,0,0.25); color:#fff; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">🚗 CarTrack</div>
    <a class="active" href="#">Dashboard</a>
    <a href="#">Alerts</a>
    <a href="#">Settings</a>
    <a href="#">Help</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="main">
    <h1>Car Tracking Dashboard</h1>

    <!-- PROFILE SECTION -->
    <div class="user-info">
        <img src="<?php echo $user_photo; ?>" class="profile-pic" alt="User">
        <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
        <form action="owner_profile_edit.php" method="get">
            <button type="submit" class="edit-profile-btn">Edit Profile</button>
        </form>
    </div>

    <!-- CAR STATS -->
    <div class="cards">
        <div class="card total">Total Cars<br><span><?php echo $totalCars; ?></span></div>
        <div class="card online">Cars Online<br><span><?php echo $carsOnline; ?></span></div>
        <div class="card offline">Cars Offline<br><span><?php echo $carsOffline; ?></span></div>
    </div>

    <!-- CAR LIST -->
    <div class="car-list">
        <h2>Car List</h2>
        <input type="text" placeholder="Search Car..." onkeyup="searchCar(this)">
        <table id="carTable">
            <tr>
                <th>Car ID</th>
                <th>Car Name</th>
                <th>Car Model</th>
                <th>Status</th>
                <th>Live Location</th>
                <th>Action</th>
            </tr>
            <?php if(!empty($carsData)): ?>
                <?php foreach($carsData as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['car_id'] ?? $row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['car_model']); ?></td>
                    <td class="<?php echo strtolower($row['live_status']); ?>"><?php echo $row['live_status']; ?></td>
                    <td>
                        <a href="Vehicle_Tracking_Dashboard.php?car_id=<?php echo $row['id']; ?>">
                            <button class="view">View</button>
                        </a>
                    </td>
                    <td>
                        <a href="car_edit.php?id=<?php echo $row['id']; ?>"><button class="edit">Edit</button></a>
                        <a href="dashboard.php?remove_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to remove this car?')"><button class="remove">Remove</button></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No cars found</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- ADD CAR -->
    <div class="add-car">
        <h2>Add New Car</h2>
        <form method="post">
            <input type="text" name="car_name" placeholder="Car Name" required>
            <input type="text" name="car_model" placeholder="Car Model" required>
            <input type="text" name="driver_name" placeholder="Driver Name" required>
            <input type="text" name="gps_device_id" placeholder="GPS Device ID" required>
            <button type="submit" name="add_car">Add Car</button>
        </form>
    </div>
</div>

<script>
// Search car by ID, Name, or Model
function searchCar(input){
    let filter = input.value.toUpperCase();
    let table = document.getElementById("carTable");
    let tr = table.getElementsByTagName("tr");
    for(let i=1; i<tr.length; i++){
        let tds = tr[i].getElementsByTagName("td");
        let found = false;
        for(let j=0; j<3; j++){ // first 3 columns
            if(tds[j]){
                let txtValue = tds[j].textContent || tds[j].innerText;
                if(txtValue.toUpperCase().indexOf(filter) > -1){
                    found = true;
                    break;
                }
            }
        }
        tr[i].style.display = found ? "" : "none";
    }
}
</script>

</body>
</html>

