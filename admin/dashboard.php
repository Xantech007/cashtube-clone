<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';

// Set time zone to WAT
date_default_timezone_set('Africa/Lagos');

// Fetch all videos
try {
    $stmt = $pdo->prepare("SELECT id, title, url, reward FROM videos ORDER BY id");
    $stmt->execute();
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Video fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $videos = [];
    $error = 'Failed to load videos: ' . $e->getMessage();
}

// Fetch total registered users
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS user_count FROM users");
    $stmt->execute();
    $user_count = $stmt->fetch(PDO::FETCH_ASSOC)['user_count'];
} catch (PDOException $e) {
    error_log('User count fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $user_count = 0;
    $error = isset($error) ? $error . '<br>Failed to load user count: ' . $e->getMessage() : 'Failed to load user count: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .dashboard-container h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .dashboard-container p {
            color: #555;
            font-size: 16px;
        }

        .logout-link, .management-link {
            display: inline-block;
            margin: 10px 5px;
            padding: 10px 20px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .logout-link {
            background-color: #dc3545;
        }

        .logout-link:hover {
            background-color: #c82333;
        }

        .management-link {
            background-color: #007bff;
        }

        .management-link:hover {
            background-color: #0056b3;
        }

        /* Management Buttons Section */
        .management-buttons {
            margin: 20px 0;
        }

        /* Video Management Section */
        .video-management {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .video-management h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .add-video-form {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .add-video-form input[type="text"],
        .add-video-form input[type="number"],
        .add-video-form input[type="file"] {
            flex: 1;
            min-width: 200px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .add-video-form input[type="file"] {
            padding: 4px;
        }

        .add-video-form button {
            padding: 8px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            position: relative;
        }

        .add-video-form button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .add-video-form button:hover:not(:disabled) {
            background-color: #0056b3;
        }

        .loading {
            display: none;
            margin-top: 10px;
            color: #007bff;
            font-size: 14px;
        }

        /* Scrollable Table */
        .table-container {
            max-width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            max-height: 400px;
            margin-top: 10px;
        }

        .video-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .video-table th,
        .video-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }

        .video-table th {
            background-color: #f8f9fa;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .video-table td {
            color: #555;
        }

        .video-table a {
            margin: 0 5px;
            text-decoration: none;
            color: #007bff;
        }

        .video-table a.delete {
            color: #dc3545;
        }

        .video-table a:hover {
            text-decoration: underline;
        }

        .error, .success {
            color: red;
            margin-bottom: 15px;
        }

        .success {
            color: green;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                margin: 20px;
                padding: 15px;
            }

            .add-video-form {
                flex-direction: column;
            }

            .add-video-form input,
            .add-video-form button {
                width: 100%;
                min-width: unset;
            }

            .video-table {
                min-width: 100%;
            }

            .management-link {
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome to Task Tube Admin Dashboard</h2>
        <p>Hello, <?php echo htmlspecialchars($_SESSION['admin_email']); ?>!</p>
        <p>Total Registered Users: <strong><?php echo $user_count; ?></strong></p>

        <!-- Management Buttons -->
        <div class="management-buttons">
            <a href="manage_verifications.php" class="management-link">Manage Verification Requests</a>
            <a href="manage_withdrawals.php" class="management-link">Manage Withdrawals</a>
        </div>

        <a href="logout.php" class="logout-link">Logout</a>

        <!-- Video Management Section -->
        <div class="video-management">
            <h3>Manage Videos</h3>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>

            <!-- Add Video Form -->
            <form action="add_video.php" method="POST" enctype="multipart/form-data" class="add-video-form" id="addVideoForm">
                <input type="text" name="title" placeholder="Video Title" required>
                <input type="file" name="video_file" accept=".mp4,.avi,.mov" required>
                <input type="number" name="reward" placeholder="Reward ($)" step="0.01" required>
                <button type="submit" id="addVideoButton">Add Video</button>
                <div class="loading" id="loadingIndicator">Uploading video, please wait...</div>
            </form>

            <!-- Video List -->
            <?php if (empty($videos)): ?>
                <p>No videos available.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="video-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>URL</th>
                                <th>Reward ($)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($videos as $video): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($video['id']); ?></td>
                                    <td><?php echo htmlspecialchars($video['title']); ?></td>
                                    <td><?php echo htmlspecialchars($video['url']); ?></td>
                                    <td><?php echo number_format($video['reward'], 2); ?></td>
                                    <td>
                                        <a href="edit_video.php?id=<?php echo $video['id']; ?>">Edit</a>
                                        <a href="delete_video.php?id=<?php echo $video['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this video?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Show loading indicator when form is submitted
        document.getElementById('addVideoForm').addEventListener('submit', function() {
            const button = document.getElementById('addVideoButton');
            const loadingIndicator = document.getElementById('loadingIndicator');
            button.disabled = true;
            button.innerText = 'Uploading...';
            loadingIndicator.style.display = 'block';
        });
    </script>
</body>
</html>
