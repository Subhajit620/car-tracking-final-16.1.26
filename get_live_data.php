<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "gpsuser", "Saha@2003", "car");
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$vehicle_id = "CAR001";

$sql = "SELECT * FROM gps_logs 
        WHERE vehicle_id='$vehicle_id' 
        ORDER BY timestamp DESC 
        LIMIT 1";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode($data);
} else {
    echo json_encode(["error" => "No data found"]);
}

$conn->close();
?>

