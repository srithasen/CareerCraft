<?php
<<<<<<< HEAD
// update_dsa_status.php
session_start();
include 'config.php';

header('Content-Type: application/json');

// Check for required POST data
if (!isset($_POST['problem_id']) || !isset($_POST['field']) || !isset($_POST['value'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters.']);
    exit;
}

$problemId = intval($_POST['problem_id']);
$field = $_POST['field'];
$value = $_POST['value'];

// Whitelist fields to prevent SQL injection attempts on column names
$allowed_fields = ['status', 'due_date', 'notes'];

if (!in_array($field, $allowed_fields)) {
    echo json_encode(['success' => false, 'error' => 'Invalid field name provided.']);
    exit;
}

// Prepare the SQL statement
// We use '?' placeholders for the values to protect against injection
$sql = "UPDATE dsa_problems SET `{$field}` = ? WHERE id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind parameters: 's' for string (value), 'i' for integer (id)
    $stmt->bind_param("si", $value, $problemId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error]);
}

$conn->close();
?>
=======
session_start();
include 'db.php';

header("Content-Type: application/json");

if (!isset($_POST['problem_id'], $_POST['field'], $_POST['value'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$problem_id = intval($_POST['problem_id']);
$field = $_POST['field'];
$value = $_POST['value'];

$allowed = ['status', 'due_date'];

if (!in_array($field, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid field']);
    exit;
}

$sql = "UPDATE dsa_problems SET `$field` = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $value, $problem_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
>>>>>>> aadfe91714b8e381e3c613ec3ab3c310d595d975
