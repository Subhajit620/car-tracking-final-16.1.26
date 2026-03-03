<?php
$conn = new mysqli("localhost", "caruser", "Subha@123", "car");
if ($conn->connect_error) {
    die("Connection failed");
}

if (!isset($_GET['token'])) {
    die("Invalid request");
}

$token = $_GET['token'];
$tokenHash = hash("sha256", $token);

$stmt = $conn->prepare(
    "SELECT id, email_verify_expiry 
     FROM owners 
     WHERE email_verify_token_hash=?"
);
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or already used link");
}

$user = $result->fetch_assoc();

if (strtotime($user['email_verify_expiry']) < time()) {
    die("Verification link expired");
}

$update = $conn->prepare(
    "UPDATE owners 
     SET email_verified=1,
         email_verify_token_hash=NULL,
         email_verify_expiry=NULL
     WHERE id=?"
);
$update->bind_param("i", $user['id']);
$update->execute();

echo "<h2>Email verified successfully ✅</h2>";
echo "<a href='login.php'>Login now</a>";
?>

