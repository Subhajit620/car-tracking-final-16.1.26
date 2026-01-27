<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if car ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$car_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Delete car only if it belongs to the logged-in user
$stmt = $conn->prepare("DELETE FROM cars WHERE id=? AND owner_id=?");
$stmt->bind_param("ii", $car_id, $user_id);
$stmt->execute();

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?>

