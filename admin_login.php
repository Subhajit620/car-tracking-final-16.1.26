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

        if(password_verify($password,$hash)){
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_name'] = $name;

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
    <title>Admin Login | Vehicle Tracking System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    height:100vh;
    display:flex;
    background: linear-gradient(-45deg,#0f2027,#203a43,#2c5364,#1c1c1c);
    background-size:400% 400%;
    animation: gradientBG 12s ease infinite;
}

@keyframes gradientBG{
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

/* Left branding section */
.left{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#fff;
    padding:40px;
}

.left h1{
    font-size:42px;
    font-weight:600;
}

.left p{
    margin-top:15px;
    font-size:16px;
    opacity:0.8;
}

/* Right login section */
.right{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
}

.login-box{
    width:380px;
    padding:40px;
    border-radius:15px;
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(15px);
    border:1px solid rgba(255,255,255,0.2);
    box-shadow:0 15px 35px rgba(0,0,0,0.5);
    color:#fff;
    animation: fadeIn 1s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}

.login-box h2{
    text-align:center;
    margin-bottom:25px;
}

.input-group{
    position:relative;
    margin-bottom:20px;
}

.input-group input{
    width:100%;
    padding:12px 40px 12px 15px;
    border-radius:8px;
    border:none;
    outline:none;
}

.input-group i{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#555;
}

.btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#00c3ff;
    color:#000;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    display:block;
    text-align:center;
}

.btn:hover{
    background:#00a2d4;
}

.btn.loading{
    pointer-events:none;
    opacity:0.7;
}

/* Cancel Button */
.cancel-btn{
    margin-top:10px;
    background:rgba(255,255,255,0.15);
    color:#fff;
    text-decoration:none;
}

.cancel-btn:hover{
    background:#ff4d4d;
    color:#fff;
}

.error{
    margin-top:15px;
    text-align:center;
    color:#ff4d4d;
    animation: shake 0.3s;
}

@keyframes shake{
    0%{transform:translateX(0);}
    25%{transform:translateX(-5px);}
    50%{transform:translateX(5px);}
    75%{transform:translateX(-5px);}
    100%{transform:translateX(0);}
}

.footer{
    text-align:center;
    margin-top:20px;
    font-size:12px;
    opacity:0.7;
}

/* Responsive */
@media(max-width:900px){
    body{flex-direction:column;}
    .left{display:none;}
}
</style>
</head>
<body>

<div class="left">
    <div>
        <h1>FleetTrack Pro</h1>
        <p>Enterprise Vehicle Monitoring & Live Tracking System</p>
    </div>
</div>

<div class="right">
    <div class="login-box">
        <h2><i class="fa fa-shield-alt"></i> Admin Login</h2>

        <form method="post" onsubmit="return loadingEffect()">

            <div class="input-group">
                <input type="email" name="email" placeholder="Admin Email" required>
            </div>

            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class="fa fa-eye" onclick="togglePassword()"></i>
            </div>

            <button type="submit" name="login" class="btn" id="loginBtn">
                Login
            </button>

            <!-- Cancel Button -->
            <a href="index.html" class="btn cancel-btn">
                Cancel
            </a>

            <?php if($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </form>

        <div class="footer">
            © <?= date("Y") ?> Vehicle Tracking System | Secure Access Only
        </div>
    </div>
</div>

<script>
function togglePassword(){
    var pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}

function loadingEffect(){
    var btn = document.getElementById("loginBtn");
    btn.classList.add("loading");
    btn.innerHTML = "Authenticating...";
    return true;
}
</script>

</body>
</html>
