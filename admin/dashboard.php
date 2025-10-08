<?php
// admin/dashboard.php
session_start();
require_once '../database/conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch admin's name
try {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = htmlspecialchars($user['name']);
} catch (PDOException $e) {
    error_log('Dashboard error: ' . $e->getMessage(), 3, '../debug.log');
    header('Location: login.php?error=database');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Task Tube</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f7f9fc;
            --card-bg: #ffffff;
            --text-color: #1a1a1a;
            --accent-color: #22c55e;
            --accent-hover: #16a34a;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] {
            --bg-color: #1f2937;
            --card-bg: #2d3748;
            --text-color: #e5e7eb;
            --accent-color: #34d399;
            --accent-hover: #22c55e;
            --shadow-color: rgba(0, 0, 0, 0.3);
        }

        body {
            background: var(--bg-color);
            color: var(--text-color);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px;
            text-align: center;
        }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 6px 16px var(--shadow-color);
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        a, button {
            display: inline-block;
            padding: 14px 20px;
            background: var(--accent-color);
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
            margin: 10px;
        }

        a:hover, button:hover {
            background: var(--accent-hover);
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Welcome, <?php echo $username; ?>!</h1>
            <p>Admin Dashboard | Task Tube</p>
            <a href="upload.php">Upload Video</a>
            <button onclick="logout()">Logout</button>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
