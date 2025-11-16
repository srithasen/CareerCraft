<?php
// signup.php
require 'db.php';

// Enable error reporting (for debugging during development)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$messageColor = "red"; // default message color

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $name     = $conn->real_escape_string($_POST['name']);
    $email    = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $conn->real_escape_string($_POST['role']);
    $year     = $conn->real_escape_string($_POST['year']);

    // ✅ Validate Gmail format
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
        $message = "❌ Please use a valid Gmail address (example@gmail.com)";
    } else {
        // ✅ Check if email already exists
        $check_stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // Email already registered — show friendly message
            $message = "⚠️ This email is already registered. Please <a href='login.php'>login here</a>.";
        } else {
            // ✅ Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, year) VALUES (?, ?, ?, ?, ?)");
            if ($stmt === false) {
                $message = "❌ Database Error: " . htmlspecialchars($conn->error);
            } else {
                $stmt->bind_param("sssss", $name, $email, $password, $role, $year);

                if ($stmt->execute()) {
                    // Success — show success message (instead of redirect)
                    $message = "✅ Account created successfully! Redirecting to login...";
                    $messageColor = "green";
                    echo "<meta http-equiv='refresh' content='2;url=login.php'>";
                } else {
                    $message = "❌ Could not create account. Please try again.";
                }
                $stmt->close();
            }
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up | Career Path Planner</title>
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

        .signup-container {
            background-color: rgba(255, 255, 255, 0.95);
            width: 400px;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            text-align: center;
            margin-right: 50px;
            transition: 0.3s ease;
        }

        .signup-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 20px rgba(0,0,0,0.4);
        }

        .signup-container img {
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

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            transition: 0.2s;
        }

        input:focus, select:focus {
            border-color: #4285F4;
            outline: none;
            box-shadow: 0 0 5px #4285F4;
        }

      
.btn {
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    display: inline-block; 
}

.btn-login {
    background-color: #36c044ff;
    margin-left: 15px; /* spacing */
}


        .message {
            margin-top: 15px;
            font-weight: bold;
            color: <?php echo $messageColor; ?>;
        }

        a {
            color: #4285F4;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <img src="logo.jpg" alt="Logo">
    <h2>Create Your Account</h2>

    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST" action="signup.php">
        <label for="name">Name:</label>
        <input type="text" name="name" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required 
               pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" 
               title="Enter a valid Gmail address (example@gmail.com)">

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <label for="role">Role:</label>
        <select name="role" required>
            <option value="Student">Student</option>
            <option value="Graduate">Graduate</option>
            <option value="Currently doing Job">Currently doing Job</option>
        </select>

        <label for="year">Year:</label>
        <select name="year" required>
            <option value="1st">1st</option>
            <option value="2nd">2nd</option>
            <option value="3rd">3rd</option>
            <option value="4th">4th</option>
            <option value="Graduate">Graduate</option>
        </select>

        <input type="submit" name="signup" value="Sign Up" class="btn">
        <a href="login.php" class="btn login">Login</a>
    </form>
</div>

</body>
</html>
