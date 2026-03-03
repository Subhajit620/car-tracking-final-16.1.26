<?php
session_start();
require_once "db_connect.php";

$message = "";
$message_type = "";

/* ================================
   STEP 1 — VERIFY TOKEN FROM EMAIL
================================ */

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $token_hash = hash('sha256', $token);

    $stmt = $conn->prepare("SELECT id FROM owners WHERE reset_token_hash=? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['reset_token'] = $token_hash; // valid token stored
    } else {
        $message = "Invalid or expired reset link.";
        $message_type = "error";
    }
}

/* ================================
   STEP 2 — UPDATE PASSWORD
================================ */

if (isset($_SESSION['reset_token']) && isset($_POST['password'], $_POST['confirm_password'])) {

    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $message_type = "error";
    }
    elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    }
    else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE owners 
            SET password=?, reset_token_hash=NULL, reset_expiry=NULL 
            WHERE reset_token_hash=?");
        $stmt->bind_param("ss", $hashedPassword, $_SESSION['reset_token']);

        if ($stmt->execute()) {
            $message = "Password updated successfully!";
            $message_type = "success";
            unset($_SESSION['reset_token']);
        } else {
            $message = "Something went wrong.";
            $message_type = "error";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - Cyberpunk</title>
<style>
/* KEEPING YOUR ORIGINAL STYLE EXACTLY */
body {
    font-family: 'Orbitron', sans-serif;
    background: url('car_img/login.jpg') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    overflow: hidden;
}
.container {
    background: rgba(10,15,44,0.95);
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 0 30px #00f0ff, 0 0 60px #ff00ff, 0 0 90px #00ffea;
    width: 380px;
    text-align: center;
    color: #fff;
}
h2 {
    font-size: 28px;
    margin-bottom: 25px;
    color: #00ffea;
    text-shadow: 0 0 8px #00ffea, 0 0 20px #ff00ff;
}
input[type="password"] {
    width: 100%;
    padding: 12px;
    margin: 10px 0 20px 0;
    border-radius: 8px;
    border: none;
    outline: none;
    background: rgba(255,255,255,0.05);
    color: #fff;
    font-size: 16px;
    box-shadow: 0 0 10px #00ffea inset, 0 0 20px #ff00ff inset;
}
button {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: none;
    background: #00ffea;
    color: #0a0f2c;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
}
.popup-message {
    position: fixed;
    top: -100px;
    left: 50%;
    transform: translateX(-50%);
    padding: 15px 35px;
    border-radius: 12px;
    font-weight: bold;
    z-index: 9999;
    opacity: 0;
    transition: 0.5s;
}
.popup-message.success { background:#28ff81; color:#000; }
.popup-message.error { background:#ff2866; color:#fff; }
</style>
</head>
<body>

<div class="container">
    <h2>Reset Password</h2>

    <?php if(isset($_SESSION['reset_token']) && $message_type!="success"): ?>
        <form method="POST">
            <input type="password" name="password" placeholder="New password" required>
            <input type="password" name="confirm_password" placeholder="Confirm password" required>
            <button type="submit">Update Password</button>
        </form>
    <?php endif; ?>
</div>

<?php if($message != ""): ?>
<div class="popup-message <?php echo $message_type; ?>" id="popupMessage">
    <?php echo $message; ?>
</div>
<script>
const popup = document.getElementById('popupMessage');
popup.style.top='20px';
popup.style.opacity=1;

<?php if($message_type=="success"): ?>
setTimeout(()=>{window.location.href='login.html';},3500);
<?php else: ?>
setTimeout(()=>{popup.style.top='-100px';popup.style.opacity=0;},3000);
<?php endif; ?>
</script>
<?php endif; ?>

</body>
</html>

