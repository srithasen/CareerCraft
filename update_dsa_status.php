<?php
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