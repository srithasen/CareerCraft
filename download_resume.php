<?php
session_start();
include 'db.php';
require_once 'vendor/autoload.php';

use Mpdf\Mpdf;

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("Unauthorized access");
}

$resume_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $resume_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$resume = $result->fetch_assoc();
$stmt->close();

if (!$resume) {
    die("Resume not found");
}

$mpdf = new Mpdf();

// Choose template CSS
$template = $resume['template_selected'];
$stylesheet = file_get_contents('css/resume.css');

// Add CSS to PDF
$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

// Create PDF content
$html = "
<body>
    <h1>{$resume['full_name']}</h1>
    <p><strong>Email:</strong> {$resume['email']}</p>
    <p><strong>Phone:</strong> {$resume['phone']}</p>
    <div class='section'>
        <h3>Education</h3>
        <p>{$resume['education']}</p>
    </div>
    <div class='section'>
        <h3>Skills</h3>
        <p>{$resume['skills']}</p>
    </div>
    <div class='section'>
        <h3>Experience</h3>
        <p>{$resume['experience']}</p>
    </div>
    <div class='section'>
        <h3>Projects</h3>
        <p>{$resume['projects']}</p>
    </div>
    <div class='section'>
        <h3>Certifications</h3>
        <p>{$resume['certifications']}</p>
    </div>
</body>
";

$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$filename = "Resume_{$resume['full_name']}_".time().".pdf";
$mpdf->Output($filename, 'D');
exit();
?>
