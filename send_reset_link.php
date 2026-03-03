<?php
session_start();
require 'vendor/autoload.php';
require 'db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(isset($_POST['reset'])){

    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id, full_name FROM owners WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows === 1){
        $stmt->bind_result($id,$full_name);
        $stmt->fetch();

        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256',$token);
        $expiry = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        $update = $conn->prepare("UPDATE owners SET reset_token_hash=?, reset_expiry=? WHERE id=?");
        $update->bind_param("ssi",$token_hash,$expiry,$id);
        $update->execute();

        // 🔥 CHANGE THIS TO YOUR PUBLIC IP OR DOMAIN
        $reset_link = "http://100.24.36.67/car_project/reset_password.php?token=$token";

        $mail = new PHPMailer(true);

        try{
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'subhajit620.saha@gmail.com'; // CHANGE
            $mail->Password = 'njrmzdtavslrxpar';     // CHANGE (16-char Gmail app password)
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('YOUR_GMAIL@gmail.com','Car Project');
            $mail->addAddress($email,$full_name);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Link';
            $mail->Body = "
                Hi $full_name,<br><br>
                Click below to reset password:<br>
                <a href='$reset_link'>$reset_link</a><br><br>
                Link expires in 30 minutes.
            ";

            $mail->send();
            $_SESSION['msg'] = "Reset link sent!";
        }catch(Exception $e){
            $_SESSION['msg'] = "Mailer Error: " . $mail->ErrorInfo;
        }

    } else {
        $_SESSION['msg'] = "Email not found.";
    }

    header("Location: forgot_password.html");
    exit();
}
?>

