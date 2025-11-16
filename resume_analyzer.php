<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/**
 * Requirements (composer):
 *   vlucas/phpdotenv
 *   smalot/pdfparser
 */
require __DIR__.'/vendor/autoload.php';

use Smalot\PdfParser\Parser as PdfParser;
use Dotenv\Dotenv;

/* ------------------ ENV ------------------ */
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
$apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
$model  = $_ENV['GEMINI_MODEL']   ?? 'gemini-2.5-flash';
if (!$apiKey) { http_response_code(500); die("❌ Missing GEMINI_API_KEY in .env"); }

/* ------------------ AUTH ----------------- */
$accountName  = $_SESSION['name']       ?? '';
$accountEmail = $_SESSION['user_email'] ?? '';
if (!$accountName || !$accountEmail) { die("Login first."); }

/* -------- Classic only (no UI switch) ---- */
$aiResult = null;
$message  = '';

/* ------------------ HELPERS -------------- */
function is_url($s){ return filter_var(trim($s), FILTER_VALIDATE_URL); }

function fetch_url_text($url){
  $ctx = stream_context_create(['http'=>['timeout'=>10,'header'=>"User-Agent: Mozilla/5.0\r\n"]]);
  $raw = @file_get_contents($url, false, $ctx);
  if ($raw === false) return null;
  $raw = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is','',$raw);
  $raw = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is','',$raw);
  $text = strip_tags($raw);
  $text = preg_replace('/\s+/', ' ', $text);
  return trim($text);
}

function extract_json_from_text($txt){
  if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $txt, $m)) return $m[0];
  $start = strpos($txt, '{'); if ($start === false) return '';
  $depth = 0; for ($i = $start; $i < strlen($txt); $i++){
    $ch = $txt[$i]; if ($ch==='{' ) $depth++; if ($ch==='}') $depth--;
    if ($depth===0) return substr($txt, $start, $i-$start+1);
  }
  return '';
}

function safe_int($v,$d=0){ return is_numeric($v)?intval($v):$d; }

function highlightKeywords($text){
  $keywords = ['Product Manager','leadership','communication','problem-solving','market research',
               'user story','product development','frameworks','skills','experience','objective','projects'];
  foreach($keywords as $w){
    $text = preg_replace("/(" . preg_quote($w,'/') . ")/i","<span style='color:#38bdf8;font-weight:bold;'>$1</span>",$text);
  }
  return $text;
}

/* ------------------ SUBMIT ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $parser = new PdfParser();
  $resumeText = '';
  $jobDescription = trim($_POST['job_description'] ?? '');

  // PDF -> text
  if (!empty($_FILES['resume']['tmp_name'])) {
    try {
      $pdf = $parser->parseFile($_FILES['resume']['tmp_name']);
      if (count($pdf->getPages()) > 2) throw new Exception("❌ Resume exceeds 2 pages.");

      $resumeText = $pdf->getText();

      /* 🔥 STRICT NAME/EMAIL VALIDATION — ONLY CHANGE ADDED */
      $resumeNormalized = strtolower(preg_replace('/\s+/', '', $resumeText));
      $nameNormalized   = strtolower(preg_replace('/\s+/', '', $accountName));
      $emailNormalized  = strtolower(trim($accountEmail));

      $nameMatch  = strpos($resumeNormalized, $nameNormalized) !== false;
      $emailMatch = strpos($resumeNormalized, $emailNormalized) !== false;

      if (!$nameMatch && !$emailMatch) {
        throw new Exception("❌ The uploaded resume could not be verified. Your name or email was not detected in the document.");
      }

    } catch (Exception $e) {
      $message = $e->getMessage();
      $resumeText = ""; // stop further analysis
    }
  } else {
    $message = "❌ Please upload a PDF resume.";
  }

  // JD text or URL
  if ($resumeText && $jobDescription) {
    if (is_url($jobDescription)) {
      $fetched = fetch_url_text($jobDescription);
      if ($fetched) {
        $jobDescription = "Source URL: {$jobDescription}\n\nExtracted Text:\n{$fetched}";
      } else {
        $message = "⚠️ Could not fetch job description from URL. Using the URL only for keyword match.";
      }
    }

    // Prompt
    $prompt = "You are an AI-powered ATS resume evaluator.
Compare the resume and job description and return a concise JSON with exactly:
{
  \"performance_overview\": {\"Technical Skills\":#,\"Soft Skills\":#,\"Job Fit\":#,\"Formatting\":#,\"ATS Readiness\":#},
  \"missing_skills\":[\"...\"],
  \"skill_gap_recommendations\":[\"...\"],
  \"resume_sections_to_improve\":[\"...\"],
  \"shortlisting_tips\":[\"...\"],
  \"highlight_points\":[\"...\"],
  \"ats_score\":#,
  \"keyword_match\":#,
  \"readability\":#
}
Rules:
- Each list <= 5 items; each item <= 15 words.
- Use simple, action-focused phrases; no paragraphs/emojis.
- Add, for each recommendation, exactly what to learn (e.g., topics/tools).
- \"performance_overview\" must be accurate and effective (0-10 scale).
- If job description is ONLY a link, compute keyword_match/readability from link text if available; otherwise use conservative numbers.
- Output JSON only; no backticks, no extra text.

RESUME:
{$resumeText}

JOB DESCRIPTION:
{$jobDescription}";

    // Gemini call
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
    $payload = json_encode([
      "contents" => [[ "role" => "user", "parts" => [["text" => $prompt]] ]]
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
      CURLOPT_POSTFIELDS => $payload,
      CURLOPT_TIMEOUT => 30
    ]);
    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if (!$resp) {
      $message = "❌ Gemini API did not respond. {$curlErr}";
    } else {
      $data = json_decode($resp, true);
      $txt  = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
      if (!$txt) {
        $apiMsg = $data['promptFeedback']['blockReason'] ?? ($data['error']['message'] ?? 'Unknown API response.');
        $message = "❌ Empty AI response. {$apiMsg}";
      } else {
        $json = extract_json_from_text($txt);
        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
          $message = "❌ Invalid AI JSON. Showing raw text.";
          $aiResult = ["raw" => $txt];
        } else {
          $decoded['ats_score']     = safe_int($decoded['ats_score']     ?? 0);
          $decoded['keyword_match'] = safe_int($decoded['keyword_match'] ?? 0);
          $decoded['readability']   = safe_int($decoded['readability']   ?? 0);
          $aiResult = $decoded;
        }
      }
    }
  }
}

/* ------------------ STYLES ---------------- */
$baseBody = "
  background: url('bg.jpg') no-repeat center center fixed;
  background-size: cover;
  font-family: 'Inter', sans-serif;
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 40px;
";
$classicCard = "background:rgba(0,0,0,0.85); padding:30px; border-radius:20px; box-shadow:0 8px 16px rgba(0,0,0,.5);";

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Intelligent Resume Analyzer (Gemini)</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { <?= $baseBody ?> }

.dashboard-link { position: fixed; top: 20px; right: 30px; }
.dashboard-link a { background:#374151; color:#fff; padding:10px 18px; border-radius:6px; font-weight:600; }

.app-container { display:flex; gap:30px; width:100%; max-width:1200px; }
.left-box  { flex:3; <?= $classicCard ?> }
.right-box { flex:4; <?= $classicCard ?> max-height:80vh; overflow-y:auto; }

.metric-label { color:#facc15; font-weight:700; }
.score-value  { font-size:56px; font-weight:900; color:#00ff99; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>

<!-- Back to Dashboard (RIGHT) -->
<div class="dashboard-link">
  <a href="dashboard_main.php">← Back to Dashboard</a>
</div>

<div class="app-container">
  <!-- LEFT -->
  <div class="left-box">
    <h3 class="text-2xl font-bold mb-4 text-red-400">Upload & ATS Metrics</h3>

    <form method="POST" enctype="multipart/form-data" class="mb-6">
      <label class="metric-label">Resume (PDF ≤2 pages)</label>
      <input type="file" name="resume" accept=".pdf" class="w-full text-black bg-gray-100 rounded p-1 mb-3" required>

      <label class="metric-label">Job Description (text or URL)</label>
      <textarea name="job_description" rows="6" class="w-full text-black rounded p-2 mb-3" required></textarea>

      <input type="submit" value="Analyze with Gemini"
             class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-bold">
    </form>

    <?php if($message): ?>
      <p class="text-red-400 mb-3"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if($aiResult && isset($aiResult['ats_score'])): ?>
      <div class="text-center border-t border-gray-600 pt-3 mt-4">
        <p class="text-lg text-gray-300 font-semibold">ATS Simulation Score</p>
        <p class="score-value"><?= intval($aiResult['ats_score']) ?></p>
        <p class="text-gray-400 text-sm mb-2">out of 100</p>
        <div class="flex justify-center gap-6 text-sm text-gray-400">
          <span><strong>Keyword Match:</strong> <?= intval($aiResult['keyword_match'] ?? 0) ?>%</span>
          <span><strong>Readability:</strong> <?= intval($aiResult['readability'] ?? 0) ?>%</span>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT -->
  <div class="right-box">
    <h3 class="text-2xl font-bold mb-4 text-red-400">AI Match Analysis & Dashboard</h3>

    <?php if($aiResult): ?>

      <?php if(isset($aiResult['performance_overview']) && is_array($aiResult['performance_overview'])): ?>
        <h4 class="metric-label mb-2">Performance Overview</h4>
        <canvas id="barChart" height="200"></canvas>
        <script>
          const ctx=document.getElementById('barChart');
          const vals=<?= json_encode(array_values($aiResult['performance_overview'])) ?>;
          const labels=<?= json_encode(array_keys($aiResult['performance_overview'])) ?>;
          const colors=vals.map(v=>v>=8?'#22c55e':v>=6?'#fbbf24':'#ef4444');
          if (labels.length) {
            new Chart(ctx,{type:'bar',
              data:{labels:labels,datasets:[{data:vals,backgroundColor:colors}]},
              options:{plugins:{legend:{display:false}},
                scales:{y:{beginAtZero:true,max:10,ticks:{color:'#ccc'}}}}});
          }
        </script>
      <?php endif; ?>

      <?php
        $sections = [
          'missing_skills' => 'Missing Skills',
          'skill_gap_recommendations' => 'Skill Gap Recommendations',
          'resume_sections_to_improve' => 'Resume Sections to Improve',
          'shortlisting_tips' => 'Shortlisting Tips',
          'highlight_points' => 'Highlighted Points to Consider'
        ];
      ?>

      <?php foreach ($sections as $key => $label): ?>
        <?php if (!empty($aiResult[$key]) && is_array($aiResult[$key])): ?>
          <h4 class="metric-label mt-5 mb-1"><?= $label ?>:</h4>
          <ul class="list-disc ml-6 text-sm space-y-2">
            <?php foreach ($aiResult[$key] as $li): ?>
              <li>
                <?php
                  $line = htmlspecialchars($li);
                  if ($key === 'skill_gap_recommendations') {
                    $encoded = urlencode($li);
                    echo highlightKeywords($line) .
                      " – <a href='https://www.coursera.org/search?query=$encoded' target='_blank' style='color:#00ff99;'>[Coursera]</a>
                          <a href='https://www.udemy.com/courses/search/?q=$encoded' target='_blank' style='color:#ffb6c1;'>[Udemy]</a>
                          <a href='https://www.linkedin.com/learning/search?keywords=$encoded' target='_blank' style='color:#a78bfa;'>[LinkedIn]</a>";
                  } else {
                    echo highlightKeywords($line);
                  }
                ?>
              </li>
            <?php endforeach; ?>
          </ul>

          <?php if($key === 'skill_gap_recommendations'): ?>
            <button onclick="window.open('https://www.google.com/search?q=best+product+management+courses','_blank')"
              class="mt-3 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
              📚 View All Learning Resources
            </button>
          <?php endif; ?>

        <?php endif; ?>
      <?php endforeach; ?>

      <?php if (isset($aiResult['raw'])): ?>
        <h4 class="metric-label mt-5">Raw AI Output</h4>
        <pre class="text-xs overflow-x-auto whitespace-pre-wrap"><?= htmlspecialchars($aiResult['raw']) ?></pre>
      <?php endif; ?>

    <?php else: ?>
      <p class="text-gray-400 italic">Upload resume and job description to start analysis.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
