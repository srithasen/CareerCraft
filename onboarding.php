<?php
session_start();
require 'db.php';

// ---------------------------
// 1️⃣ Redirect if not logged in
// ---------------------------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ---------------------------
// 2️⃣ Fetch user info
// ---------------------------
$stmt = $conn->prepare("SELECT name, role, onboarding_done FROM users WHERE id=?");
if (!$stmt) die("Prepare failed: " . $conn->error);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) die("User not found in database!");

// ---------------------------
// Define role and username for JS
// ---------------------------
$role = isset($user['role']) ? $user['role'] : '';
$name = isset($user['name']) ? $user['name'] : '';

// ---------------------------
// 3️⃣ Redirect returning users
// ---------------------------
if ($user['onboarding_done'] != 0) {
    $_SESSION['onboarding_done'] = 1;
    header("Location: dashboard_main.php");
    exit();
}

// ---------------------------
// 4️⃣ Handle form submission
// ---------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Example: capture form inputs
    $career_goal = trim($_POST['career_goal'] ?? '');
    $skills = trim($_POST['skills'] ?? '');

    // Optional: save answers in DB
    $update = $conn->prepare("UPDATE users SET onboarding_done = 1, career_goal = ?, skills = ? WHERE id = ?");
    if (!$update) die("Prepare failed: " . $conn->error);

    $update->bind_param("ssi", $career_goal, $skills, $user_id);

    if (!$update->execute()) die("Execute failed: " . $update->error);

    $_SESSION['onboarding_done'] = 1;

    header("Location: dashboard_main.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Onboarding - SkillPath AI</title>
<style>
    /* Only skills list gets scrollbar */
.scroll-box {
  max-height: 200px;   /* Adjust height as needed */
  overflow-y: auto;
  border: 1px solid #ccc;
  padding: 10px;
  margin-bottom: 10px;
}

.scroll-box label {
  display: block;   /* Vertical stacking */
  margin: 5px 0;
}

/* Roles list - vertical but no scrollbar */
.role-box label {
  display: block;   /* Vertical stacking */
  margin: 5px 0;
}
.scroll-box {
  max-height: 150px;   /* control scroll height */
  overflow-y: auto;
  display: flex;
  flex-direction: column; /* vertical list */
  
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

body {
  margin:0; padding:0;
  font-family: Arial, sans-serif;
  background: url('bg.jpg') no-repeat center center fixed;
  background-size: cover;
  color:white;
  overflow:hidden; /* Prevent whole page scroll */
}
.container {
  display: flex; height:100vh; align-items:center; justify-content:space-between; padding:40px;
}
.left {
  flex:1; padding-left:60px;
}
.right {
  flex:1; display:flex; justify-content:flex-end; align-items:center;
}
.question-box {
  background: rgba(0,0,0,0.7);
  padding: 25px 30px;
  border-radius: 15px;
  width: 450px;
  max-height: 85vh;
  overflow:hidden;
}
.question-box h3 { margin-bottom:15px; }
.radio-group label { display:block; margin-bottom:8px; }
.scroll-box {
  max-height:200px;
  overflow-y:auto;
  padding-right:6px;
}
.scroll-box::-webkit-scrollbar { width:6px; }
.scroll-box::-webkit-scrollbar-thumb { background:#888; border-radius:4px; }
.btn {
  background:tomato; border:none; padding:10px 20px;
  border-radius:6px; color:white; cursor:pointer; margin-top:10px;
}
</style>
</head>
<body>
<div class="container">
  <div class="left">
    <h1>Welcome, <?=htmlspecialchars($name)?>!</h1>
    <p>Let's set up your profile now.</p>
  </div>
  <div class="right">
    <div class="question-box" id="question-box">
      <!-- Dynamic questions -->
    </div>
  </div>
</div>

<script>
const role = "<?= $role ?>";
let current = 0;
let formData = {};

// Predefined skills + roles
const skills = [
  "Java","Python","C#","JavaScript","TypeScript","HTML","CSS","React","Angular","Vue.js",
  "Spring Boot","Node.js","Express.js","Django","Flask","SQL","MySQL","PostgreSQL","MongoDB",
  "AWS","Azure","Google Cloud","Docker","Kubernetes","Git","CI/CD","REST APIs","GraphQL",
  "Machine Learning","Deep Learning","TensorFlow","PyTorch","Cybersecurity","Blockchain",
  "UI/UX Design","Agile Methodologies","Scrum","Data Analysis","Power BI","Tableau"
];
const roles = [
  "Software Engineer","Full Stack Developer","Backend Developer","Frontend Developer",
  "Mobile App Developer","DevOps Engineer","Cloud Engineer","Data Analyst","Data Scientist",
  "Machine Learning Engineer","UI/UX Designer","QA Engineer","Database Administrator",
  "System Administrator","Security Analyst","Game Developer","Embedded Systems Engineer",
  "Product Manager","Technical Support Engineer","Business Analyst","Scrum Master",
  "Solutions Architect","Network Engineer","AI Engineer","Blockchain Developer"
];

// Question flows
let questions = [];
if (role === "Student") {
  questions = [
    { q: "Which year are you in?", type: "radio", key:"year", options: ["1st","2nd","3rd","4th"] },
    { q: "Select your primary skills:", type: "skillList", key:"skills" },
    { q: "What is the ideal job you are looking for?", type:"roleList", key:"targetRole" },
    { q: "Do you have any project experience?", type: "radio", key:"experience", options: ["Yes","No"] },
    { q: "Do you have any intership experience?", type: "radio", key:"internship", options: ["Yes","No"] },
    { q: "What's your learning speed?", type: "radio", key:"learningSpeed", options: ["Fast","Medium","Slow"] },
    { q: "Are you preparing for placements or higher studies?", type: "radio", key:"goal", options:["Placements","Higher Studies"] }
    
  ];
} else if (role === "Graduate") {
  questions = [
    { q: "Which year did you graduate?", type: "radio", key:"gradYear", options:["2025","2024","2023","2022","Before 2022"] },
    { q: "What is your degree/stream?", type: "radio", key:"degree", options:["B.Tech CSE","B.Tech ECE","B.Tech IT","B.Sc CS","MCA","MBA","Other"] },
    { q: "Select your primary skills:", type: "skillList", key:"skills" },
    { q: "Do you have any internship experience?", type:"radio", key:"internship", options:["Yes","No"] },
    { q: "Which type of job are you looking for?", type:"roleList", key:"targetRole" },
    { q: "Preferred work location?", type:"radio", key:"workLocation", options:["Any","Remote","On-site","Hybrid"] }
  ];
} else if (role === "Currently doing Job") {
  questions = [
    { q:"Select your current job title:", type:"roleList", key:"jobTitle" },
    { q:"How many years of experience do you have?", type:"radio", key:"experience", options:["<1 year","1–3 years","3–5 years","5+ years"] },
    { q:"Select the primary skills/technologies you use:", type:"skillList", key:"skills" },
    { q:"Why do you want to switch jobs?", type:"radio", key:"switchReason", options:["Better Salary","Better Work-Life Balance","Different Tech Stack","Leadership Role","Other"] },
    { q:"Select your target job role:", type:"roleList", key:"targetRole" },
    { q:"Select the skills/technologies you want to learn:", type:"skillList", key:"desiredSkills" },
    { q:"Preferred work mode?", type:"radio", key:"workMode", options:["Remote","Hybrid","On-site"] }
    
  ];
}

function showQuestion() {
  if (current >= questions.length) {
    saveAnswers();
    return;
  }

  const q = questions[current];
  let html = `<h3>${q.q}</h3>`;

  if (q.type === "radio") {
    html += `<div class="radio-group">`;
    q.options.forEach(opt => {
      html += `<label><input type="radio" name="answer" value="${opt}"> ${opt}</label>`;
    });
    html += `</div><button class="btn" onclick="next()">Next</button>`;
  }
  else if (q.type === "skillList") {
    html += `<div class="scroll-box">`;
    skills.forEach(skill => {
      html += `<label><input type="checkbox" name="answer" value="${skill}"> ${skill}</label><br>`;
    });
    html += `</div><button class="btn" onclick="next()">Next</button>`;
  }
  else if (q.type === "roleList") {
    html += `<div class="scroll-box">`;
    roles.forEach(role => {
      html += `<label><input type="checkbox" name="answer" value="${role}"> ${role}</label><br>`;
    });
    html += `</div><button class="btn" onclick="next()">Next</button>`;
  }

  document.getElementById("question-box").innerHTML = html;
}

function next() {
    const q = questions[current];
    let value = "";

    if (q.type === "radio") {
        const selected = document.querySelector("input[name='answer']:checked");
        if (!selected) {
            alert("Please select an option before proceeding!");
            return;
        }
        value = selected.value;
    } 
    else if (q.type === "skillList" || q.type === "roleList") {
        const selected = Array.from(document.querySelectorAll("input[name='answer']:checked"));
        if (selected.length === 0) {
            alert("Please select at least one option!");
            return;
        }
        value = selected.map(s => s.value).join(", ");
    } 
    else if (q.type === "file") {
        const fileInput = document.getElementById("resume");
        if (!fileInput || fileInput.files.length === 0) {
            alert("Please upload your resume to continue!");
            return;
        }
        value = fileInput.files[0].name;
    }

    // ✅ Map JS keys to DB columns
    if (q.key) {
        if (q.key === "targetRole") formData["target_role"] = value;
        else if (q.key === "career_goal") formData["career_goal"] = value;
        else if (q.key === "skills") formData["skills"] = value;
        else if (q.key === "year") formData["year"] = value;
        else if (q.key === "experience") formData["experience"] = value;
        else if (q.key === "learningSpeed") formData["learning_speed"] = value;
        else if (q.key === "goal") formData["goal"] = value;
    }

    current++;
    showQuestion();
}


function saveAnswers() {
  fetch('save_onboarding.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      window.location.href = 'dashboard_main.php';
    } else {
      alert('Failed to save data: ' + data.message);
    }
  })
  .catch(err => {
    console.error(err);
    alert('Error saving data.');
  });
}

showQuestion();
</script>
</body>
</html>

