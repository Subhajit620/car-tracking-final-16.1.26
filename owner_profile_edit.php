<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost","caruser","Subha@123","car");
if ($conn->connect_error) die("DB Error");

$user_id = $_SESSION['user_id'];
$msg = "";

/* Fetch user data (we still need it for password/image update, but won't prefill form) */
$stmt = $conn->prepare("SELECT * FROM owners WHERE id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* Update profile */
if (isset($_POST['update'])) {

    $full_name = $_POST['full_name'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $dob       = $_POST['dob'];
    $gender    = $_POST['gender'];
    $aadhaar   = $_POST['aadhaar'];
    $address   = $_POST['address'];

    $password = !empty($_POST['password'])
        ? password_hash($_POST['password'], PASSWORD_DEFAULT)
        : $user['password'];

    if (!empty($_FILES['owner_image']['name'])) {
        if (!is_dir("uploads")) mkdir("uploads");
        $img = time().'_'.$_FILES['owner_image']['name'];
        move_uploaded_file($_FILES['owner_image']['tmp_name'], "uploads/".$img);
    } else {
        $img = $user['owner_image'];
    }

    $stmt = $conn->prepare("
        UPDATE owners SET
        full_name=?, email=?, phone=?, dob=?, gender=?, aadhaar=?,
        address=?, password=?, owner_image=? WHERE id=?
    ");
    $stmt->bind_param(
        "sssssssssi",
        $full_name,$email,$phone,$dob,$gender,$aadhaar,
        $address,$password,$img,$user_id
    );
    $stmt->execute();

    $msg = "Profile updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<style>
* {margin:0;padding:0;box-sizing:border-box;font-family:"Segoe UI",sans-serif;}
body {
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: radial-gradient(circle at top left, rgba(255,255,255,.05), rgba(0,0,0,.4)),
                url('car_img/owner_reg.jpg') center/cover no-repeat;
}
.container {
    width:600px;
    max-width:90%;
    padding:25px;
    border-radius:20px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(12px);
    border:1px solid rgba(255,255,255,0.15);
    box-shadow:0 10px 20px rgba(0,0,0,.5);
}
.logo-container {text-align:center; margin-bottom:15px;}
.form-logo {width:80px; height:80px;}
h2 {text-align:center; margin-bottom:20px; color:#000;}
form {display:grid; grid-template-columns: repeat(2,1fr); gap:15px;}
.field {position:relative;}
.field.full {grid-column:1 / -1;}
.field input,
.field select,
.field textarea {
    width:100%;
    padding:10px;
    background: rgba(255,255,255,0.15);
    border:none;
    border-bottom:2px solid #000;
    border-radius:8px;
    color:#000;
}
.field input:focus,
.field select:focus,
.field textarea:focus {border-color:#000; outline:none;}
.field label{
    position:absolute; left:10px; top:50%; transform:translateY(-50%);
    pointer-events:none; transition:0.3s; color:#000;
}
.field input:focus + label,
.field input:valid + label,
.field textarea:focus + label,
.field textarea:valid + label,
.field select:focus + label,
.field select:valid + label {
    top:-8px; font-size:12px; color:#000;
}
button[name="update"]{
    grid-column:1 / -1;
    padding:12px;
    border:none;
    border-radius:20px;
    background:linear-gradient(45deg,#000,#555);
    color:#fff;
    font-weight:bold;
    cursor:pointer;
    margin-top:10px;
    transition:0.3s;
}
button[name="update"]:hover {transform:scale(1.05);}
.popup{
    text-align:center;
    background: rgba(0,0,0,.2);
    padding:8px;
    margin-bottom:10px;
    color:#0f0;
    font-weight:bold;
    border-radius:6px;
}
/* Back Button at Bottom */
.back-bottom{
    display:block;
    margin:20px auto 0 auto;
    width:200px;
    text-align:center;
    padding:10px;
    background:#000;
    color:#fff;
    border-radius:20px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
}
.back-bottom:hover{
    background:#555;
}
</style>
</head>
<body>

<div class="container">

    <div class="logo-container">
        <img src="car_img/carlogo.png" class="form-logo">
    </div>

    <h2>Edit Profile</h2>

    <?php if($msg): ?><div class="popup"><?= $msg ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" autocomplete="off">

        <div class="field">
            <input type="text" name="full_name" placeholder=" " required autocomplete="off">
            <label>Full Name</label>
        </div>

        <div class="field">
            <input type="email" name="email" placeholder=" " required autocomplete="off">
            <label>Email</label>
        </div>

        <div class="field">
            <input type="tel" name="phone" placeholder=" " required autocomplete="off">
            <label>Phone</label>
        </div>

        <div class="field">
            <input type="date" name="dob" placeholder=" " required autocomplete="off">
            <label>DOB</label>
        </div>

        <div class="field">
            <select name="gender" required autocomplete="off">
                <option value="" selected disabled>Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
            </select>
            <label>Gender</label>
        </div>

        <div class="field">
            <input type="text" name="aadhaar" maxlength="12" placeholder=" " required autocomplete="off">
            <label>Aadhaar</label>
        </div>

        <div class="field full">
            <textarea name="address" placeholder=" " required autocomplete="off"></textarea>
            <label>Address</label>
        </div>

        <div class="field">
            <input type="password" name="password" placeholder=" " autocomplete="new-password">
            <label>New Password (optional)</label>
        </div>

        <div class="field">
            <input type="file" name="owner_image">
            <label>Profile Image</label>
        </div>

        <button name="update">Update Profile</button>
    </form>

    <!-- Back to Dashboard Button -->
    <a href="dashboard.php" class="back-bottom">← Back to Dashboard</a>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const popup = document.querySelector('.popup');
    if(popup) setTimeout(()=>popup.style.display='none',2500);
});
</script>

</body>
</html>
