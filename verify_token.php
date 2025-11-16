<?php
session_start();
require 'db.php';

$error_msg = '';
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);

    // Fetch latest OTP for this email
    $stmt = mysqli_prepare($conn, "SELECT id, code, expires_at FROM password_resets WHERE email=? ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $record = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$record) {
        $error_msg = "No reset code found. Please request a new code.";
    } elseif (new DateTime($record['expires_at']) < new DateTime()) {
        $error_msg = "Reset code expired. Please request a new one.";
    } elseif ($record['code'] !== $otp) {
        $error_msg = "Incorrect code. Try again.";
    } else {
        // OTP verified -> redirect to reset password
        $_SESSION['verified_email'] = $email;

        // delete used OTP
        $stmt = mysqli_prepare($conn, "DELETE FROM password_resets WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $record['id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: reset_password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify Reset Code</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { background: url('bg.jpg') no-repeat center center fixed; background-size: cover; font-family: Arial, sans-serif; margin: 0; padding: 40px; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.container { background: rgba(0, 0, 0, 0.6); border-radius: 20px; box-shadow: 0 8px 16px rgba(0,0,0,0.4); padding: 30px; width: 400px; backdrop-filter: blur(8px); }
h2 { text-align: center; margin-bottom: 20px; color: #fff; font-size: 22px; }
input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 8px; border: none; font-size: 14px; background: rgba(255,255,255,0.1); color: #fff; }
input::placeholder { color: #bbb; }
.btn { width: 100%; background-color: #b28b94; color: #fff; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: bold; }
.btn:hover { background-color: #936e7a; }
.message { padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; }
.error { background-color: rgba(244, 67, 54, 0.2); border: 1px solid #ef5350; color: #ef9a9a; }
</style>
</head>
<body>
<div class="container">
    <h2>Verify Reset Code</h2>

    <?php if ($error_msg): ?>
        <div class="message error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="POST">
        <p style="text-align:center; font-size:14px; margin-bottom:20px;">
            Enter the 6-digit code sent to <strong><?= htmlspecialchars($email) ?></strong>.
        </p>
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="text" name="otp" placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}" required>
        <button type="submit" class="btn">Verify Code</button>
        <a href="forgot_password.php" style="display:block; text-align:center; margin-top:10px; color:#fff; text-decoration:underline;">Request new code</a>
    </form>
</div>
</body>
</html>
