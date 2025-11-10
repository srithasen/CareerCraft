<?php
// dsa_categories.php
session_start();
include 'db.php';

$stmt = $conn->prepare("SELECT id, category_name FROM dsa_categories ORDER BY category_name");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>DSA Concepts</title>
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
        .category-list {
            list-style: none;
            padding: 0;
        }
        .category-list li {
            margin: 10px 0;
        }
        .category-list a {
            display: block;
            padding: 15px;
            background-color: #444;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .category-list a:hover {
            background-color: #ff6347;
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
        <h1>DSA Concepts - Categories</h1>
        <div class="dashboard-section">
            <ul class="category-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <a href="dsa_problems_list.php?category_id=<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['category_name']); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</body>
</html>