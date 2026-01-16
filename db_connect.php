<?php
// Enable errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database credentials
$servername = "localhost";
$username   = "caruser";
$password   = "Subha@123";
$database   = "car";

// Connect
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>


