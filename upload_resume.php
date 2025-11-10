<?php
session_start();
require 'vendor/autoload.php'; // Smalot\PdfParser, PhpOffice, etc.

// Database connection
$conn = new mysqli("localhost", "root", "", "career_path_planner", 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_name'])) {
    die("You must be logged in to upload a resume.");
}

$message = "";
$username = $_SESSION['user_name'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadedFile = $_FILES['resume']['tmp_name'];
    $fileName = $_FILES['resume']['name'];
    $fileType = $_FILES['resume']['type'];

    $extractedName = "";

    // --- Try parsing PDF ---
    if ($fileType == "application/pdf" && class_exists('Smalot\PdfParser\Parser')) {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($uploadedFile);
            $text = $pdf->getText();

            // Use first non-empty line as name
            $lines = array_filter(array_map('trim', explode("\n", $text)));
            if (!empty($lines)) {
                $extractedName = reset($lines);
            }
        } catch (Exception $e) {
            $extractedName = "";
        }
    }

    // --- Fallback: use file name if no text extracted ---
    if (empty($extractedName)) {
        $extractedName = pathinfo($fileName, PATHINFO_FILENAME);
    }

    // --- Clean extracted name & account name ---
    $cleanName = preg_replace("/[^a-zA-Z ]/", "", $extractedName); // remove numbers/symbols
    $cleanAccountName = preg_replace("/[^a-zA-Z ]/", "", $username);

    // --- Compare ---
    if (strcasecmp(trim($cleanName), trim($cleanAccountName)) !== 0) {
        $message = "❌ Name mismatch. Resume name detected as: '<b>$extractedName</b>'. "
                 . "It must match your account name: '<b>$username</b>'.";
    } else {
        // Save uploaded resume
        $uploadDir = __DIR__ . "/uploads/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $destPath = $uploadDir . basename($fileName);
        if (move_uploaded_file($uploadedFile, $destPath)) {
            $message = "✅ Resume uploaded successfully.";
        } else {
            $message = "❌ Error saving the file.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Resume</title>
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 0; padding: 0;
    background: url('bg.jpg') no-repeat center center fixed;
    background-size: cover;
    
}
    .error { color: red; }
    .success { color: green;
  }



    </style>
</head>
<body>
    <h2>Upload Resume</h2>
    <?php if (!empty($message)): ?>
        <p class="<?= strpos($message, '❌') !== false ? 'error' : 'success' ?>">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="resume">Select Resume (PDF):</label><br><br>
        <input type="file" name="resume" accept=".pdf" required><br><br>
        <input type="submit" value="Upload">
    </form>

    <p><small>Note: If PDF parsing fails, filename will be used instead.</small></p>
</body>
</html>
