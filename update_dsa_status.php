<?php
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
