<?php
// admin/upload.php
session_start();
require_once '../database/conn.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

// Fetch user role (assuming 'users' table has a 'role' column with 'admin' for admins)
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['role'] !== 'admin') {
        header('Location: ../users/home.php'); // Redirect to user dashboard if not admin
        exit;
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage(), 3, '../debug.log');
    header('Location: ../signin.php?error=database');
    exit;
}

// Handle video upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $reward = floatval($_POST['reward']);

    // Validate inputs
    if (empty($title) || $reward <= 0 || !isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Invalid input or file upload error.';
    } else {
        $uploadDir = '../users/videos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = basename($_FILES['video_file']['name']);
        $filePath = $uploadDir . $fileName;

        // Validate file type (only allow video files)
        $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        $fileType = mime_content_type($_FILES['video_file']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Invalid file type. Only MP4, WebM, OGG allowed.';
        } elseif (move_uploaded_file($_FILES['video_file']['tmp_name'], $filePath)) {
            // Insert into database
            try {
                $stmt = $pdo->prepare("INSERT INTO videos (title, url, reward) VALUES (?, ?, ?)");
                $relativeUrl = 'users/videos/' . $fileName; // Relative path for src
                $stmt->execute([$title, $relativeUrl, $reward]);
                $success = 'Video uploaded successfully.';
            } catch (PDOException $e) {
                error_log('Video insert error: ' . $e->getMessage(), 3, '../debug.log');
                $error = 'Database error during insert.';
                unlink($filePath); // Remove file on failure
            }
        } else {
            $error = 'Failed to move uploaded file.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Upload Video | Cash Tube</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #f7f9fc;
            --card-bg: #ffffff;
            --text-color: #1a1a1a;
            --subtext-color: #6b7280;
            --border-color: #d1d5db;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --accent-color: #22c55e;
            --accent-hover: #16a34a;
        }

        [data-theme="dark"] {
            --bg-color: #1f2937;
            --card-bg: #2d3748;
            --text-color: #e5e7eb;
            --subtext-color: #9ca3af;
            --border-color: #4b5563;
            --shadow-color: rgba(0, 0, 0, 0.3);
            --accent-color: #34d399;
            --accent-hover: #22c55e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
        }

        .form-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 6px 16px var(--shadow-color);
        }

        .input-container {
            position: relative;
            margin-bottom: 28px;
        }

        .input-container input,
        .input-container input[type="file"] {
            width: 100%;
            padding: 14px 0;
            font-size: 16px;
            border: none;
            border-bottom: 2px solid var(--border-color);
            background: transparent;
            color: var(--text-color);
            outline: none;
            transition: border-color 0.3s ease;
        }

        .input-container input:focus,
        .input-container input:valid {
            border-bottom-color: var(--accent-color);
        }

        .input-container label {
            position: absolute;
            top: 14px;
            left: 0;
            font-size: 16px;
            color: var(--subtext-color);
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .input-container input:focus ~ label,
        .input-container input:valid ~ label {
            top: -18px;
            font-size: 12px;
            color: var(--accent-color);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--accent-color);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .submit-btn:hover {
            background: var(--accent-hover);
            transform: scale(1.02);
        }

        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
        }

        .success {
            background: #d1fae5;
            color: #065f46;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
        }

        @media (max-width: 768px) {
            .container {
                padding: 16px;
            }

            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Panel - Upload Video</h1>
        </div>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="input-container">
                    <input type="text" id="title" name="title" required>
                    <label for="title">Video Title</label>
                </div>
                <div class="input-container">
                    <input type="number" id="reward" name="reward" step="0.01" min="0.01" required>
                    <label for="reward">Reward Amount ($)</label>
                </div>
                <div class="input-container">
                    <input type="file" id="video_file" name="video_file" accept="video/*" required>
                    <label for="video_file">Upload Video File</label>
                </div>
                <button type="submit" class="submit-btn">Upload Video</button>
            </form>

            <?php if (isset($success)): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php elseif (isset($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Optional: Add client-side validation or enhancements if needed
    </script>
</body>
</html>
