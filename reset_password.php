
<?php
session_start();
require 'db.php';

$error_msg = '';
$success_msg = '';

// Check if email is verified
if (!isset($_SESSION['verified_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['verified_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_msg = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE email=?");
        mysqli_stmt_bind_param($stmt, "ss", $hashed, $email);

        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Password reset successful! You can now log in.";
            unset($_SESSION['verified_email']); // clear session
        } else {
            $error_msg = "Error updating password. Try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style>
body { background: url('bg.jpg') no-repeat center center fixed; background-size: cover; font-family: Arial, sans-serif; margin:0; padding:40px; color:#fff; min-height:100vh; display:flex; align-items:center; justify-content:center; }
.container { background: rgba(0,0,0,0.6); border-radius:20px; box-shadow:0 8px 16px rgba(0,0,0,0.4); padding:30px; width:400px; backdrop-filter:blur(8px); }
h2 { text-align:center; margin-bottom:20px; color:#fff; font-size:22px; }
input { width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:none; font-size:14px; background: rgba(255,255,255,0.1); color:#fff; }
input::placeholder { color:#bbb; }
.btn { width:100%; background-color:#b28b94; color:#fff; padding:12px; border:none; border-radius:8px; cursor:pointer; font-size:15px; font-weight:bold; }
.btn:hover { background-color:#936e7a; }
.message { padding:10px; border-radius:8px; margin-bottom:15px; text-align:center; }
.success { background-color: rgba(200,230,201,0.2); border:1px solid #81c784; color:#a5d6a7; }
.error { background-color: rgba(244,67,54,0.2); border:1px solid #ef5350; color:#ef9a9a; }
</style>
</head>
<body>
<div class="container">
<h2>Reset Password</h2>

<?php if ($success_msg): ?>
    <div class="message success"><?= htmlspecialchars($success_msg) ?></div>
    <a href="login.php" class="btn">Go to Login</a>
<?php else: ?>
    <?php if ($error_msg): ?>
        <div class="message error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="new_password" placeholder="New Password" required minlength="6">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="6">
        <button type="submit" class="btn">Reset Password</button>
    </form>
<?php endif; ?>
</div>
</body>
</html>
