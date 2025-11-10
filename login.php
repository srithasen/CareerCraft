<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require('db.php');  // your DB connection file

$message = "";

// Clear old sessions (optional, for testing)
session_unset();
session_destroy();
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    // ✅ Gmail-only login validation
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
        $message = "❌ Please login with a valid Gmail address (example@gmail.com)";
    } else {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("❌ Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();

                if (password_verify($password, $row['password'])) {
                    // ✅ Store user info in session
                    $_SESSION['user_id']         = $row['id'];
                    $_SESSION['name']            = $row['name'];       // fixed
                    $_SESSION['user_email']      = $row['email'];      // fixed
                    $_SESSION['role']            = $row['role'];
                    $_SESSION['year']            = $row['year'];
                    $_SESSION['onboarding_done'] = $row['onboarding_done'];

                    // ✅ Redirect based on onboarding status
                    if ($_SESSION['onboarding_done'] == 0) {
                        header("Location: onboarding.php");  // first-time onboarding page
                        exit;
                    } else {
                        header("Location: dashboard_main.php");  // main dashboard
                        exit;
                    }

                } else {
                    $message = "❌ Incorrect password.";
                }
            } else {
                $message = "❌ User not found.";
            }
        } else {
            $message = "❌ Query error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('bg.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            width: 400px;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            text-align: center;
            margin-right: 50px;
        }
        .login-container img {
            max-width: 100px;
            margin-bottom: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: bold;
            text-align: left;
            margin: 10px 0 5px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .btn {
            width: 100%;
            background-color: #4285F4;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #3367d6;
        }
        .message {
            margin-top: 15px;
            font-weight: bold;
            color: red;
        }
    </style>
</head>
<body>

<div class="login-container">
    <img src="logo.jpg" alt="Logo">
    <h2>Login</h2>

    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST" action="login.php">
        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <input type="submit" name="login" value="Login" class="btn">
    </form>
    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
</div>

</body>
</html>
