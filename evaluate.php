<?php
session_start();

// ===== CONFIG =====
$OPENROUTER_API_KEY = "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"; 
$OPENROUTER_URL     = "https://openrouter.ai/api/v1/chat/completions";
$MODEL              = "openai/gpt-3.5-turbo"; // OpenRouter model

// ===== Get Interview Data =====
$questions = $_SESSION['questions'] ?? [];
$answers   = $_SESSION['answers'] ?? [];
$resume    = $_SESSION['resume_text'] ?? "No resume provided.";
$job_desc  = $_SESSION['job_description'] ?? "No job description provided.";

// ===== Build Transcript =====
$transcript = "";
for($i=0; $i<count($answers); $i++){
    $q = $questions[$i] ?? "N/A";
    $a = $answers[$i]['answer'] ?? "N/A";
    $transcript .= "Q: $q\nA: $a\n";
}

// ===== Prompt for AI Evaluation =====
$prompt = "You are an expert interviewer. Evaluate this candidate's mock interview.
Resume: $resume
Job Description: $job_desc
Transcript:
$transcript

Provide:
1. Overall score out of 10
2. Strengths
3. Weaknesses
4. Actionable tips to improve

Keep the response concise and professional.";

// ===== Ask OpenRouter =====
$feedback = "⚠️ Could not generate feedback.";
try {
    $ch = curl_init($OPENROUTER_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $OPENROUTER_API_KEY",
        "Content-Type: application/json",
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "model" => $MODEL,
        "messages" => [
            ["role" => "system", "content" => "You are a professional career evaluator."],
            ["role" => "user", "content" => $prompt]
        ],
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if(isset($data['choices'][0]['message']['content'])){
        $feedback = trim($data['choices'][0]['message']['content']);
    }
} catch(Exception $e){
    $feedback = "⚠️ Error during evaluation: ".$e->getMessage();
}

// ===== Clear Session if you want =====
// session_destroy(); // optional, to restart new interview
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mock Interview Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 50px;
        }
        .feedback-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 { color: #333; }
        pre {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
        a.button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
        }
        a.button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="feedback-box">
        <h2>Mock Interview Feedback</h2>
        <pre><?php echo htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8'); ?></pre>
        <a href="mock-interview.php" class="button">Start New Interview</a>
        <a href="dashboard_main.php" class="button">Back to Dashboard</a>
    </div>
</body>
</html>
