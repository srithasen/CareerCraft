<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON response
require 'db.php'; // Your database connection

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error', 'message'=>'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Read JSON POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['status'=>'error', 'message'=>'No data received']);
    exit;
}

// Prepare statement to save answers
$stmt = $conn->prepare("UPDATE users SET 
    year = ?, 
    skills = ?, 
    experience = ?, 
    learningSpeed = ?, 
    goal = ?, 
    degree = ?, 
    internship = ?, 
    workLocation = ?, 
    switchReason = ?, 
    targetRole = ?, 
    desiredSkills = ?, 
    jobTitle = ?, 
    onboarding_done = 1 
    WHERE id = ?");

// Assign variables safely from data or empty strings
$year = $data['year'] ?? '';
$skills = $data['skills'] ?? '';
$experience = $data['experience'] ?? '';
$learningSpeed = $data['learningSpeed'] ?? '';
$goal = $data['goal'] ?? '';
$degree = $data['degree'] ?? '';
$internship = $data['internship'] ?? '';
$workLocation = $data['workLocation'] ?? '';
$switchReason = $data['switchReason'] ?? '';
$targetRole = $data['targetRole'] ?? '';
$desiredSkills = $data['desiredSkills'] ?? '';
$jobTitle = $data['jobTitle'] ?? '';

$stmt->bind_param(
    "sssssssssssssi",
    $year,
    $skills,
    $experience,
    $learningSpeed,
    $goal,
    $degree,
    $internship,
    $workLocation,
    $switchReason,
    $targetRole,
    $desiredSkills,
    $jobTitle,
    $user_id
);

if ($stmt->execute()) {
    $_SESSION['onboarding_done'] = 1; // Mark in session
    echo json_encode(['status'=>'saved', 'redirect'=>'chat.php']);
    exit;
} else {
    echo json_encode(['status'=>'error', 'message'=>'Could not save answers']);
    exit;
}

$stmt->close();
$conn->close();
?>
