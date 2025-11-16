<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db.php';

// ---------------------------
// 1️⃣ Ensure user is logged in
// ---------------------------
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// ---------------------------
// 2️⃣ Check DB connection
// ---------------------------
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// ---------------------------
// 3️⃣ Get JSON data from request
// ---------------------------
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data received or invalid JSON"]);
    exit();
}

// ---------------------------
// 4️⃣ Map JS keys to DB columns
// ---------------------------
$skills        = $data['skills'] ?? '';
$targetRole    = $data['target_role'] ?? '';
$year          = $data['year'] ?? '';
$experience    = $data['experience'] ?? '';
$learningSpeed = $data['learning_speed'] ?? '';
$goal          = $data['goal'] ?? '';
$career_goal   = $data['career_goal'] ?? '';

// ---------------------------
// 5️⃣ Prepare and execute update query
// ---------------------------
$stmt = $conn->prepare("
    UPDATE users 
    SET onboarding_done = 1, 
        skills = ?, 
        target_role = ?, 
        year = ?, 
        experience = ?, 
        learning_speed = ?, 
        goal = ?, 
        career_goal = ?
    WHERE id = ?
");

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit();
}

// Bind parameters: 7 strings + 1 integer
$stmt->bind_param(
    "sssssssi", 
    $skills, 
    $targetRole, 
    $year, 
    $experience, 
    $learningSpeed, 
    $goal, 
    $career_goal, 
    $user_id
);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Execute failed: " . $stmt->error]);
    exit();
}

$stmt->close();

// ---------------------------
// 6️⃣ Update session
// ---------------------------
$_SESSION['onboarding_done'] = 1;

// ---------------------------
// 7️⃣ Return success response
// ---------------------------
echo json_encode(["success" => true]);
exit();
?>
