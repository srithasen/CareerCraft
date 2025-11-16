<?php
// dsa_problems_list.php
session_start();
include 'db.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
if ($category_id === 0) {
    echo "No category selected. Please go back to <a href='dsa_categories.php'>DSA Categories</a>.";
    exit;
}

$stmt_category = $conn->prepare("SELECT category_name FROM dsa_categories WHERE id = ?");
$stmt_category->bind_param("i", $category_id);
$stmt_category->execute();
$category_name = $stmt_category->get_result()->fetch_assoc()['category_name'];

$stmt_problems = $conn->prepare("SELECT * FROM dsa_problems WHERE category_id = ? ORDER BY problem_title");
$stmt_problems->bind_param("i", $category_id);
$stmt_problems->execute();
$problems_result = $stmt_problems->get_result();

<<<<<<< HEAD
// Define status options for the select field
=======
>>>>>>> aadfe91714b8e381e3c613ec3ab3c310d595d975
$status_options = ['Not started', 'Started', 'Completed'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<<<<<<< HEAD
    <meta charset="UTF-8" />
    <title><?php echo htmlspecialchars($category_name); ?> Problems</title>
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
            justify-content: space-between;
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
        nav a:last-child { margin-right: 0; }
        nav a:hover { text-decoration: underline; }
        .container {
            padding: 100px 20px 40px 20px;
            max-width: 900px;
            margin: auto;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 12px;
        }
        h1 { margin-bottom: 30px; }
        .dashboard-section {
            background: rgba(30, 30, 30, 0.8);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .problems-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .problems-table th, .problems-table td {
            border: 1px solid #555;
            padding: 12px;
            text-align: left;
        }
        .problems-table th {
            background-color: #333;
            color: #ff6347;
            white-space: nowrap; /* Prevent headers from wrapping */
        }
        .problems-table tr:nth-child(even) {
            background-color: rgba(0,0,0,0.2);
        }
        .problems-table tr:hover {
            background-color: rgba(0,0,0,0.4);
        }
        .status-select {
            padding: 5px;
            border-radius: 4px;
            background-color: #444;
            color: white;
            border: 1px solid #666;
            cursor: pointer;
        }
        .date-input {
            padding: 5px;
            border-radius: 4px;
            background-color: #444;
            color: white;
            border: 1px solid #666;
            cursor: pointer;
            font-family: inherit;
        }
        .notes-text {
            display: block; /* Ensures notes get their own space */
            white-space: pre-wrap; /* Preserves line breaks in notes */
            min-height: 20px;
            padding: 5px;
        }
        .action-link {
            display: inline-block;
            background-color: #ff4d00ff;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .action-link:hover {
            background-color: #e43d01ff;
        }
        .back-link-top {
            display: inline-block;
            margin-bottom: 20px;
            color: #ff6347;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link-top:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav>

        <div class="nav-right">
            <a href="dashboard_main.php">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <a href="dsa_categories.php" class="back-link-top">&larr; Back to Categories</a>
        <h1><?php echo htmlspecialchars($category_name); ?> Problems</h1>
        <div class="dashboard-section">
            <?php if ($problems_result->num_rows > 0): ?>
                <table class="problems-table">
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>Problem Link</th>
                            <th>Solution Link</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($problem = $problems_result->fetch_assoc()): ?>
                            <tr id="problem-<?php echo $problem['id']; ?>">
                                <td><?php echo htmlspecialchars($problem['problem_title']); ?></td>
                                <td>
                                    <?php if ($problem['leetcode_link']): ?>
                                        <a href="<?php echo htmlspecialchars($problem['leetcode_link']); ?>" target="_blank">LeetCode</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($problem['solution_video_link']): ?>
                                        <a href="<?php echo htmlspecialchars($problem['solution_video_link']); ?>" target="_blank">Watch Solution</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select 
                                        class="status-select" 
                                        data-problem-id="<?php echo $problem['id']; ?>" 
                                        onchange="updateField(this, 'status', this.value)"
                                    >
                                        <?php foreach ($status_options as $option): ?>
                                            <option 
                                                value="<?php echo $option; ?>" 
                                                <?php echo ($problem['status'] == $option) ? 'selected' : ''; ?>
                                            >
                                                <?php echo $option; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input 
                                        type="date" 
                                        class="date-input" 
                                        value="<?php echo $problem['due_date'] ?? ''; ?>" 
                                        data-problem-id="<?php echo $problem['id']; ?>"
                                        onchange="updateField(this, 'due_date', this.value)"
                                    >
                                </td>
                                <td>
                                    <span class="notes-text"><?php echo nl2br(htmlspecialchars($problem['notes'])); ?></span>
                                </td>
                                <td>
                                    <a href="dsa_practice_problem.php?problem_id=<?php echo $problem['id']; ?>" class="action-link">Practice</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No problems found for this category.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        /**
         * Sends an AJAX request to update the database without a page reload.
         * @param {HTMLElement} element - The HTML element that triggered the change.
         * @param {string} field - The database column name ('status' or 'due_date').
         * @param {string} value - The new value for the field.
         */
        function updateField(element, field, value) {
            const problemId = element.getAttribute('data-problem-id');
            const row = document.getElementById(`problem-${problemId}`);
            
            if (row) {
                // A quick visual indicator that the update is in progress
                row.style.backgroundColor = 'rgba(92, 184, 92, 0.5)'; // A light green flash
                setTimeout(() => {
                    row.style.backgroundColor = ''; // Restore original background after a short delay
                }, 1000); 
            }

            fetch('update_dsa_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `problem_id=${problemId}&field=${field}&value=${encodeURIComponent(value)}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Update failed:', data.error);
                    alert('Error saving data: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                alert('A network error occurred while saving data.');
            });
        }
    </script>
</body>
</html>
=======
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($category_name); ?> Problems</title>

<style>
/* SAME UI AS BEFORE */
body {
    font-family: Arial;
    margin: 0;
    background: url('bg.jpg') no-repeat center center fixed;
    background-size: cover;
    color: white;
}

nav {
    background: rgba(0,0,0,0.7);
    padding: 12px 20px;
    position: fixed;
    width: 100%;
    top: 0;
}

nav a {
    color: white;
    text-decoration: none;
    font-weight: bold;
}

.container {
    padding: 100px 20px;
    max-width: 900px;
    margin: auto;
    background: rgba(0,0,0,0.6);
    border-radius: 12px;
}

.dashboard-section {
    background: rgba(30,30,30,0.8);
    padding: 20px;
    border-radius: 10px;
}

.problems-table {
    width: 100%;
    border-collapse: collapse;
}

.problems-table th, .problems-table td {
    border: 1px solid #555;
    padding: 12px;
}

.problems-table th {
    background: #333;
    color: #ff6347;
}

/* PERMANENT ROW COLORS */
.completed-row {
    background-color: rgba(46, 204, 113, 0.35) !important; /* green */
}

.started-row {
    background-color: rgba(241, 196, 15, 0.35) !important; /* yellow */
}

.notstarted-row {
    background-color: rgba(255, 255, 255, 0.05) !important;
}

.status-select, .date-input {
    background: #444;
    color: white;
    border: 1px solid #666;
    padding: 6px;
    border-radius: 4px;
}

.action-link {
    background: #ff4d00ff;
    padding: 8px 12px;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    font-weight: bold;
}

.back-link-top {
    color: #ff6347;
    font-weight: bold;
}
</style>

</head>

<body>

<nav>
    <a href="dashboard_main.php">Back to Dashboard</a>
</nav>

<div class="container">

    <a href="dsa_categories.php" class="back-link-top">&larr; Back to Categories</a>
    <h1><?php echo htmlspecialchars($category_name); ?> Problems</h1>

    <div class="dashboard-section">
    <table class="problems-table">
        <thead>
            <tr>
                <th>Topic</th>
                <th>Problem Link</th>
                <th>Solution Link</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Notes</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($p = $problems_result->fetch_assoc()): ?>

            <?php
            // APPLY ROW COLOR BASED ON DB STATUS
            $rowClass = "notstarted-row";
            if ($p['status'] == "Started") $rowClass = "started-row";
            if ($p['status'] == "Completed") $rowClass = "completed-row";
            ?>

            <tr id="problem-<?php echo $p['id']; ?>" class="<?php echo $rowClass; ?>">

                <td><?php echo htmlspecialchars($p['problem_title']); ?></td>

                <td><?php if ($p['leetcode_link']): ?>
                    <a href="<?php echo $p['leetcode_link']; ?>" target="_blank">LeetCode</a>
                <?php endif; ?></td>

                <td><?php if ($p['solution_video_link']): ?>
                    <a href="<?php echo $p['solution_video_link']; ?>" target="_blank">Watch Solution</a>
                <?php endif; ?></td>

                <td>
                    <select class="status-select"
                            data-problem-id="<?php echo $p['id']; ?>"
                            onchange="updateField(this, 'status', this.value)">
                        <?php foreach ($status_options as $op): ?>
                            <option value="<?php echo $op; ?>"
                                <?php echo ($p['status'] == $op) ? 'selected' : ''; ?>>
                                <?php echo $op; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>

                <td>
                    <input type="date"
                           class="date-input"
                           value="<?php echo $p['due_date']; ?>"
                           data-problem-id="<?php echo $p['id']; ?>"
                           onchange="updateField(this, 'due_date', this.value)">
                </td>

                <td><?php echo nl2br(htmlspecialchars($p['notes'])); ?></td>

                <td>
                    <a href="dsa_practice_problem.php?problem_id=<?php echo $p['id']; ?>" class="action-link">Practice</a>
                </td>

            </tr>

        <?php endwhile; ?>
        </tbody>
    </table>
    </div>

</div>

<script>
/* UPDATE AND CHANGE ROW COLOR PERMANENTLY */
function updateField(element, field, value) {
    
    const id = element.dataset.problemId;
    const row = document.getElementById("problem-" + id);

    fetch("update_dsa_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `problem_id=${id}&field=${field}&value=${encodeURIComponent(value)}`
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) {
            alert("Error: " + data.error);
            return;
        }

        // REMOVE OLD COLOR CLASS
        row.classList.remove("completed-row", "started-row", "notstarted-row");

        // APPLY NEW COLOR PERMANENTLY
        if (value === "Completed") row.classList.add("completed-row");
        else if (value === "Started") row.classList.add("started-row");
        else row.classList.add("notstarted-row");
    })

    .catch(() => {
        alert("Network error while saving data.");
    });
}
</script>

</body>
</html>
>>>>>>> aadfe91714b8e381e3c613ec3ab3c310d595d975
