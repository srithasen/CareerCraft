<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';
require 'phpmailer_config.php'; // SMTP_HOST, SMTP_USERNAME, SMTP_PASSWORD, etc.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists in users table
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        $error_msg = "No account found with that email address.";
    } else {
        $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT); // 6-digit OTP
        $expires = date("Y-m-d H:i:s", time() + 600); // 10 minutes validity

        // Insert OTP into password_resets table
        $stmt = mysqli_prepare($conn, "INSERT INTO password_resets (email, code, expires_at) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $email, $otp, $expires);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Send OTP via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Career Planner Password Reset Code';
            $mail->Body = "
                <h2>Password Reset Code</h2>
                <p>Use the code below to reset your password. It expires in 10 minutes.</p>
                <p style='font-size:1.5em; font-weight:bold; background:#f0f0f0; padding:10px; border:1px solid #ccc;'>{$otp}</p>
                <p>If you did not request a reset, ignore this email.</p>
            ";
            $mail->AltBody = "Your OTP code is: {$otp}";

            $mail->send();
            $_SESSION['reset_email'] = $email; // store email in session
            header("Location: verify_token.php");
            exit();

        } catch (Exception $e) {
            $error_msg = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Forgot Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style>
/* Same styles as change_password.php */
body { background: url('bg.jpg') no-repeat center center fixed; background-size: cover; font-family: Arial, sans-serif; margin: 0; padding: 40px; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.nav { position: absolute; top: 20px; right: 30px; }
.nav a { padding: 10px 18px; background: rgba(0, 0, 0, 0.9); color: #fff; text-decoration: none; border-radius: 6px; font-size: 14px; }
.nav a:hover { background: rgba(0, 0, 0, 1); }
.container { background: rgba(0, 0, 0, 0.6); border-radius: 20px; box-shadow: 0 8px 16px rgba(0,0,0,0.4); padding: 30px; width: 400px; backdrop-filter: blur(8px); }
h2 { text-align: center; margin-bottom: 20px; color: #fff; font-size: 22px; }
input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 8px; border: none; font-size: 14px; background: rgba(255,255,255,0.1); color: #fff; }
input::placeholder { color: #bbb; }
.btn { width: 100%; background-color: #b28b94; color: #fff; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: bold; }
.btn:hover { background-color: #936e7a; }
.message { padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; }
.success { background-color: rgba(200, 230, 201, 0.2); border: 1px solid #81c784; color: #a5d6a7; }
.error { background-color: rgba(244, 67, 54, 0.2); border: 1px solid #ef5350; color: #ef9a9a; }
</style>
</head>
<body>

<div class="nav">
    <a href="change_password.php">Change Password</a>
</div>

<div class="container">
    <h2>Reset Password Request</h2>

    <?php if ($success_msg): ?>
        <div class="message success"><?= $success_msg ?></div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="message error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email address" required>
        <button type="submit" class="btn">Send Reset Code</button>
    </form>
</div>

</body>
</html>
