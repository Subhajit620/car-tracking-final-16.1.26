<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
$car_db_id = intval($_GET['car_id']); // cars.id

// Optional: verify owner owns the car
$stmt = $conn->prepare("SELECT * FROM cars WHERE id=? AND owner_id=?");
$stmt->bind_param("ii", $car_db_id, $owner_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if(!$car){
    die("Unauthorized or car not found");
}

// Pass $car_db_id to JS
?>
<script>
    var car_db_id = <?php echo $car_db_id; ?>;
</script>

