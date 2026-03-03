<?php
session_start();

// If already logged in → go to MAIN dashboard
if(isset($_SESSION['admin_id'])){
    header("Location: dashboard.php");
    exit();
}

$conn = new mysqli("localhost","caruser","Subha@123","car");
if($conn->connect_error){
    die("DB Connection Failed");
}

$error = '';

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, full_name, password FROM admins WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows === 1){
        $stmt->bind_result($id,$name,$hash);
        $stmt->fetch();

        // Verify hashed password
        if(password_verify($password,$hash)){
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_name'] = $name;

            // ✅ SAME dashboard for admin
            header("Location: dashboard.php");
            exit();
        }
    }

    $error = "Invalid email or password";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: Arial; background:#111; color:#fff; display:flex; justify-content:center; align-items:center; height:100vh; }
        form { background:#222; padding:30px; border-radius:10px; width:300px; }
        input { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:none; }
        button { width:100%; padding:10px; background:#00c3ff; border:none; color:#000; font-weight:bold; border-radius:5px; cursor:pointer; }
        p { text-align:center; color:red; }
    </style>
</head>
<body>

<form method="post">
    <h2 style="text-align:center;">Admin Login</h2>

    <input type="email" name="email" placeholder="Admin Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <button type="submit" name="login">Login</button>

    <?php if($error): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</form>

</body>
</html>

