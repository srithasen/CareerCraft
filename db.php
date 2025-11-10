<?php
// db.php

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection settings
$servername = "127.0.0.1";   
$username   = "root";         
$password   = "";             
$dbname     = "career_path_planner";
$port       = 3307;           

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create chat_history table if it doesn’t exist
/*
// ---------------------------
// 2️⃣ Save a message
// ---------------------------
function saveMessage($conn, $user_id, $topic, $role, $message) {
    if ($role === 'ai') $role = 'assistant'; // Map AI role
    $stmt = $conn->prepare(
        "INSERT INTO chat_history (user_id, topic, role, content) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("isss", $user_id, $topic, $role, $message);
    $stmt->execute();
    $stmt->close();
}
    */

// ---------------------------
// 3️⃣ Retrieve chat history for a topic
// ---------------------------
function getChatHistory($conn, $user_id, $topic) {
    $stmt = $conn->prepare(
        "SELECT role, content FROM chat_history WHERE user_id=? AND topic=? ORDER BY id ASC"
    );
    $stmt->bind_param("is", $user_id, $topic);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    $stmt->close();
    return $messages;
}
?>