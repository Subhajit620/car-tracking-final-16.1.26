<?php
session_start();
require_once "db_connect.php";

$message = "";
$message_type = "";

// Step 1: Email submitted
if (isset($_POST['reset']) && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM owners WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $message = "No account found with this email.";
        $message_type = "error";
    } else {
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit;
    }

    $stmt->close();
}

// Step 2: New password submitted
if (isset($_SESSION['reset_email']) && isset($_POST['password'], $_POST['confirm_password'])) {
    $email = $_SESSION['reset_email'];
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE owners SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hashedPassword, $email);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "Password updated successfully!";
                $message_type = "success";
                unset($_SESSION['reset_email']); // clear session
            } else {
                $message = "Failed to update password.";
                $message_type = "error";
            }
        } else {
            $message = "Execution failed: " . $stmt->error;
            $message_type = "error";
        }

        $stmt->close();
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
input[type="password"], input[type="email"] {
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
    transition: 0.3s;
}
input:focus { box-shadow: 0 0 15px #00ffea inset, 0 0 25px #ff00ff inset; }
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
    box-shadow: 0 0 10px #00ffea, 0 0 20px #ff00ff;
    text-transform: uppercase;
    transition: 0.3s;
}
button:hover { box-shadow: 0 0 20px #00ffea, 0 0 40px #ff00ff; transform: scale(1.05); }

/* Cyberpunk popup animation */
.popup-message {
    position: fixed;
    top: -100px;
    left: 50%;
    transform: translateX(-50%);
    padding: 15px 35px;
    border-radius: 12px;
    font-weight: bold;
    z-index: 9999;
    font-size: 16px;
    text-shadow: 0 0 5px #000;
    transition: top 0.5s ease, opacity 0.5s ease, transform 0.3s;
    opacity: 0;
}
.popup-message.success {
    background: #28ff81;
    color: #0a0f2c;
    box-shadow: 0 0 15px #28ff81, 0 0 30px #00fff0, 0 0 60px #00fff0;
    animation: pulse 1s infinite alternate;
}
.popup-message.error {
    background: #ff2866;
    color: #fff;
    box-shadow: 0 0 15px #ff2866, 0 0 30px #ff00ff, 0 0 60px #ff00ff;
    animation: pulse 1s infinite alternate;
}

@keyframes pulse {
    0% { transform: translateX(-50%) scale(1); }
    50% { transform: translateX(-50%) scale(1.05); }
    100% { transform: translateX(-50%) scale(1); }
}
</style>
</head>
<body>
<div class="container">
    <h2>Reset Password</h2>

    <?php if(isset($_SESSION['reset_email'])): ?>
        <form action="" method="POST">
            <input type="password" name="password" placeholder="New password" required>
            <input type="password" name="confirm_password" placeholder="Confirm password" required>
            <button type="submit">Update Password</button>
        </form>
    <?php else: ?>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" name="reset">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

<?php if($message != ""): ?>
<div class="popup-message <?php echo $message_type; ?>" id="popupMessage">
    <?php echo $message; ?>
</div>
<script>
const popup = document.getElementById('popupMessage');
popup.style.top = '20px';
popup.style.opacity = 1;

<?php if($message_type=="success"): ?>
// Smooth glowing popup + redirect to login
setTimeout(() => { window.location.href = 'login.html'; }, 3500);
<?php else: ?>
// Hide error popup after 3s
setTimeout(() => { popup.style.top = '-100px'; popup.style.opacity = 0; }, 3000);
<?php endif; ?>
</script>
<?php endif; ?>
</body>
</html>
