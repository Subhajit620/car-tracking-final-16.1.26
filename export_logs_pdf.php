<?php
ob_start(); // prevent "output already sent" errors
session_start();
require_once(__DIR__ . '/tcpdf/tcpdf.php');
include 'db_connect.php';

// ================= USER AUTH =================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
$car_id = intval($_GET['car_id'] ?? 0);

if ($car_id <= 0) {
    die("Invalid car ID");
}

// ================= VERIFY CAR BELONGS TO OWNER =================
$stmt = $conn->prepare("SELECT * FROM cars WHERE id=? AND owner_id=?");
$stmt->bind_param("ii", $car_id, $owner_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if (!$car) {
    die("Unauthorized");
}

// ================= FETCH LOGS =================
$whereClauses = ["car_id = ?"];
$params = [$car_id];
$types = "i";

// Date filter
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $whereClauses[] = "timestamp BETWEEN ? AND ?";
    $params[] = $_GET['from'] . " 00:00:00";
    $params[] = $_GET['to'] . " 23:59:59";
    $types .= "ss";
}

$whereSQL = implode(" AND ", $whereClauses);

$sql = "SELECT * FROM gps_logs WHERE $whereSQL ORDER BY timestamp DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

// Only bind if params exist
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) {
    die("No logs found for this vehicle.");
}

// ================= CREATE PDF =================
$pdf = new TCPDF();
$pdf->SetCreator('CarTrack');
$pdf->SetAuthor('CarTrack System');
$pdf->SetTitle('Vehicle Log Report');
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

// HTML Table
$html = '<h2>Vehicle Log Report - ' . htmlspecialchars($car['car_name']) . '</h2>';
$html .= '<table border="1" cellpadding="4">
<tr style="font-weight:bold;background-color:#eeeeee;">
<th>Date</th><th>Speed</th><th>Fuel</th><th>Odometer</th><th>Seatbelt</th><th>Engine</th><th>Ignition</th>
</tr>';

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
        <td>' . $row['timestamp'] . '</td>
        <td>' . ($row['speed'] !== null ? $row['speed'] : '-') . '</td>
        <td>' . ($row['fuel_level'] !== null ? $row['fuel_level'] : '-') . '</td>
        <td>' . ($row['odometer'] !== null ? $row['odometer'] : '-') . '</td>
        <td>' . ($row['seatbelt'] ? 'On' : 'Off') . '</td>
        <td>' . ($row['engine_status'] ? 'On' : 'Off') . '</td>
        <td>' . ($row['ignition'] ? 'On' : 'Off') . '</td>
    </tr>';
}

$html .= '</table>';

// Write HTML to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
ob_end_clean();
$pdf->Output('vehicle_logs.pdf', 'D');
exit;

