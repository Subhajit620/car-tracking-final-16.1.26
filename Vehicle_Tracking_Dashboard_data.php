<?php
header('Content-Type: application/json');
include 'db_connect.php';

if(!isset($_GET['car_id'])){
    echo json_encode(null);
    exit();
}

$car_id = intval($_GET['car_id']);

// Fetch the latest GPS log for this car
$stmt = $conn->prepare("
    SELECT *
    FROM gps_logs
    WHERE car_id = ?
    ORDER BY timestamp DESC
    LIMIT 1
");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if(!$data){
    echo json_encode(null);
    exit();
}

// Return as JSON
echo json_encode([
    'vehicle_id'      => $data['vehicle_id'] ?? 'N/A',
    'lat'             => $data['lat'],
    'lng'             => $data['lng'],
    'speed'           => $data['speed'],
    'seatbelt'        => $data['seatbelt'],
    'fuel_level'      => $data['fuel_level'],
    'engine_status'   => $data['engine_status'],
    'ignition'        => $data['ignition'],
    'battery_voltage' => $data['battery_voltage'],
    'odometer'        => $data['odometer'],
    'segment_distance'=> $data['segment_distance'],
    'timestamp'       => $data['timestamp']
]);
?>

