<?php
session_start();
// NOTE: Ensure your db.php connects using $conn = mysqli_connect(...)
require 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_msg = '';
$success_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch current password hash
    $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user || !password_verify($current_password, $user['password'])) {
        $error_msg = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_msg = "New password must be at least 6 characters long.";
    } else {
        // Hash new password and update
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
        mysqli_stmt_bind_param($update_stmt, "si", $hashed, $user_id);
        if (mysqli_stmt_execute($update_stmt)) {
            $success_msg = "Password updated successfully!";
        } else {
            $error_msg = "Error updating password: " . mysqli_error($conn);
        }
        mysqli_stmt_close($update_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Change Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style>
body {
    background: url('bg.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 40px;
    color: #fff;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.nav {
    position: absolute;
    top: 20px;
    right: 30px;
}
.nav a {
    padding: 10px 18px;
    background: rgba(0, 0, 0, 0.9);
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
}
.nav a:hover {
    background: rgba(0, 0, 0, 1);
}
.container {
    background: rgba(0, 0, 0, 0.6);
    border-radius: 20px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.4);
    padding: 30px;
    width: 400px;
    backdrop-filter: blur(8px);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #fff;
    font-size: 22px;
}
input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    background: rgba(255,255,255,0.1);
    color: #fff;
}
input::placeholder {
    color: #bbb;
}
.btn {
    width: 100%;
    background-color: #b28b94;
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: bold;
}
.btn:hover {
    background-color: #936e7a;
}
.message {
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}
.success {
    background-color: rgba(200, 230, 201, 0.2);
    border: 1px solid #81c784;
    color: #a5d6a7;
}
.error {
    background-color: rgba(244, 67, 54, 0.2);
    border: 1px solid #ef5350;
    color: #ef9a9a;
}
.forgot-link {
    display: block;
    text-align: center;
    margin-top: 15px;
    color: #4dc0b5; /* Highlighted color */
    text-decoration: none;
    font-size: 14px;
}
.forgot-link:hover {
    text-decoration: underline;
}

</style>
</head>
<body>

<div class="nav">
    <a href="dashboard_main.php">Dashboard</a>
    <a href="profile.php">Profile</a>
</div>

<div class="container">
    <h2>Change Password</h2>

    <?php if ($success_msg): ?>
        <div class="message success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="message error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="current_password" placeholder="Current Password" required>
        <input type="password" name="new_password" placeholder="New Password" required minlength="6">
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required minlength="6">
        <button type="submit" class="btn">Update Password</button>
    </form>
    
    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
</div>

</body>
</html>
