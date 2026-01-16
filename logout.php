<?php
session_start();       // Start the session

// Remove all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect to login page
header("Location: index.html");
exit();
?>
