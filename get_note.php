<?php
require_once "db.php";
session_start();
$user_id = $_SESSION['user_id'] ?? 1;

$id = (int)$_GET['id'];
$result = $conn->query("SELECT * FROM notes WHERE id=$id AND user_id=$user_id");
if ($result && $row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Note not found."]);
}
?>
