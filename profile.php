<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check for photo update message
$photo_message = "";
if (isset($_SESSION['photo_message'])) {
    $photo_message = $_SESSION['photo_message'];
    unset($_SESSION['photo_message']); // Clear the message after displaying
}

// Use a prepared statement to securely fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Check if a user was found
if (!$user) {
    die("User not found.");
}

// Default photo if empty
if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) {
    $profile_photo = htmlspecialchars($user['profile_photo']);
} else {
    // Use a simple placeholder if no photo or file doesn't exist
    $profile_photo = "data:image/svg+xml;base64," . base64_encode('<svg width="150" height="150" viewBox="0 0 150 150" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="150" height="150" fill="#F3F4F6"/><circle cx="75" cy="60" r="30" fill="#9CA3AF"/><path d="M37.5 105L37.5 120L112.5 120L112.5 105C112.5 97.54 96.46 91.5 75 91.5C53.54 91.5 37.5 97.54 37.5 105Z" fill="#9CA3AF"/></svg>');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>My Profile</title>
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
    backdrop-filter: blur(6px);
}
.dashboard-link a:hover {
    background: rgba(0, 0, 0, 1);
}

.profile-container {
    display: flex;
    max-width: 900px;
    margin: 90px auto;
    margin-right: 40px;
    background: rgba(0, 0, 0, 0.6); /* transparent dark */
    border-radius: 20px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.4);
    overflow: hidden;
    backdrop-filter: blur(8px); /* frosted glass effect */
}

/* Left side: Profile photo panel */
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
.change-photo {
    margin-top: 10px;
    text-align: center;
}
.change-photo input[type="file"] {
    margin-bottom: 8px;
    font-size: 13px;
}
.change-photo button {
    background-color: rgba(255, 255, 255, 0.9);
    color: #000000ff;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
}
.change-photo button:hover {
    background-color: #eee;
}

/* Right side: User details */
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
.box {
    background: rgba(255, 255, 255, 0.1); /* transparent box */
    border: none;
    padding: 12px 15px;
    border-radius: 12px;
    margin-bottom: 15px;
    font-size: 16px;
    color: #fff;
}
.row {
    margin-bottom: 15px;
}
.row.two-columns {
    display: flex;
    gap: 20px;
}
.row.two-columns .box {
    flex: 1;
}
.row.password-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.row.password-row .box {
    flex: 1;
}

/* Buttons */
.btn {
    padding: 8px 16px;
    background-color: rgba(0, 0, 0, 1);
    color: #fff;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
    backdrop-filter: blur(4px);
}
.btn:hover {
    background-color: rgba(0, 0, 0, 1);
}
.edit-btn {
    display: block;
    width: 150px;
    margin: 20px auto 0;
    text-align: center;
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

        <div class="change-photo">
            <?php if ($photo_message): ?>
                <div style="color: <?= strpos($photo_message, 'successfully') !== false ? '#4CAF50' : '#f44336' ?>; font-size: 12px; margin-bottom: 8px; text-align: center;">
                    <?= htmlspecialchars($photo_message) ?>
                </div>
            <?php endif; ?>
            <form action="update_photo.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_photo" accept="image/*" required>
                <br>
                <button type="submit">Change Photo</button>
            </form>
        </div>
    </div>

    <!-- Right side: User details -->
    <div class="right-side">
        <h2>My Profile</h2>

        <!-- Name -->
        <div class="row">
            <label>Name:</label>
            <div class="box"><?= htmlspecialchars($user['name']) ?></div>
        </div>

        <!-- Email -->
        <div class="row">
            <label>Email:</label>
            <div class="box"><?= htmlspecialchars($user['email']) ?></div>
        </div>

        <!-- Skills and Year -->
        <div class="row two-columns">
            <div>
                <label>Skills:</label>
                <div class="box"><?= htmlspecialchars($user['skills']) ?></div>
            </div>
            <div>
                <label>Year:</label>
                <div class="box"><?= htmlspecialchars($user['year']) ?></div>
            </div>
        </div>

        <!-- Password and Change Button -->
        <div class="row password-row">
            <label>Password:</label>
            <div class="box">••••••••••</div>
            <a href="change_password.php" class="btn">Change Password</a>
        </div>

        <!-- Edit Profile Button -->
        <a href="edit_profile.php" class="btn edit-btn">Edit Profile</a>
    </div>
</div>

</body>
</html>
