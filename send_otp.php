<?php
session_start();
require 'db.php';
require 'vendor/autoload.php'; // PHPMailer
require 'mail_config.php';     // your SMTP constants

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['email'])) {
    header('Location: forgot_password.php');
    exit;
}

$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
if (!$email) {
    $_SESSION['error'] = "Enter a valid email.";
    header('Location: forgot_password.php');
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $_SESSION['success'] = "If this email is registered, a reset code has been sent.";
    header('Location: forgot_password.php');
    exit;
}
$stmt->close();

// Delete old codes for this email
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$del->bind_param("s", $email);
$del->execute();
$del->close();

// Generate 6-digit numeric code
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires_at = date("Y-m-d H:i:s", strtotime('+15 minutes'));

// Insert new code
$ins = $conn->prepare("INSERT INTO password_resets (email, code, expires_at) VALUES (?, ?, ?)");
$ins->bind_param("sss", $email, $code, $expires_at);
$ins->execute();
$ins->close();

// Send OTP email using PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your Password Reset Code';
    $mail->Body    = "
        <p>Hello,</p>
        <p>We received a request to reset your password. Your 6-digit verification code is:</p>
        <h2 style='letter-spacing:4px;'>$code</h2>
        <p>This code will expire in 15 minutes. If you didn't request this, please ignore this email.</p>
    ";

    $mail->send();
    $_SESSION['success'] = "If this email is registered, a reset code has been sent.";
} catch (Exception $e) {
    error_log("Mail error: " . $mail->ErrorInfo);
    $_SESSION['error'] = "Could not send reset email. Try again later.";
}

header('Location: verify_token.php?email=' . urlencode($email));
exit;
