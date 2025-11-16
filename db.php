<?php
// db.php (SAFE VERSION)

// Enable error reporting (optional for debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection settings FROM .env
$servername = $_ENV['DB_HOST'];
$username   = $_ENV['DB_USER'];
$password   = $_ENV['DB_PASS'];
$dbname     = $_ENV['DB_NAME'];
$port       = $_ENV['DB_PORT'];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---------------------------
// Retrieve chat history for a topic
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
