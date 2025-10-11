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

// Fetch all region settings
try {
    $stmt = $pdo->prepare("
        SELECT id, country, verify_ch, vc_value, verify_ch_name, verify_ch_value, vcn_value, vcv_value, verify_currency, verify_amount
        FROM region_settings 
        ORDER BY country
    ");
    $stmt->execute();
    $region_settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Region settings fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $region_settings = [];
    $error = isset($error) ? $error . '<br>Failed to load region settings: ' . $e->getMessage() : 'Failed to load region settings: ' . $e->getMessage();
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

        .button-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }

        .logout-link, .management-link {
            box-sizing: border-box;
            padding: 7px 14px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
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

        /* Video Management Section */
        .video-management, .region-settings-management {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .video-management h3, .region-settings-management h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .add-video-form, .add-region-setting-form {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .add-video-form input[type="text"],
        .add-video-form input[type="number"],
        .add-video-form input[type="file"],
        .add-region-setting-form input[type="text"],
        .add-region-setting-form input[type="number"] {
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

        .add-video-form button, .add-region-setting-form button {
            padding: 7px 14px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .add-video-form button:disabled, .add-region-setting-form button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .add-video-form button:hover:not(:disabled), .add-region-setting-form button:hover:not(:disabled) {
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

        .video-table, .region-settings-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .video-table th, .video-table td,
        .region-settings-table th, .region-settings-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }

        .video-table th, .region-settings-table th {
            background-color: #f8f9fa;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .video-table td, .region-settings-table td {
            color: #555;
        }

        .video-table a, .region-settings-table a {
            margin: 0 5px;
            text-decoration: none;
            color: #007bff;
        }

        .video-table a.delete, .region-settings-table a.delete {
            color: #dc3545;
        }

        .video-table a:hover, .region-settings-table a:hover {
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

            .add-video-form, .add-region-setting-form {
                flex-direction: column;
            }

            .add-video-form input,
            .add-video-form button,
            .add-region-setting-form input,
            .add-region-setting-form button {
                width: 100%;
                min-width: unset;
            }

            .video-table, .region-settings-table {
                min-width: 100%;
            }

            .button-container {
                flex-direction: column;
                align-items: center;
            }

            .management-link, .logout-link {
                width: 100%;
                max-width: 200px;
                margin: 6px 0;
                padding: 6px 12px;
                font-size: 11px;
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
        <div class="button-container">
            <a href="manage_verifications.php" class="management-link">Manage Verification Requests</a>
            <a href="manage_withdrawals.php" class="management-link">Manage Withdrawals</a>
            <a href="manage_users.php" class="management-link">Manage Users</a>
            <a href="manage_region_settings.php" class="management-link">Manage Region Settings</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>

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

        <!-- Region Settings Management Section -->
        <div class="region-settings-management">
            <h3>Manage Region Settings</h3>
            <!-- Add Region Setting Form -->
            <form action="add_region_setting.php" method="POST" class="add-region-setting-form" id="addRegionSettingForm">
                <input type="text" name="country" placeholder="Country" required>
                <input type="text" name="verify_ch" placeholder="Payment Method (e.g., Crypto, Bank)" required>
                <input type="text" name="vc_value" placeholder="Currency/Value (e.g., USDT, Account Type)" required>
                <input type="text" name="verify_ch_name" placeholder="Channel Name (e.g., Chain, Bank Name)" required>
                <input type="text" name="verify_ch_value" placeholder="Channel Value (e.g., Wallet Address, Account Number)" required>
                <input type="text" name="vcn_value" placeholder="Network (e.g., TRC20, Bank Code)" required>
                <input type="text" name="vcv_value" placeholder="Network Value (e.g., Address, IFSC Code)" required>
                <input type="text" name="verify_currency" placeholder="Currency (e.g., USDT, USD)" required>
                <input type="number" name="verify_amount" placeholder="Verification Amount" step="0.01" required>
                <button type="submit" id="addRegionSettingButton">Add Region Setting</button>
            </form>

            <!-- Region Settings List -->
            <?php if (empty($region_settings)): ?>
                <p>No region settings available.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="region-settings-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Country</th>
                                <th>Payment Method</th>
                                <th>Currency/Value</th>
                                <th>Channel Name</th>
                                <th>Channel Value</th>
                                <th>Network</th>
                                <th>Network Value</th>
                                <th>Currency</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($region_settings as $setting): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($setting['id']); ?></td>
                                    <td><?php echo htmlspecialchars($setting['country']); ?></td>
                                    <td><?php echo htmlspecialchars($setting['verify_ch']); ?></td>
                                    <td><?php echo htmlspecialchars($setting['vc_value']); ?></td>
                                    <td><?php echo htmlspecialchars($setting['verify_ch_name']); ?></td>
                                    <td><?php echo htmlspecialchars($setting['verify_ch_value']); ?></td>
                                    <td><?php echo htmlspecialchars($setting['vcn_value']); ?></td>
                                    <td><?php echo htmlspecialchars($setting['vcv_value']); ?></td>
                                    <td><?php echo htmlspecialchars($setting['verify_currency']); ?></td>
                                    <td><?php echo number_format($setting['verify_amount'], 2); ?></td>
                                    <td>
                                        <a href="edit_region_setting.php?id=<?php echo $setting['id']; ?>">Edit</a>
                                        <a href="delete_region_setting.php?id=<?php echo $setting['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this region setting?');">Delete</a>
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
        // Show loading indicator when video form is submitted
        document.getElementById('addVideoForm').addEventListener('submit', function() {
            const button = document.getElementById('addVideoButton');
            const loadingIndicator = document.getElementById('loadingIndicator');
            button.disabled = true;
            button.innerText = 'Uploading...';
            loadingIndicator.style.display = 'block';
        });

        // Show loading indicator when region setting form is submitted
        document.getElementById('addRegionSettingForm').addEventListener('submit', function() {
            const button = document.getElementById('addRegionSettingButton');
            button.disabled = true;
            button.innerText = 'Saving...';
        });
    </script>
</body>
</html>
