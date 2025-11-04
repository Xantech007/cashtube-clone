<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';
require_once '../inc/countries.php';

// Set time zone to WAT
date_default_timezone_set('Africa/Lagos');

// Define upload directory
$uploadDir = '../images/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Handle region setting actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        $pdo->beginTransaction();

        $imageName = null;

        // Only handle image for verification section
        if ($action === 'add_verification' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $fileType = mime_content_type($file['tmp_name']);
            $fileSize = $file['size'];

            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.");
            }
            if ($fileSize > $maxFileSize) {
                throw new Exception("File is too large. Maximum size is 5MB.");
            }

            $imageName = uniqid('img_', true) . '_' . basename($file['name']);
            $destination = $uploadDir . $imageName;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception("Failed to upload image.");
            }
        }

        if ($action === 'add_dashboard') {
            $country = trim($_POST['country']);
            $section_header = trim($_POST['section_header']);
            $channel = trim($_POST['channel']);
            $ch_name = trim($_POST['ch_name']);
            $ch_value = trim($_POST['ch_value']);
            $withdraw_currency = trim($_POST['withdraw_currency']);

            if (empty($country) || empty($section_header) || empty($channel) || empty($ch_name) || empty($ch_value) || empty($withdraw_currency)) {
                $_SESSION['error'] = "All Dashboard fields, including Currency, are required.";
            } else {
                $stmt = $pdo->prepare("SELECT id FROM region_settings WHERE country = ?");
                $stmt->execute([$country]);
                if ($stmt->fetch()) {
                    $_SESSION['error'] = "Region settings for this country already exist.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO region_settings 
                        (country, section_header, channel, ch_name, ch_value, withdraw_currency)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$country, $section_header, $channel, $ch_name, $ch_value, $withdraw_currency]);
                    $_SESSION['success'] = "Dashboard settings added successfully.";
                }
            }
        } elseif ($action === 'add_verification') {
            $country = trim($_POST['country']);
            $verify_ch = trim($_POST['verify_ch']);
            $vc_value = trim($_POST['vc_value']);
            $verify_ch_name = trim($_POST['verify_ch_name']);
            $verify_ch_value = trim($_POST['verify_ch_value']);
            $verify_medium = trim($_POST['verify_medium']);
            $vcn_value = trim($_POST['vcn_value']);
            $vcv_value = trim($_POST['vcv_value']);
            $verify_currency = trim($_POST['verify_currency']);
            $verify_amount = floatval($_POST['verify_amount']);
            $rate = floatval($_POST['rate']);
            $account_upgrade = isset($_POST['account_upgrade']) ? 1 : 0;

            if (empty($country) || empty($verify_ch) || empty($vc_value) || empty($verify_ch_name) || 
                empty($verify_ch_value) || empty($vcn_value) || empty($vcv_value) || 
                empty($verify_currency) || $verify_amount <= 0 || $rate <= 0) {
                $_SESSION['error'] = "All Verification fields except Verify Medium are required, Amount and Rate must be greater than 0.";
            } else {
                $stmt = $pdo->prepare("
                    UPDATE region_settings 
                    SET verify_ch = ?, vc_value = ?, verify_ch_name = ?, verify_ch_value = ?, 
                        verify_medium = ?, vcn_value = ?, vcv_value = ?, verify_currency = ?, 
                        verify_amount = ?, rate = ?, account_upgrade = ?, images = COALESCE(?, images)
                    WHERE country = ?
                ");
                $result = $stmt->execute([
                    $verify_ch, $vc_value, $verify_ch_name, $verify_ch_value, $verify_medium, 
                    $vcn_value, $vcv_value, $verify_currency, $verify_amount, $rate, $account_upgrade,
                    $imageName, $country
                ]);
                if ($stmt->rowCount() === 0) {
                    $_SESSION['error'] = "No Dashboard settings found for this country. Add Dashboard settings first.";
                } else {
                    $_SESSION['success'] = "Verification settings updated successfully.";
                }
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("SELECT images FROM region_settings WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && !empty($row['images'])) {
                $imagePath = $uploadDir . $row['images'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $stmt = $pdo->prepare("DELETE FROM region_settings WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Region setting deleted successfully.";
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        if ($imageName && file_exists($uploadDir . $imageName)) {
            unlink($uploadDir . $imageName);
        }
        error_log('Region setting action error: ' . $e->getMessage(), 3, '../debug.log');
        $_SESSION['error'] = "Failed to process region setting action: " . $e->getMessage();
    }
    
    header("Location: manage_region_settings.php");
    exit;
}

// Fetch all region settings
try {
    $stmt = $pdo->prepare("
        SELECT id, country, section_header, channel, ch_name, ch_value, withdraw_currency, 
               verify_ch, vc_value, verify_ch_name, verify_ch_value, verify_medium, vcn_value, 
               vcv_value, verify_currency, verify_amount, rate, account_upgrade, images
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
        /* [Same CSS as before - unchanged] */
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f4; }
        .dashboard-container { max-width: 1400px; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        .dashboard-container h2 { color: #333; margin-bottom: 20px; }
        .dashboard-container h3 { color: #333; margin: 30px 0 15px; font-size: 18px; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        .button-container { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; margin: 20px 0; }
        .back-link, .action-btn { padding: 7px 14px; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; transition: background-color 0.3s ease; }
        .back-link { background-color: #6c757d; }
        .back-link:hover { background-color: #5a6268; }
        .action-btn.add { background-color: #28a745; }
        .action-btn.add:hover { background-color: #218838; }
        .action-btn.delete { background-color: #dc3545; }
        .action-btn.delete:hover { background-color: #c82333; }
        .action-btn.edit { background-color: #007bff; }
        .action-btn.edit:hover { background-color: #0056b3; }
        .action-buttons { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
        .add-form { margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; justify-items: center; max-width: 100%; margin-left: auto; margin-right: auto; }
        .add-form input[type="text"], .add-form input[type="number"], .add-form input[type="file"], .add-form select, .add-form input[type="checkbox"] { width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; box-sizing: border-box; }
        .add-form input[type="file"] { padding: 3px; }
        .add-form select { background-color: #fff; cursor: pointer; }
        .add-form label.checkbox-label { display: flex; align-items: center; font-size: 13px; color: #555; justify-self: start; }
        .add-form input[type="checkbox"] { width: auto; margin-right: 8px; }
        .add-form button { width: 200px; grid-column: 1 / -1; justify-self: center; }
        .table-container { max-width: 100%; overflow-x: auto; max-height: 400px; margin-top: 10px; margin-left: auto; margin-right: auto; }
        .region-settings-table { width: 100%; border-collapse: collapse; }
        .region-settings-table th, .region-settings-table td { padding: 8px; border: 1px solid #ddd; text-align: left; font-size: 13px; }
        .region-settings-table th { background-color: #f8f9fa; color: #333; position: sticky; top: 0; z-index: 1; }
        .region-settings-table td { color: #555; }
        .image-preview { max-width: 60px; max-height: 60px; object-fit: cover; border-radius: 4px; }
        .error, .success { color: red; margin-bottom: 15px; }
        .success { color: green; }
        @media (max-width: 768px) {
            .dashboard-container { margin: 20px; padding: 15px; }
            .add-form { grid-template-columns: 1fr; }
            .region-settings-table th, .region-settings-table td { font-size: 12px; padding: 6px; }
            .action-btn, .back-link { width: 100%; max-width: 180px; margin: 6px 0; padding: 6px 12px; font-size: 11px; }
            .action-buttons { flex-direction: column; align-items: center; }
            .image-preview { max-width: 40px; max-height: 40px; }
        }
    </style>
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

        <!-- Dashboard Section -->
        <h3>Dashboard Section</h3>
        <form action="manage_region_settings.php" method="POST" class="add-form">
            <select name="country" required>
                <option value="" disabled selected>Select Country</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo htmlspecialchars($country); ?>"><?php echo htmlspecialchars($country); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="section_header" placeholder="Section Heading (e.g., Withdraw with bank)" required>
            <input type="text" name="channel" placeholder="Channel (e.g., Bank)" required>
            <input type="text" name="ch_name" placeholder="Channel Name (e.g., Bank Name)" required>
            <input type="text" name="ch_value" placeholder="Channel Number (e.g., Account Number)" required>
            <input type="text" name="withdraw_currency" placeholder="Currency (e.g., NGN)" required>
            <input type="hidden" name="action" value="add_dashboard">
            <button type="submit" class="action-btn add">Add Dashboard Settings</button>
        </form>

        <!-- Dashboard Table -->
        <?php if (empty($region_settings)): ?>
            <p>No Dashboard settings available.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="region-settings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Country</th>
                            <th>Section Heading</th>
                            <th>Channel</th>
                            <th>Channel Name</th>
                            <th>Channel Number</th>
                            <th>Currency</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($region_settings as $setting): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($setting['id']); ?></td>
                                <td><?php echo htmlspecialchars($setting['country']); ?></td>
                                <td><?php echo htmlspecialchars($setting['section_header']); ?></td>
                                <td><?php echo htmlspecialchars($setting['channel']); ?></td>
                                <td><?php echo htmlspecialchars($setting['ch_name']); ?></td>
                                <td><?php echo htmlspecialchars($setting['ch_value']); ?></td>
                                <td><?php echo htmlspecialchars($setting['withdraw_currency'] ?? 'N/A'); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_region_setting.php?id=<?php echo $setting['id']; ?>&section=dashboard" class="action-btn edit">Edit</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $setting['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="action-btn delete" onclick="return confirm('Are you sure? This will delete all settings and image.');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Verification Section -->
        <h3>Verification/Upgrade Section</h3>
        <form action="manage_region_settings.php" method="POST" class="add-form" enctype="multipart/form-data">
            <select name="country" required>
                <option value="" disabled selected>Select Country</option>
                <?php foreach ($region_settings as $setting): ?>
                    <option value="<?php echo htmlspecialchars($setting['country']); ?>"><?php echo htmlspecialchars($setting['country']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="verify_ch" placeholder="Channel (e.g., Bank)" required>
            <input type="text" name="vc_value" placeholder="Name (e.g., Obi Mikel)" required>
            <input type="text" name="verify_ch_name" placeholder="Channel Name (e.g., Bank Name)" required>
            <input type="text" name="verify_ch_value" placeholder="Channel Number (e.g., Account Number)" required>
            <input type="text" name="verify_medium" placeholder="Verify Medium (e.g., Payment Method)">
            <input type="text" name="vcn_value" placeholder="Channel Name Value (e.g., MOMO PSB)" required>
            <input type="text" name="vcv_value" placeholder="Channel Number Value (e.g., 8012345678)" required>
            <input type="text" name="verify_currency" placeholder="Currency (e.g., NGN)" required>
            <input type="number" name="verify_amount" placeholder="Charges (e.g., 15000)" step="0.01" required>
            <input type="number" name="rate" placeholder="Conversion Rate (e.g., 1000)" step="0.01" required>
            <label class="checkbox-label">
                <input type="checkbox" name="account_upgrade" value="1"> Upgrade (checked) / Verification (unchecked)
            </label>
            <input type="file" name="image" accept="image/*">
            <input type="hidden" name="action" value="add_verification">
            <button type="submit" class="action-btn add">Add Verification/Upgrade Settings</button>
        </form>

        <!-- Verification Table -->
        <?php if (empty($region_settings)): ?>
            <p>No Verification/Upgrade settings available.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="region-settings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Country</th>
                            <th>Type</th>
                            <th>Channel</th>
                            <th>Name</th>
                            <th>Channel Name</th>
                            <th>Channel Number</th>
                            <th>Verify Medium</th>
                            <th>Channel Name Value</th>
                            <th>Channel Number Value</th>
                            <th>Currency</th>
                            <th>Charges</th>
                            <th>Rate</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($region_settings as $setting): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($setting['id']); ?></td>
                                <td><?php echo htmlspecialchars($setting['country']); ?></td>
                                <td><?php echo $setting['account_upgrade'] == 1 ? 'Upgrade' : 'Verification'; ?></td>
                                <td><?php echo htmlspecialchars($setting['verify_ch'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($setting['vc_value'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($setting['verify_ch_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($setting['verify_ch_value'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($setting['verify_medium'] ?? 'Payment Method'); ?></td>
                                <td><?php echo htmlspecialchars($setting['vcn_value'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($setting['vcv_value'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($setting['verify_currency'] ?? 'N/A'); ?></td>
                                <td><?php echo isset($setting['verify_amount']) ? number_format($setting['verify_amount'], 2) : 'N/A'; ?></td>
                                <td><?php echo isset($setting['rate']) ? number_format($setting['rate'], 2) : 'N/A'; ?></td>
                                <td>
                                    <?php if (!empty($setting['images'])): ?>
                                        <img src="../images/<?php echo htmlspecialchars($setting['images']); ?>" alt="Image" class="image-preview">
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="edit_region_setting.php?id=<?php echo $setting['id']; ?>&section=verification" class="action-btn edit">Edit</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $setting['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="action-btn delete" onclick="return confirm('Delete all settings and image?');">Delete</button>
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
