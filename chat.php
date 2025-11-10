<?php
session_start();
require 'db.php'; // Ensure this file correctly sets up the $conn object

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ⚠️ SECURITY: Use prepared statements for $user_id in all operations.
$user_id = $_SESSION['user_id'];

// --- ⚙️ Chat Management Handlers ---

// 🟩 New chat
if (isset($_POST['new_chat'])) {
    $title = "New Chat " . date("Y-m-d H:i:s");
    $stmt = $conn->prepare("INSERT INTO chats (user_id, title) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $title);
    $stmt->execute();
    header("Location: chat.php?chat_id=" . $stmt->insert_id);
    exit;
}

// 🟥 Delete chat
if (isset($_GET['delete'])) {
    $chat_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM chats WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $chat_id, $user_id);
    $stmt->execute();
    header("Location: chat.php");
    exit;
}

// ✏️ Rename chat
if (isset($_POST['rename_chat_id']) && isset($_POST['new_title'])) {
    $cid = intval($_POST['rename_chat_id']);
    $new = trim($_POST['new_title']);
    if ($new !== '') {
        $stmt = $conn->prepare("UPDATE chats SET title=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sii", $new, $cid, $user_id);
        $stmt->execute();
    }
    header("Location: chat.php?chat_id=$cid");
    exit;
}

// --- 📊 Fetch Data ---

// 🟦 Fetch chats
$stmt = $conn->prepare("SELECT id, title FROM chats WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$chats = $stmt->get_result();

// 🟨 Current chat
$current_chat_id = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : 0;

if ($current_chat_id == 0 && $chats->num_rows > 0) {
    $first = $chats->fetch_assoc();
    $current_chat_id = $first['id'];
    $chats->data_seek(0);
}

// 🟧 Messages
$messages = [];
if ($current_chat_id) {
    $stmt = $conn->prepare("SELECT sender, message FROM messages WHERE chat_id=? ORDER BY created_at ASC");
    $stmt->bind_param("i", $current_chat_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $messages[] = $row;
    }
}

// --- 🟪 Handle message + modes ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && $current_chat_id) {
    $msg = trim($_POST['message'] ?? '');
    
    // 🟢 Distinct and fully working modes
    $teach  = isset($_POST['teach']);
    $lesson = isset($_POST['lesson']);
    $quiz   = isset($_POST['quiz']);
    $file_text = '';

    // 📄 File upload (Requires vendor/autoload.php and Smalot\PdfParser)
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_type = mime_content_type($file_tmp);
        $file_name = $_FILES['file']['name'];

        if (str_contains($file_type, 'pdf')) {
            require_once 'vendor/autoload.php';
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file_tmp);
                $raw = $pdf->getText();
                $clean = preg_replace('/[^A-Za-z0-9\s.,;:!?()\-]/', '', $raw);
                $file_text = "This is content from a PDF titled '$file_name'. Please summarise and teach it step by step:\n\n" . substr($clean, 0, 8000);
            } catch (\Exception $e) {
                $file_text = "⚠️ Could not parse PDF file: " . $e->getMessage();
            }
        } elseif (str_contains($file_type, 'text')) {
            $file_text = "This is a text file named '$file_name'. Please explain this material clearly:\n\n" . file_get_contents($file_tmp);
        } elseif (str_contains($file_type, 'video') || str_contains($file_name, '.mp4')) {
            $file_text = "This is a video file named '$file_name'. Please summarise what the video might teach in a career context.";
        }
    }

    if ($msg !== '' || $file_text !== '') {
        $final_user_msg = trim($msg . "\n" . $file_text);

        // Save user message
        $stmt = $conn->prepare("INSERT INTO messages (chat_id, sender, message) VALUES (?, 'user', ?)");
        $stmt->bind_param("is", $current_chat_id, $final_user_msg);
        $stmt->execute();

        // ✅ Load .env (requires vlucas/phpdotenv)
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// ✅ Gemini API Configuration (using .env)
$api_key = $_ENV["GEMINI_API_KEY"] ?? "";
$api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$api_key";

// ✅ Build history/context
$context = "";
$stmt = $conn->prepare("SELECT sender, message FROM messages WHERE chat_id=? ORDER BY created_at ASC");
$stmt->bind_param("i", $current_chat_id);
$stmt->execute();
$hist = $stmt->get_result();
while ($m = $hist->fetch_assoc()) {
    $context .= ($m['sender'] == 'user' ? 'USER' : 'AI') . ": " . $m['message'] . "\n";
}
$hist->close();


        // 🟢 Enhanced Mode Prompts (Conditional logic implemented here)
        $mode_prompt = "You are Career AI, a helpful and professional assistant. Answer the user's request directly and clearly.";
        $is_mode = $teach || $lesson || $quiz;
        
        if ($is_mode && $msg === '' && $file_text === '') {
            if ($quiz) {
                // 🧩 Quiz Mode follow-up
                $ai_response = "Great! You selected Quiz Mode. **What specific topic or subject do you want your 5 multiple-choice questions to be about?** Please type your answer below.";
            } elseif ($lesson) {
                // 🎓 Lesson Mode follow-up
                $ai_response = "Excellent choice. You selected Lesson Mode. **What career-related topic do you want to break down into sequential lessons?** Type the topic below and I will start Lesson 1.";
            } elseif ($teach) {
                 // 📚 Teach Mode follow-up
                $ai_response = "Understood. You selected Teach Me Mode. **What concept do you need an expert, structured explanation for?**";
            }
            
            // Save initial AI response and redirect, skipping the Gemini API call
            $stmt = $conn->prepare("INSERT INTO messages (chat_id, sender, message) VALUES (?, 'ai', ?)");
            $stmt->bind_param("is", $current_chat_id, $ai_response);
            $stmt->execute();

            header("Location: chat.php?chat_id=$current_chat_id");
            exit;
            
        } elseif ($teach) {
            $mode_prompt = "You are a highly structured expert teacher AI named CareerTutor. Your primary goal is to **Break down the user's request into 3-5 key sections** and explain them clearly with real-world, career-oriented examples. Use markdown for lists and bolding. Be concise yet comprehensive.";
        } elseif ($lesson) {
            $mode_prompt = "You are a dedicated tutor AI named LessonMentor. Your job is to **Break the user's topic into small, sequential lessons (Lesson 1, Lesson 2, etc.)**. Today, you only need to output the content for **Lesson 1** and end with a quick, thought-provoking question related to that lesson. Do NOT provide subsequent lessons until asked.";
        } elseif ($quiz) {
            $mode_prompt = "You are a Quiz Master AI. Your sole task is to **Create 5 challenging multiple-choice questions** related to the user's last message or the current topic. Each question must have 4 options (A, B, C, D) and you **MUST explicitly mark the correct answer** immediately after the questions, like this: 'Correct Answers: 1.C, 2.A, 3.D, 4.B, 5.C'. Do not provide any other text.";
        }

        // API call payload
        $payload = [
            "contents" => [[
                "parts" => [[
                    "text" => "$mode_prompt\n\nFull Conversation History:\n$context"
                ]]
            ]]
        ];

        // cURL setup
        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_ENCODING => "",
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $ai_response = "⚠️ No response from Gemini.";

        if ($response === false) {
            $ai_response = "⚠️ cURL Error: " . $error;
        } elseif ($http_code != 200) {
            $ai_response = "⚠️ API returned HTTP $http_code. Response: " . htmlspecialchars($response);
        } else {
            $data = json_decode($response, true);
            $ai_response = $data["candidates"][0]["content"]["parts"][0]["text"] 
                           ?? $data['error']['message'] 
                           ?? "⚠️ No text found in Gemini response.";
        }

        // Save AI reply
        $stmt = $conn->prepare("INSERT INTO messages (chat_id, sender, message) VALUES (?, 'ai', ?)");
        $stmt->bind_param("is", $current_chat_id, $ai_response);
        $stmt->execute();

        header("Location: chat.php?chat_id=$current_chat_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Career AI Chat</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: url('bg.jpg') no-repeat center center fixed;
    background-size: cover;
}
header { display: flex; justify-content: flex-end; padding: 10px 20px; }
header a { padding: 10px 20px; background: rgba(0, 0, 0, 0.64); color: #fff; text-decoration: none; border-radius: 8px; }
.container { display: flex; justify-content: flex-start; align-items: flex-start; padding: 20px; gap: 20px; }
.chat-wrapper { display: flex; flex-direction: column; width: 70%; }
.chat-box {
    padding: 20px;
    overflow-y: auto;
    background: rgba(0, 0, 0, 0.39);
    border-radius: 10px;
    height: 600px;
    color: white;
    width: 90%;
}
.chat-box .message { margin: 10px 0; padding: 10px 15px; border-radius: 15px; max-width: 70%; clear: both; }

/* 🎨 Updated Transparency for Messages */
.chat-box .message.user { 
    background: rgba(16, 186, 212, 0.68); /* More transparent black */
    float: right; 
    text-align: right; 
}
.chat-box .message.ai { 
    background: rgba(2, 0, 0, 0.7); /* Even more transparent black */
    float: left; 
    white-space: pre-wrap; 
}
/* End of Transparency Update */

.chat-form { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
.chat-form input[type="text"] { flex: 1; padding: 10px; border-radius: 10px; border: 1px solid #eaebeaff; }
.chat-form button { padding: 10px 20px; border: none; border-radius: 10px; color: black; cursor: pointer; }
.file-upload { flex-basis: 100%; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 10px; color: white; }
.teach-btn { background: #ff9800; }
.send-btn{ background: #28a745; }
.lesson-btn { background: #673ab7; }
.quiz-btn { background: #009688; }
#mic { background: #21af28ff; }
#speakBtn { background: #03a9f4; }
#stopBtn { background: red; }

/* 💬 History Sidebar */
.history-box {
    width: 25%;
    padding: 15px;
    background: rgba(0,0,0,0.5);
    border-radius: 12px;
    color: white;
    overflow-y: auto;
    max-height: 80vh;
}
.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.new-chat-btn {
    background: #28a745;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 5px 10px;
    cursor: pointer;
}
.chat-list { list-style: none; padding: 0; margin: 0; }
.chat-item {
    background: rgba(255,255,255,0.1);
    margin: 5px 0;
    padding: 8px 10px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.3s;
}
.chat-item:hover { background: rgba(255,255,255,0.25); }
.chat-title {
    flex: 1;
    cursor: pointer;
    color: #fff;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-decoration: none; /* For the anchor tag */
}
.rename-btn, .delete-btn {
    background: transparent;
    border: none;
    color: white;
    cursor: pointer;
    margin-left: 5px;
    font-size: 1em;
}
.rename-btn:hover { color: #ffc107; }
.delete-btn:hover { color: #f44336; }
.rename-input {
    width: 90%;
    border: none;
    border-radius: 5px;
    padding: 4px;
    outline: none;
}
.chat-list .active {
    background: rgba(255, 255, 255, 0.3);
    border-left: 5px solid #03a9f4;
}
</style>
</head>
<body>
<header><a href="dashboard_main.php">Dashboard</a></header>

<div class="container">
    <div class="chat-wrapper">
        <div class="chat-box" id="chat-box">
            <?php foreach($messages as $m): ?>
                <div class="message <?php echo $m['sender']; ?>"><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
            <?php endforeach; ?>
            <?php if($current_chat_id == 0): ?>
                <div class="message ai" style="float:none; max-width:100%; text-align:center;">
                    Welcome to Career AI Chat! Click **+ New** to start your first conversation.
                </div>
            <?php endif; ?>
        </div>

        <form method="post" enctype="multipart/form-data" class="chat-form">
            <input type="file" name="file" class="file-upload" accept=".pdf,.txt,.mp4">
            <input type="text" name="message" id="messageInput" placeholder="Type your message or use 🎙..." <?php echo ($current_chat_id == 0) ? 'disabled' : ''; ?>>
            <button type="submit" <?php echo ($current_chat_id == 0) ? 'disabled' : ''; ?>>Send</button>
            <button type="button" id="mic" title="Speech-to-Text">🎤</button>
            <button type="button" id="speakBtn" title="Speak Last AI Message">🔊</button>
            <button type="button" id="stopBtn" title="Stop Speaking">⏹️</button>
            <button type="submit" name="teach" class="teach-btn" title="Expert structured explanation" <?php echo ($current_chat_id == 0) ? 'disabled' : ''; ?>>📚 Teach Me</button>
            <button type="submit" name="lesson" class="lesson-btn" title="Step-by-step Lesson 1" <?php echo ($current_chat_id == 0) ? 'disabled' : ''; ?>>🎓 Lesson</button>
            <button type="submit" name="quiz" class="quiz-btn" title="Create a 5-question quiz" <?php echo ($current_chat_id == 0) ? 'disabled' : ''; ?>>🧩 Quiz</button>
        </form>
    </div>

    <div class="history-box">
        <div class="history-header">
            <h3>💬 Your Chats</h3>
            <form method="post" style="display:inline;">
                <button type="submit" name="new_chat" class="new-chat-btn">+ New</button>
            </form>
        </div>

        <ul class="chat-list">
            <?php while($row = $chats->fetch_assoc()): ?>
                <li class="chat-item <?php echo ($current_chat_id == $row['id']) ? 'active' : ''; ?>">
                    <a href="chat.php?chat_id=<?php echo $row['id']; ?>" class="chat-title" ondblclick="event.preventDefault(); renameChat(<?php echo $row['id']; ?>, this)">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </a>
                    <div class="chat-actions">
                        <button class="rename-btn" onclick="event.stopPropagation(); renameChat(<?php echo $row['id']; ?>, this.parentNode.previousElementSibling)">✏️</button>
                        <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete the chat: <?php echo htmlspecialchars($row['title']); ?>?')">🗑️</a>
                    </div>
                </li>
            <?php endwhile; ?>
            <?php if($chats->num_rows == 0): ?>
                <li style="text-align: center; margin-top: 20px;">No chats yet. Click '+ New' to start!</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script>
// Scroll to bottom
document.getElementById("chat-box").scrollTop = document.getElementById("chat-box").scrollHeight;

// 🎙 Speech to text
document.getElementById('mic').onclick = () => {
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        alert("Sorry, Speech Recognition is not supported in your browser.");
        return;
    }
    
    const rec = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    rec.lang = 'en-US';
    rec.interimResults = false;
    rec.maxAlternatives = 1;
    
    document.getElementById('mic').style.backgroundColor = 'red'; 
    rec.onend = () => document.getElementById('mic').style.backgroundColor = '#28a745';

    rec.onresult = e => {
        const transcript = e.results[0][0].transcript;
        document.getElementById('messageInput').value = transcript;
    };
    
    rec.onerror = (e) => {
        console.error('Speech recognition error:', e.error);
        document.getElementById('mic').style.backgroundColor = '#28a745';
        if (e.error === 'not-allowed') {
            alert('Microphone access denied. Please check your browser permissions.');
        }
    };

    rec.start();
};

// 🔊 Text to speech
document.getElementById('speakBtn').onclick = () => {
    const aiMsgs = document.querySelectorAll('.ai');
    if (aiMsgs.length > 0) {
        let fullText = "";
        aiMsgs.forEach(m => fullText += m.innerText + ". \n\n");
        
        const clean = fullText.replace(/[\*\_\#\>]/g,'').replace(/\[.*?\]\(.*?\)/g,'');
        
        const utter = new SpeechSynthesisUtterance(clean);
        utter.lang = 'en-US'; 
        utter.rate = 1;
        window.speechSynthesis.speak(utter);
    }
};

document.getElementById('stopBtn').onclick = () => window.speechSynthesis.cancel();

// ✏️ Rename chat
function renameChat(id, element) {
    const isAnchor = element.tagName === 'A';
    const oldTitle = element.textContent.trim();
    
    if (isAnchor) element.onclick = (e) => e.preventDefault(); 

    const input = document.createElement("input");
    input.type = "text";
    input.value = oldTitle;
    input.className = "rename-input";

    element.replaceWith(input);
    input.focus();
    input.select(); 

    function finaliseRename(save) {
        if (save) {
            saveRename(id, input.value);
        } else {
            const newElement = isAnchor ? document.createElement("a") : document.createElement("span");
            newElement.className = "chat-title";
            newElement.textContent = oldTitle;

            if (isAnchor) {
                newElement.href = `chat.php?chat_id=${id}`;
                newElement.ondblclick = (e) => { e.preventDefault(); renameChat(id, newElement); };
            } else {
                 newElement.ondblclick = () => renameChat(id, newElement);
            }
            
            input.replaceWith(newElement);
        }
    }

    input.addEventListener("blur", () => finaliseRename(false));
    input.addEventListener("keydown", e => {
        if (e.key === "Enter") {
            input.removeEventListener("blur", () => finaliseRename(false));
            finaliseRename(true);
        }
        if (e.key === "Escape") {
            input.removeEventListener("blur", () => finaliseRename(false));
            finaliseRename(false);
        }
    });
}

function saveRename(id, newTitle) {
    const form = document.createElement("form");
    form.method = "POST";
    form.innerHTML = `<input type="hidden" name="rename_chat_id" value="${id}"><input type="hidden" name="new_title" value="${newTitle}">`;
    document.body.appendChild(form);
    form.submit();
}
</script>
</body>
</html>