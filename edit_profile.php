<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found.");
}

$profile_photo = !empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : "assets/default.png";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $year  = mysqli_real_escape_string($conn, $_POST['year']);
    $skills = mysqli_real_escape_string($conn, $_POST['skills']);
    $target_role = mysqli_real_escape_string($conn, $_POST['target_role']);

    $new_photo = "";
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $new_photo = "profile_" . $user_id . "_" . time() . "_" . basename($_FILES["profile_photo"]["name"]);
        $target_file = $target_dir . $new_photo;

        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            // success
        } else {
            $new_photo = "";
        }
    }

    $sql = "UPDATE users SET 
                name='$name', 
                email='$email', 
                year='$year', 
                skills='$skills', 
                target_role='$target_role'";

    if (!empty($new_photo)) {
        $sql .= ", profile_photo='$new_photo'";
    }

    $sql .= " WHERE id=$user_id";

    if (mysqli_query($conn, $sql)) {
        header("Location: profile.php?success=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style>
body {
    background: url('bg.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 40px;
    color: #fff;
}

/* Top-right dashboard button */
.dashboard-link {
    position: absolute;
    top: 20px;
    right: 30px;
}
.dashboard-link a {
    padding: 10px 18px;
    background: rgba(0, 0, 0, 1);
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
}
.dashboard-link a:hover {
    background: rgba(0, 0, 0, 1);
}

/* Profile container */
.profile-container {
    display: flex;
    max-width: 900px;
    margin: 90px auto;
    margin-right: 40px;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 20px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.4);
    overflow: hidden;
    backdrop-filter: blur(8px);
}

/* Left side */
.left-side {
    background: rgba(255, 255, 255, 0.08);
    width: 250px;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
}
.left-side img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 4px solid #fff;
    object-fit: cover;
    margin-bottom: 10px;
}

/* Right side form */
.right-side {
    flex: 1;
    padding: 20px 40px;
}
h2 {
    margin-top: 0;
    font-size: 24px;
    margin-bottom: 20px;
    color: #fff;
}
form label {
    font-weight: bold;
    display: block;
    margin-bottom: 6px;
    color: #ddd;
}
form input[type="text"],
form input[type="email"],
form textarea,
form input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    background: rgba(255,255,255,0.1);
    color: #fff;
}
form textarea {
    resize: vertical;
}
form button {
    margin-top: 10px;
    padding: 12px;
    background-color: #b28b94;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
}
form button:hover {
    background-color: #936e7a;
}
.back-link {
    text-align: center;
    margin-top: 15px;
}
.back-link a {
    color: #fff;
    text-decoration: none;
}
.back-link a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<!-- Top Right Dashboard Button -->
<div class="dashboard-link">
    <a href="dashboard_main.php">Back to Dashboard</a>
</div>

<div class="profile-container">
    <!-- Left side: Profile photo -->
    <div class="left-side">
        <img src="<?= $profile_photo ?>" alt="Profile Photo">
    </div>

    <!-- Right side: Edit form -->
    <div class="right-side">
        <h2>Edit Profile</h2>

        <form method="POST" enctype="multipart/form-data">
            <label for="profile_photo">Profile Photo</label>
            <input type="file" name="profile_photo" accept="image/*">

            <label for="name">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label for="skills">Skills</label>
            <textarea name="skills" rows="3"><?= htmlspecialchars($user['skills']) ?></textarea>

            <label for="year">Year</label>
            <input type="text" name="year" value="<?= htmlspecialchars($user['year']) ?>">

        

            <button type="submit">Save Changes</button>
        </form>

        <div class="back-link">
            <a href="profile.php">Back to Profile</a>
        </div>
    </div>
</div>

</body>
</html>
