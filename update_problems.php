<?php
// update_problem.php
session_start();
include 'db.php';

if(isset($_POST['problem_id'])) {
    $problem_id = intval($_POST['problem_id']);
    $status = $_POST['status'];
    $due_date = $_POST['due_date'] ? $_POST['due_date'] : NULL;

    $stmt = $conn->prepare("UPDATE dsa_problems SET status = ?, due_date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $due_date, $problem_id);

    if($stmt->execute()){
        $_SESSION['msg'] = "Problem updated successfully!";
    } else {
        $_SESSION['msg'] = "Update failed. Try again!";
    }
}

header("Location: dsa_problems_list.php?category_id=".$_POST['category_id']);
exit;
