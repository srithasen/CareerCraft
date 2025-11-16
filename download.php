
<?php
session_start();
require('fpdf/fpdf.php'); // make sure the path is correct

$answers = $_SESSION['answers'] ?? [];

function generateSuggestions($answers) {
    if (count($answers) < 5) {
        return ["Please complete the questionnaire first."];
    }

    $year = $answers[0];
    $project = $answers[1];
    $goal = $answers[2];
    $skills = strtolower($answers[3]);
    $speed = $answers[4];

    $tips = [];

    if ($project === "No") {
        $tips[] = "Start at least 1 project to stand out.";
    } else {
        $tips[] = "Great! Mention your project clearly on your resume.";
    }

    if ($goal === "Placements") {
        $tips[] = "Focus on DSA, mock interviews & aptitude.";
    } elseif ($goal === "Higher Studies") {
        $tips[] = "Prepare for entrance exams and build research-based projects.";
    }

    if ($speed === "Fast") {
        $tips[] = "Try learning advanced tools like Git, Docker, or frameworks.";
    } elseif ($speed === "Medium") {
        $tips[] = "Follow consistent learning with short daily goals.";
    } else {
        $tips[] = "Set smaller milestones and practice regularly.";
    }

    if (!str_contains($skills, "java") && !str_contains($skills, "python")) {
        $tips[] = "Learn at least one popular language like Java or Python.";
    }

    return $tips;
}

$suggestions = generateSuggestions($answers);

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Your Career Path Suggestions', 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);

foreach ($suggestions as $tip) {
    $pdf->MultiCell(0, 10, "- $tip");
}

$pdf->Output('D', 'Career_Report.pdf');
exit;
?>
