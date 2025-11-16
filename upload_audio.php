<?php
session_start();

// ===== CONFIG =====
$OPENAI_API_KEY = "your_openai_api_key";
$WHISPER_URL = "https://api.openai.com/v1/audio/transcriptions";

if (!isset($_FILES['audio'])) {
    echo json_encode(["error" => "No audio file uploaded"]);
    exit;
}

$tmpPath = $_FILES['audio']['tmp_name'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $WHISPER_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $OPENAI_API_KEY"
]);

$postFields = [
    "file" => new CURLFile($tmpPath, $_FILES['audio']['type'], $_FILES['audio']['name']),
    "model" => "whisper-1"
];

curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$transcript = $data['text'] ?? "";

// Save last answer for context
$_SESSION['last_answer'] = $transcript;

echo json_encode(["text" => $transcript]);
?>
