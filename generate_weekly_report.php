<?php
include 'db_connect.php';

/*
 This script calculates weekly report for each car
 using gps_logs and stores into weekly_reports table
*/

$cars = $conn->query("SELECT id FROM cars");

while ($car = $cars->fetch_assoc()) {

    $car_id = $car['id'];

    // Current week (Monday → Sunday)
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $week_end   = date('Y-m-d', strtotime('sunday this week'));

    // Aggregate GPS logs
    $stmt = $conn->prepare("
        SELECT 
            SUM(segment_distance) AS total_distance,
            MIN(odometer) AS odometer_start,
            MAX(odometer) AS odometer_end,
            (MAX(fuel_level) - MIN(fuel_level)) AS fuel_used
        FROM gps_logs
        WHERE car_id = ?
        AND DATE(timestamp) BETWEEN ? AND ?
    ");

    $stmt->bind_param("iss", $car_id, $week_start, $week_end);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $total_distance = $row['total_distance'] ?? 0;
    $odometer_start = $row['odometer_start'] ?? 0;
    $odometer_end   = $row['odometer_end'] ?? 0;
    $fuel_used      = $row['fuel_used'] ?? 0;

    // Mileage calculation
    if ($fuel_used > 0) {
        $avg_mileage = $total_distance / $fuel_used;
    } else {
        $avg_mileage = 0;
    }

    $best_mileage = $avg_mileage; // simple logic for now

    // Insert or Update weekly report
    $insert = $conn->prepare("
        INSERT INTO weekly_reports
        (car_id, week_start, week_end, total_distance, fuel_used, avg_mileage, best_mileage, odometer_start, odometer_end)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            total_distance=VALUES(total_distance),
            fuel_used=VALUES(fuel_used),
            avg_mileage=VALUES(avg_mileage),
            best_mileage=VALUES(best_mileage),
            odometer_start=VALUES(odometer_start),
            odometer_end=VALUES(odometer_end),
            updated_at=CURRENT_TIMESTAMP
    ");

    $insert->bind_param(
        "issdddddd",
        $car_id,
        $week_start,
        $week_end,
        $total_distance,
        $fuel_used,
        $avg_mileage,
        $best_mileage,
        $odometer_start,
        $odometer_end
    );

    $insert->execute();
}

echo "✅ Weekly reports updated successfully!";
?>

