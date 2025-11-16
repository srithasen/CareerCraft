<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $upload_dir = "uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES['profile_photo']['name']);
    $target_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_path)) {
        // Save only relative path (so HTML img src can find it)
        $sql = "UPDATE users SET profile_photo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $target_path, $user_id);
        $stmt->execute();
    }
}

header("Location: profile.php");
exit();
?>

