<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';

// Fetch video details
$video = null;
if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT id, title, url, reward FROM videos WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$video) {
            $_SESSION['error'] = 'Video not found.';
            header("Location: dashboard.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to fetch video: ' . $e->getMessage();
        error_log('Edit video fetch error: ' . $e->getMessage(), 3, '../debug.log');
        header("Location: dashboard.php");
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $url = filter_var($_POST['url'], FILTER_SANITIZE_STRING);
    $reward = filter_var($_POST['reward'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    if (empty($title) || empty($url) || empty($reward)) {
        $_SESSION['error'] = 'All fields are required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE videos SET title = ?, url = ?, reward = ? WHERE id = ?");
            $stmt->execute([$title, $url, $reward, $_GET['id']]);
            $_SESSION['success'] = 'Video updated successfully.';
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to update video: ' . $e->getMessage();
            error_log('Edit video error: ' . $e->getMessage(), 3, '../debug.log');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Video - Task Tube</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .edit-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .edit-container h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .edit-form input {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .edit-form button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .edit-form button:hover {
            background-color: #0056b3;
        }

        .error, .success {
            margin-bottom: 15px;
            text-align: center;
        }

        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2>Edit Video</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        <?php endif; ?>
        <form action="" method="POST" class="edit-form">
            <input type="text" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" placeholder="Video Title" required>
            <input type="text" name="url" value="<?php echo htmlspecialchars($video['url']); ?>" placeholder="Video URL (e.g., users/videos/video.mp4)" required>
            <input type="number" name="reward" value="<?php echo htmlspecialchars($video['reward']); ?>" placeholder="Reward ($)" step="0.01" required>
            <button type="submit">Update Video</button>
        </form>
        <a href="dashboard.php" style="display: block; text-align: center; margin-top: 10px; color: #007bff;">Back to Dashboard</a>
    </div>
</body>
</html>
