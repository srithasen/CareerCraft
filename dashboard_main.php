<?php
session_start();
include 'db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// ✅ Fetch user info including onboarding_done
$stmt = $conn->prepare("SELECT name, onboarding_done FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// ✅ Save username and onboarding status in session
$_SESSION['name'] = $user['name'];
$_SESSION['onboarding_done'] = $user['onboarding_done'];

// ✅ Redirect to onboarding if not completed
if ($_SESSION['onboarding_done'] == 0) {
    header("Location: onboarding.php");
    exit();
}

// ✅ Ensure username is properly set
$username = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Your Dashboard</title>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0; padding: 0;
    background: url('bg.jpg') no-repeat center center fixed;
    background-size: cover;
    color: white;
  }
  nav {
  background: rgba(0,0,0,0.7);
  display: flex;
  justify-content: space-between; /* left items on left, right items on right */
  align-items: center;
    padding: 10px 20px;
    position: fixed;
    width: 100%;
    top: 0; left: 0;
    z-index: 1000;
  }
  nav a {
  color: white;
  margin-right: 15px;
  text-decoration: none;
  font-weight: bold;
}

nav a:last-child {
  margin-right: 0; /* remove margin from last link */
}

nav a:hover {
  text-decoration: underline;
}
  
  .container {
    padding: 100px 20px 40px 20px; /* Padding top to clear nav */
    max-width: 900px;
    margin: auto;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 12px;
  }
  h1 {
    margin-bottom: 30px;
  }
  .dashboard-section {
    background: rgba(30, 30, 30, 0.8);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
  }
  .dashboard-section a {
    color: #ff6347;
    font-weight: bold;
    text-decoration: none;
  }
  .dashboard-section a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>

<nav>
  <div class="nav-left">
    <a href="index.html">Home</a>
    <a href="profile.php">Profile</a>
  </div>
  <div class="nav-right">
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <h1>Welcome back, <?= $username ?>!</h1>

  <div class="dashboard-section">
    <h2>Chat with AI</h2>
    <p>Get personalized career guidance, learn concepts faster, and ask anything in real-time with your smart AI assistant.</p>
    <a href="chat.php">Open AI Chat &rarr;</a>
  </div>

  <div class="dashboard-section">
    <h2>Resume Builder</h2>
    <p>Create a professional, ATS-friendly resume with customizable templates and real-time editing tools.</p>
    <a href="resume_builder.php">Go to Resume Builder &rarr;</a>
  </div>

  <div class="dashboard-section">
    <h2>Resume Analyzer</h2>
    <p>Upload your resume and get instant feedback, skill-gap analysis, and improvement suggestions powered by AI.</p>
    <a href="resume_analyzer.php">Go to Resume Analyzer &rarr;</a>
  </div>

  <div class="dashboard-section">
    <h2>Save your Notes</h2>
    <p>Save your ideas, class notes, tasks, and reminders with a smart note editor supporting voice input and rich formatting.</p>
    <a href="notes.php">Save your Notes &rarr;</a>
  </div>

  <div class="dashboard-section">
    <h2>Leetcode</h2>
    <p>Improve your coding skills with structured DSA problems, explanations, and AI-generated hints for faster learning.</p>
    <a href="dsa_categories.php">Leetcode  &rarr;</a>
  </div>

  
</div>

</body>
</html>
