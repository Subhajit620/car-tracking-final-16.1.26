<?php
session_start();
$conn = new mysqli("localhost","caruser","Subha@123","car");
if($conn->connect_error){
    die("DB connection failed: ".$conn->connect_error);
}else{
    echo "DB connected!";
}
?>
