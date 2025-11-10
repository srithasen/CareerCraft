<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_photo'])) {
    $file = $_FILES['profile_photo'];
    
    if ($file['error'] === 0) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $newName = 'uploads/' . uniqid() . '.' . $ext;
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            if (move_uploaded_file($file['tmp_name'], $newName)) {
                // Update database
                $sql = "UPDATE users SET profile_photo = '$newName' WHERE id = $user_id";
                if (mysqli_query($conn, $sql)) {
                    header("Location: profile.php");
                    exit();
                }
            }
        }
    }
}

echo "Error uploading photo.";
?>
