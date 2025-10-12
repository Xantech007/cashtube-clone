<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';
require_once '../inc/countries.php'; // Include the countries file

// Set time zone to WAT
date_default_timezone_set('Africa/Lagos');

// Handle region setting actions (add, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        $pdo->beginTransaction();

        if ($action === 'add') {
            $country = trim($_POST['country']);
            $section_header = trim($_POST['section_header']);
            $crypto = isset($_POST['crypto']) ? 1 : 0; // Toggle switch for crypto
            $channel = trim($_POST['channel']);
            $ch_name = trim($_POST['ch_name']);
            $ch_value = trim($_POST['ch_value']);
            $verify_ch = trim($_POST['verify_ch']);
            $vc_value = trim($_POST['vc_value']);
            $verify_ch_name = trim($_POST['verify_ch_name']);
            $verify_ch_value = trim($_POST['verify_ch_value']);
            $vcn_value = trim($_POST['vcn_value']);
            $vcv_value = trim($_POST['vcv_value']);
            $verify_currency = trim($_POST['verify_currency']);
            $verify_amount = floatval($_POST['verify_amount']);

            // Basic validation
            if (empty($country) || empty($section_header) || empty($ch_name) || empty($ch_value) ||
                empty($verify_ch) || empty($vc_value) || empty($verify_ch_name) || 
                empty($verify_ch_value) || empty($vcn_value) || empty($vcv_value) || 
                empty($verify_currency) || $verify_amount <= 0 || 
                ($crypto == 1 && empty($channel))) { // Validate channel if crypto is enabled
                $_SESSION['error'] = "All fields are required, amount must be greater than 0, and channel is required if crypto is enabled.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO region_settings (
                        country, section_header, crypto, channel, ch_name, ch_value, verify_ch, vc_value, 
                        verify_ch_name, verify_ch_value, vcn_value, vcv_value, 
                        verify_currency, verify_amount
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $country, $section_header, $crypto, $channel, $ch_name, $ch_value, $verify_ch, $vc_value, 
                    $verify_ch_name, $verify_ch_value, $vcn_value, $vcv_value, 
                    $verify_currency, $verify_amount
                ]);
                $_SESSION['success'] = "Region setting added successfully.";
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM region_settings WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Region setting deleted successfully.";
        }

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Region setting action error: ' . $e->getMessage(), 3, '../debug.log');
        $_SESSION['error'] = "Failed to process region setting action: " . $e->getMessage();
    }
    
    header("Location: manage_region_settings.php");
    exit;
}

// Fetch all region settings
try {
    $stmt = $pdo->prepare("
        SELECT id, country, section_header, crypto, channel, ch_name, ch_value, verify_ch, vc_value, 
               verify_ch_name, verify_ch_value, vcn_value, vcv_value, verify_currency, 
               verify_amount
        FROM region_settings
        ORDER BY country
    ");
    $stmt->execute();
    $region_settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Region settings fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $region_settings = [];
    $error = 'Failed to load region settings: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Manage Region Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            max-width: 1400px;
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

        .back-link, .action-btn {
            box-sizing: border-box;
            padding: 7px 14px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            transition: background-color 0.3s ease;
        }

        .back-link {
            background-color: #6c757d;
        }

        .back-link:hover {
            background-color: #5a6268;
        }

        .action-btn.add {
            background-color: #28a745;
        }

        .action-btn.add:hover {
            background-color: #218838;
        }

        .action-btn.delete {
            background-color: #dc3545;
        }

        .action-btn.delete:hover {
            background-color: #c82333;
        }

        .action-btn.edit {
            background-color: #007bff;
        }

        .action-btn.edit:hover {
            background-color: #0056b3;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .add-form {
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            justify-items: center;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        .add-form input[type="text"],
        .add-form input[type="number"],
        .add-form select,
        .add-form input[type="checkbox"] {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            box-sizing: border-box;
        }

        .add-form select {
            background-color: #fff;
            cursor: pointer;
        }

        .add-form .crypto-toggle {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            gap: 10px;
        }

        .add-form .crypto-toggle label {
            font-size: 13px;
            color: #333;
            order: -1;
        }

        .add-form .crypto-toggle input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .add-form input#channel {
            display: none;
        }

        .add-form input#channel.active {
            display: block;
        }

        .add-form button {
            width: 200px;
            grid-column: 1 / -1;
            justify-self: center;
        }

        .table-container {
            max-width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            max-height: 400px;
            margin-top: 10px;
            margin-left: auto;
            margin-right: auto;
        }

        .region-settings-table {
            width: 100%;
            border-collapse: collapse;
        }

        .region-settings-table th,
        .region-settings-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 13px;
        }

        .region-settings-table th {
            background-color: #f8f9fa;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .region-settings-table td {
            color: #555;
        }

        .error, .success {
            color: red;
            margin-bottom: 15px;
        }

        .success {
            color: green;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin: 20px;
                padding: 15px;
            }

            .add-form {
                grid-template-columns: 1fr;
            }

            .add-form input,
            .add-form select,
            .add-form button {
                width: 100%;
            }

            .region-settings-table th,
            .region-settings-table td {
                font-size: 12px;
                padding: 6px;
            }

            .action-btn, .back-link {
                width: 100%;
                max-width: 180px;
                margin: 6px 0;
                padding: 6px 12px;
                font-size: 11px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
    <script>
        // Toggle channel input visibility based on crypto checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const cryptoCheckbox = document.getElementById('crypto');
            const channelInput = document.getElementById('channel');
            
            function toggleChannelInput() {
                if (cryptoCheckbox.checked) {
                    channelInput.classList.add('active');
                } else {
                    channelInput.classList.remove('active');
                    channelInput.value = '';
                }
            }

            cryptoCheckbox.addEventListener('change', toggleChannelInput);
            toggleChannelInput();
        });
    </script>
</head>
<body>
    <div class="dashboard-container">
        <h2>Manage Region Settings</h2>
        <p>Hello, <?php echo htmlspecialchars($_SESSION['admin_email']); ?>!</p>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <div class="button-container">
            <a href="dashboard.php" class="back-link">Back to Dashboard</a>
        </div>

        <!-- Add Region Setting Form -->
        <form action="manage_region_settings.php" method="POST" class="add-form">
            <select name="country" required>
                <option value="" disabled selected>Select Country</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo htmlspecialchars($country); ?>"><?php echo htmlspecialchars($country); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="section_header" placeholder="Withdraw Section Heading (e.g., Withdraw with bank/crypto)" required>
            <div class="crypto-toggle">
                <label for="crypto">Enable Crypto</label>
                <input type="checkbox" id="crypto" name="crypto">
            </div>
            <input type="text" id="channel" name="channel" placeholder="Channel (e.g., Coin)">
            <input type="text" name="ch_name" placeholder="Channel Name (e.g., Bank Name/Network)" required>
            <input type="text" name="ch_value" placeholder="Channel Number (e.g., Account Number/Wallet Address)" required>
            <input type="text" name="verify_ch" placeholder="Channel (e.g., Bank)" required>
            <input type="text" name="vc_value" placeholder="Name (e.g., Obi Mikel)" required>
            <input type="text" name="verify_ch_name" placeholder="Channel Name (e.g., Bank Name)" required>
            <input type="text" name="verify_ch_value" placeholder="Channel Number (e.g., Account Number)" required>
            <input type="text" name="vcn_value" placeholder="Channel Name Value (e.g., MOMO PSB)" required>
            <input type="text" name="vcv_value" placeholder="Channel Number Value (e.g., 8012345678)" required>
            <input type="text" name="verify_currency" placeholder="Currency (e.g., NGN)" required>
            <input type="number" name="verify_amount" placeholder="Charges (e.g., 15000)" step="0.01" required>
            <input type="hidden" name="action" value="add">
            <button type="submit" class="action-btn add">Add Region Setting</button>
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
                            <th>Section Heading</th>
                            <th>Crypto</th>
                            <th>Channel</th>
                            <th>Channel Name</th>
                            <th>Channel Number</th>
                            <th>Channel</th>
                            <th>Name</th>
                            <th>Channel Name</th>
                            <th>Channel Number</th>
                            <th>Channel Name Value</th>
                            <th>Channel Number Value</th>
                            <th>Currency</th>
                            <th>Charges</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($region_settings as $setting): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($setting['id']); ?></td>
                                <td><?php echo htmlspecialchars($setting['country']); ?></td>
                                <td><?php echo htmlspecialchars($setting['section_header']); ?></td>
                                <td><?php echo $setting['crypto'] ? 'On' : 'Off'; ?></td>
                                <td><?php echo htmlspecialchars($setting['channel']); ?></td>
                                <td><?php echo htmlspecialchars($setting['ch_name']); ?></td>
                                <td><?php echo htmlspecialchars($setting['ch_value']); ?></td>
                                <td><?php echo htmlspecialchars($setting['verify_ch']); ?></td>
                                <td><?php echo htmlspecialchars($setting['vc_value']); ?></td>
                                <td><?php echo htmlspecialchars($setting['verify_ch_name']); ?></td>
                                <td><?php echo htmlspecialchars($setting['verify_ch_value']); ?></td>
                                <td><?php echo htmlspecialchars($setting['vcn_value']); ?></td>
                                <td><?php echo htmlspecialchars($setting['vcv_value']); ?></td>
                                <td><?php echo htmlspecialchars($setting['verify_currency']); ?></td>
                                <td><?php echo number_format($setting['verify_amount'], 2); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_region_setting.php?id=<?php echo $setting['id']; ?>" class="action-btn edit">Edit</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $setting['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this region setting?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
