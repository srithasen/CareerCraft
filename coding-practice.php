<?php
session_start();
require_once "db/db.php"; // connect DB

// Load a problem (first one for now)
$problem = $conn->query("SELECT * FROM problems WHERE id=1")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Coding Practice - Career Path Planner</title>
  <link rel="stylesheet" href="css/coding.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.34.0/min/vs/loader.min.js"></script>
</head>
<body>
<div class="container">

  <!-- Problem Box -->
  <div class="problem-box">
    <h2><?php echo $problem['title']; ?> <span class="tag"><?php echo $problem['difficulty']; ?></span></h2>
    <p><?php echo nl2br($problem['description']); ?></p>
    <b>Input Format:</b><pre><?php echo $problem['input_format']; ?></pre>
    <b>Output Format:</b><pre><?php echo $problem['output_format']; ?></pre>
    <b>Example Input:</b><pre><?php echo $problem['example_input']; ?></pre>
    <b>Example Output:</b><pre><?php echo $problem['example_output']; ?></pre>
    <b>Constraints:</b><pre><?php echo $problem['constraints']; ?></pre>
  </div>

  <!-- Code Editor -->
  <div class="editor-box">
    <div id="editor"></div>
    <div class="controls">
      <select id="language">
        <option value="java">Java</option>
        <option value="cpp">C++</option>
        <option value="python">Python</option>
      </select>
      <button onclick="runCode()">Run Code</button>
      <button onclick="submitCode()">Submit</button>
    </div>
    <div class="console">
      <h3>Output</h3>
      <pre id="output">Write code and click Run...</pre>
    </div>
  </div>

</div>

<script>
let editor;

require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.34.0/min/vs' }});
require(["vs/editor/editor.main"], function () {
  editor = monaco.editor.create(document.getElementById("editor"), {
    value: "public class Solution {\n  public static void main(String[] args) {\n    System.out.println(\"Hello World\");\n  }\n}",
    language: "java",
    theme: "vs-dark",
    automaticLayout: true
  });
});

// Run Code with Example Input
async function runCode(){
  let code = editor.getValue();
  let lang = document.getElementById("language").value;

  let response = await fetch("api/judge0.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({
      language: lang,
      code: code,
      input: `<?php echo trim($problem['example_input']); ?>`
    })
  });

  let result = await response.json();
  document.getElementById("output").textContent =
    result.error ? "❌ " + result.error :
    "✅ " + result.output;
}

// Submit against Hidden Testcases
async function submitCode(){
  let code = editor.getValue();
  let lang = document.getElementById("language").value;

  let response = await fetch("api/judge0.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({
      language: lang,
      code: code,
      input: "hidden",
      problem_id: <?php echo $problem['id']; ?>
    })
  });

  let result = await response.json();
  document.getElementById("output").textContent = result.feedback;
}
</script>
</body>
</html>

