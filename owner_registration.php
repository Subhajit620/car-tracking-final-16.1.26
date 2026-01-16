<?php
session_start();

// === Enable error reporting (for debugging) ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === DATABASE CONNECTION ===
$servername = "localhost";
$username = "caruser";
$password = "Subha@123";
$dbname = "car";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$error_field = ''; // will store which field caused the error

if (isset($_POST['register'])) {
    // === Get form data ===
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $aadhaar = trim($_POST['aadhaar']);
    $address = trim($_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // === Handle profile image upload ===
    if (isset($_FILES['owner_image']) && $_FILES['owner_image']['name'] != '') {
        $filename = time() . "_" . basename($_FILES['owner_image']['name']);
        $target = "uploads/" . $filename;
        if (!move_uploaded_file($_FILES['owner_image']['tmp_name'], $target)) {
            $error = "Failed to upload image!";
        } else {
            $owner_image = $target;
        }
    } else {
        $owner_image = 'uploads/user.png';
    }

    if (!$error) {
        // === Check for duplicate email or Aadhaar ===
        $stmt = $conn->prepare("SELECT id, email, aadhaar FROM owners WHERE email=? OR aadhaar=?");
        $stmt->bind_param("ss", $email, $aadhaar);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($existing_id, $existing_email, $existing_aadhaar);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            if ($existing_email == $email) {
                $error = "Email is already registered!";
                $error_field = 'email';
            } else if ($existing_aadhaar == $aadhaar) {
                $error = "Aadhaar is already registered!";
                $error_field = 'aadhaar';
            }
        } else {
            // === Insert owner data safely ===
            $stmt = $conn->prepare(
                "INSERT INTO owners 
                (full_name,email,phone,dob,gender,aadhaar,address,password,owner_image) 
                VALUES (?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param(
                "sssssssss",
                $full_name,
                $email,
                $phone,
                $dob,
                $gender,
                $aadhaar,
                $address,
                $password,
                $owner_image
            );

            if ($stmt->execute()) {
                // === Save session and redirect ===
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_photo'] = $owner_image;

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed! Error: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Owner Registration</title>
<style>
body { font-family: Arial; background:#f5f6fa; }
form { background:#fff; padding:20px; width:400px; margin:50px auto; border-radius:10px; box-shadow:0 0 10px #ccc; }
input, select, textarea { width:100%; padding:10px; margin:5px 0; border-radius:5px; border:1px solid #ccc; }
input.error-field, textarea.error-field, select.error-field { border-color:red; }
button { padding:10px 20px; border:none; border-radius:5px; background:#3498db; color:#fff; cursor:pointer; }
p.error { color:red; text-align:center; }
</style>
</head>
<body>
<form method="post" enctype="multipart/form-data">
    <h2>Owner Registration</h2>

    <input type="text" name="full_name" placeholder="Full Name" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" required>

    <input type="email" name="email" placeholder="Email" 
           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
           <?php if($error_field=='email') echo 'class="error-field"'; ?> required>

    <input type="text" name="phone" placeholder="Phone" 
           value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>

    <input type="date" name="dob" value="<?php echo isset($dob) ? $dob : ''; ?>" required>

    <select name="gender" required>
        <option value="">Select Gender</option>
        <option value="Male" <?php if(isset($gender) && $gender=='Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if(isset($gender) && $gender=='Female') echo 'selected'; ?>>Female</option>
    </select>

    <input type="text" name="aadhaar" placeholder="Aadhaar Number" 
           value="<?php echo isset($aadhaar) ? htmlspecialchars($aadhaar) : ''; ?>" 
           <?php if($error_field=='aadhaar') echo 'class="error-field"'; ?> required>

    <textarea name="address" placeholder="Address" required><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>

    <input type="password" name="password" placeholder="Password" required>

    <input type="file" name="owner_image" accept="image/*">

    <?php if($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <button type="submit" name="register">Register</button>
</form>
</body>
</html>

