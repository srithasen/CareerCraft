<?php
// dsa_practice_problem.php
session_start();
include 'db.php';

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;

if ($problem_id === 0) {
    echo "No problem selected.";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM dsa_problems WHERE id = ?");
$stmt->bind_param("i", $problem_id);
$stmt->execute();
$problem = $stmt->get_result()->fetch_assoc();

if (!$problem) {
    echo "Problem not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practice: <?php echo htmlspecialchars($problem['problem_title']); ?></title>
    <style>
        body { font-family: sans-serif; display: flex; height: 100vh; margin: 0; background-color: #f0f2f5; }
        .sidebar { width: 40%; padding: 20px; background-color: #fff; overflow-y: auto; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .main-content { flex-grow: 1; display: flex; flex-direction: column; }
        .code-editor-area { flex-grow: 1; padding: 20px; background-color: #282c34; color: #abb2bf; font-family: 'Consolas', 'Monaco', monospace; display: flex; flex-direction: column; }
        textarea { flex-grow: 1; background-color: #1e2127; border: 1px solid #3e4451; color: #abb2bf; padding: 10px; font-size: 1em; resize: vertical; min-height: 200px; }
        .output-area { padding: 15px; background-color: #21252b; border-top: 1px solid #3e4451; min-height: 100px; max-height: 200px; overflow-y: auto; }
        .controls { display: flex; justify-content: space-between; padding: 10px 20px; background-color: #343a40; }
        .controls select, .controls button { padding: 8px 15px; border-radius: 5px; border: none; cursor: pointer; }
        .controls select { background-color: #495057; color: white; }
        .controls button { background-color: #007bff; color: white; margin-left: 10px; }
        .controls button.submit { background-color: #28a745; }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 20px; }
        pre { background-color: #eee; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1><?php echo htmlspecialchars($problem['problem_title']); ?></h1>
        <p><b>Status:</b> <?php echo htmlspecialchars($problem['status']); ?></p>
        <?php if ($problem['due_date']): ?>
            <p><b>Due Date:</b> <?php echo htmlspecialchars($problem['due_date']); ?></p>
        <?php endif; ?>
        <?php if ($problem['notes']): ?>
            <p><b>Notes:</b> <?php echo nl2br(htmlspecialchars($problem['notes'])); ?></p>
        <?php endif; ?>

        <h2>Problem Description</h2>
        <p><?php echo nl2br(htmlspecialchars($problem['problem_description'])); ?></p>

        <?php if ($problem['input_output_format']): ?>
            <h2>Input/Output Format</h2>
            <p><?php echo nl2br(htmlspecialchars($problem['input_output_format'])); ?></p>
        <?php endif; ?>

        <?php if ($problem['hints']): ?>
            <h2>Hints</h2>
            <p><?php echo nl2br(htmlspecialchars($problem['hints'])); ?></p>
        <?php endif; ?>

        <?php if ($problem['leetcode_link']): ?>
            <p>External Link: <a href="<?php echo htmlspecialchars($problem['leetcode_link']); ?>" target="_blank">LeetCode</a></p>
        <?php endif; ?>
        <?php if ($problem['solution_video_link']): ?>
            <p>Solution Video: <a href="<?php echo htmlspecialchars($problem['solution_video_link']); ?>" target="_blank">Watch on YouTube</a></p>
        <?php endif; ?>
        <br>
        <a href="dsa_problems_list.php?category_id=<?php echo $problem['category_id']; ?>">Back to Problems List</a>
    </div>

    <div class="main-content">
        <div class="code-editor-area">
            <label for="language-select">Language:</label>
            <select id="language-select">
                <option value="php">PHP</option>
                <option value="python">Python</option>
                <option value="java">Java</option>
                <option value="cpp">C++</option>
                </select>
            <br>
            <label for="code-input">Write your code here:</label>
            <textarea id="code-input" placeholder="// Write your <?php echo strtolower($problem['problem_title']); ?> solution here..."></textarea>
        </div>
        <div class="controls">
            <button onclick="runCode()">Run Code</button>
            <button class="submit" onclick="submitCode()">Submit</button>
        </div>
        <div class="output-area">
            <h2>Output / Results</h2>
            <pre id="code-output">Your code output will appear here...</pre>
        </div>
    </div>

    <script>
        function runCode() {
            const code = document.getElementById('code-input').value;
            const language = document.getElementById('language-select').value;
            const outputElement = document.getElementById('code-output');
            
            outputElement.textContent = "Running code... (This requires an external compiler API)";

            // --- IMPORTANT: This is where you would integrate with an actual online judge API ---
            // Example of a hypothetical AJAX call:
            /*
            fetch('api/execute_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    problem_id: <?php echo $problem_id; ?>,
                    code: code,
                    language: language
                }),
            })
            .then(response => response.json())
            .then(data => {
                outputElement.textContent = JSON.stringify(data, null, 2); // Display raw response for now
                // In a real app, you'd parse data.result, data.error, etc.
            })
            .catch((error) => {
                console.error('Error:', error);
                outputElement.textContent = 'Error: Could not connect to compiler service.';
            });
            */
            // --- End of hypothetical API integration ---

            // For now, just show a message indicating functionality is missing
            setTimeout(() => {
                outputElement.textContent = `Code (Language: ${language}):\n${code}\n\nActual compilation/execution requires an external online judge API.`;
            }, 1000);
        }

        function submitCode() {
            const code = document.getElementById('code-input').value;
            const language = document.getElementById('language-select').value;
            const outputElement = document.getElementById('code-output');

            outputElement.textContent = "Submitting code... (This requires an external compiler API)";

            // Similar to runCode(), this would call your backend which then talks to the OJ API
            // and runs against all test cases, not just basic input.
            setTimeout(() => {
                outputElement.textContent = `Code submitted for evaluation (Language: ${language}):\n${code}\n\nFull submission and scoring requires an external online judge API.`;
            }, 1000);
        }
    </script>
</body>
</html>