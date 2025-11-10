<?php
session_start();
require_once "db.php";

// ✅ If user is not logged in, redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ If user clicks "Delete Account"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {

    // DELETE user from database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Clear Session
    session_unset();
    session_destroy();

    // Redirect to homepage or login page
    header("Location: login.php?deleted=1");
    exit;
}

// ✅ If user clicks "Just Logout"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout_only'])) {

    session_unset();
    session_destroy();

    header("Location: login.php?logout=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logout & Delete Account</title>
    <meta charset="UTF-8">
    <style>
        body {
            background: url('bg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Poppins, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .box {
            background: rgba(0,0,0,0.65);
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            color: white;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.25);
        }
        h2 { margin-bottom: 10px; }
        p { opacity: .9; }
        .btn {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            color: white;
        }
        .logout { background: #03a9f4; }
        .delete { background: #e53935; }
        .cancel { background: #777; }
    </style>
</head>
<body>

<div class="box">
    <h2>Are you sure?</h2>
    <p>You can logout or permanently delete your account.</p>

    <form method="POST">
        <button type="submit" name="logout_only" class="btn logout">Logout Only</button>
        <button type="submit" name="confirm_delete" class="btn delete">Delete My Account</button>
        <a href="dashboard_main.php" class="btn cancel" style="display:block; text-decoration:none;">Cancel</a>
    </form>
</div>

</body>
</html>
