<?php
session_start();

if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}

$servername = "localhost";
$username = "caruser";
$password = "Subha@123";
$dbname = "car";

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$error = '';

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, full_name, owner_image, password FROM owners WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $owner = $result->fetch_assoc();
        if(password_verify($password, $owner['password'])){
            $_SESSION['user_id'] = $owner['id'];
            $_SESSION['user_name'] = $owner['full_name'];
            $_SESSION['user_photo'] = $owner['owner_image'] ?: 'uploads/user.png';

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Owner Login</title>
<style>
body { font-family: Arial; background:#f5f6fa; }
form { background:#fff; padding:20px; width:300px; margin:100px auto; border-radius:10px; box-shadow:0 0 10px #ccc; }
input { width:100%; padding:10px; margin:5px 0; border-radius:5px; border:1px solid #ccc; }
button { padding:10px 20px; border:none; border-radius:5px; background:#3498db; color:#fff; cursor:pointer; }
p.error { color:red; text-align:center; }
</style>
</head>
<body>
<form method="post">
    <h2>Owner Login</h2>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="login">Login</button>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>
</form>
</body>
</html>
