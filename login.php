<?php
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}

$conn = new mysqli("localhost","caruser","Subha@123","car");
if($conn->connect_error){
    die("Database Connection Failed");
}

$error = "";

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, full_name, owner_image, password FROM owners WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows === 1){
        $stmt->bind_result($id, $name, $photo, $hash);
        $stmt->fetch();

        if(password_verify($password, $hash)){
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_photo'] = !empty($photo) ? $photo : 'uploads/user.png';

            header("Location: dashboard.php");
            exit();
        }
    }

    $error = "Invalid email or password";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Owner Login</title>

<!-- Correct paths (same folder) -->
<link rel="stylesheet" href="login_style.css">
<script src="login.js" defer></script>

</head>
<body>

<canvas id="bg"></canvas>

<div class="container">
    <div class="card">
        <div class="card-content">
            <h2>Owner Login</h2>

            <form method="post" action="">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <button type="submit" name="login">Login</button>

                <?php if($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <div class="register-link">
                    <a href="owner_registration.php">Create Account</a>
                </div>

                <div class="forgot-password">
                    <a href="forgot_password.html">Forgot Password?</a>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>
<?php ob_end_flush(); ?>

