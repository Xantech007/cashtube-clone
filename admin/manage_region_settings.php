<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';
require_once '../inc/countries.php';

date_default_timezone_set('Africa/Lagos');

// === IMAGE UPLOAD DIRECTORY ===
$upload_dir = '../images/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle region setting actions + image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        $pdo->beginTransaction();

        // === IMAGE HANDLING ===
        $image_file = $_FILES['image'] ?? null;
        $current_image = $_POST['current_image'] ?? '';
        $new_image_name = $current_image; // Default: keep existing

        if ($image_file && $image_file['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($image_file['type'], $allowed) || $image_file['size'] > $max_size) {
                throw new Exception("Invalid image. Only JPG/PNG allowed, max 5MB.");
            }

            $ext = pathinfo($image_file['name'], PATHINFO_EXTENSION);
            $new_image_name = 'payment_' . strtolower(str_replace(' ', '_', $_POST['country'])) . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_image_name;

            if (!move_uploaded_file($image_file['tmp_name'], $upload_path)) {
                throw new Exception("Failed to upload image.");
            }

            // Delete old image if exists and not default
            if ($current_image && file_exists($upload_dir . $current_image) && $current_image !== $new_image_name) {
                @unlink($upload_dir . $current_image);
            }
        } elseif ($image_file && $image_file['error'] !== UPLOAD_ERR_NO_FILE) {
            throw new Exception("Image upload error.");
        }

        if ($action === 'add_dashboard') {
            $country = trim($_POST['country']);
            $section_header = trim($_POST['section_header']);
            $channel = trim($_POST['channel']);
            $ch_name = trim($_POST['ch_name']);
            $ch_value = trim($_POST['ch_value']);
            $withdraw_currency = trim($_POST['withdraw_currency']);

            if (empty($country) || empty($section_header) || empty($channel) || empty($ch_name) || empty($ch_value) || empty($withdraw_currency)) {
                $_SESSION['error'] = "All Dashboard fields are required.";
            } else {
                $stmt = $pdo->prepare("SELECT id FROM region_settings WHERE country = ?");
                $stmt->execute([$country]);
                if ($stmt->fetch()) {
                    $_SESSION['error'] = "Region settings for this country already exist.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO region_settings 
                        (country, section_header, channel, ch_name, ch_value, withdraw_currency, images)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$country, $section_header, $channel, $ch_name, $ch_value, $withdraw_currency, $new_image_name]);
                    $_SESSION['success'] = "Dashboard settings added successfully.";
                }
            }
        } 
        elseif ($action === 'add_verification') {
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
                $_SESSION['error'] = "All Verification fields except Medium are required. Amount & Rate > 0.";
            } else {
                $stmt = $pdo->prepare("
                    UPDATE region_settings 
                    SET verify_ch = ?, vc_value = ?, verify_ch_name = ?, verify_ch_value = ?, 
                        verify_medium = ?, vcn_value = ?, vcv_value = ?, verify_currency = ?, 
                        verify_amount = ?, rate = ?, account_upgrade = ?, images = ?
                    WHERE country = ?
                ");
                $result = $stmt->execute([
                    $verify_ch, $vc_value, $verify_ch_name, $verify_ch_value, $verify_medium, 
                    $vcn_value, $vcv_value, $verify_currency, $verify_amount, $rate, $account_upgrade, 
                    $new_image_name, $country
                ]);
                if ($stmt->rowCount() === 0) {
                    $_SESSION['error'] = "Add Dashboard settings first for this country.";
                } else {
                    $_SESSION['success'] = "Verification/Upgrade settings updated.";
                }
            }
        } 
        elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            // Get image to delete
            $stmt = $pdo->prepare("SELECT images FROM region_settings WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && $row['images'] && file_exists($upload_dir . $row['images'])) {
                @unlink($upload_dir . $row['images']);
            }
            $stmt = $pdo->prepare("DELETE FROM region_settings WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Region setting deleted.";
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Admin error: ' . $e->getMessage(), 3, '../debug.log');
        $_SESSION['error'] = $e->getMessage();
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
    error_log('Fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $region_settings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Manage Region Settings</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f4; }
        .dashboard-container { max-width: 1400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        .dashboard-container h2 { color: #333; margin-bottom: 20px; }
        .dashboard-container h3 { color: #333; margin: 30px 0 15px; font-size: 18px; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        .button-container { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; margin: 20px 0; }
        .back-link, .action-btn { padding: 7px 14px; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; transition: 0.3s; }
        .back-link { background: #6c757d; }
        .back-link:hover { background: #5a6268; }
        .action-btn.add { background: #28a745; }
        .action-btn.add:hover { background: #218838; }
        .action-btn.delete { background: #dc3545; }
        .action-btn.delete:hover { background: #c82333; }
        .action-btn.edit { background: #007bff; }
        .action-btn.edit:hover { background: #0056b3; }
        .action-buttons { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
        .add-form { margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; justify-items: center; max-width: 100%; margin-left: auto; margin-right: auto; }
        .add-form input, .add-form select, .add-form button { width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; box-sizing: border-box; }
        .add-form select { background: #fff; cursor: pointer; }
        .add-form button { width: 200px; grid-column: 1 / -1; justify-self: center; }
        .add-form label.checkbox-label { display: flex; align-items: center; font-size: 13px; color: #555; justify-self: start; }
        .add-form input[type="checkbox"] { width: auto; margin-right: 8px; }
        .image-preview { margin-top: 8px; text-align: center; }
        .image-preview img { max-width: 180px; max-height: 120px; border-radius: 6px; border: 1px solid #ddd; }
        .table-container { max-width: 100%; overflow-x: auto; max-height: 500px; margin-top: 10px; }
        .region-settings-table { width: 100%; border-collapse: collapse; }
        .region-settings-table th, .region-settings-table td { padding: 8px; border: 1px solid #ddd; text-align: left; font-size: 13px; }
        .region-settings-table th { background: #f8f9fa; color: #333; position: sticky; top: 0; z-index: 1; }
        .region-settings-table td { color: #555; }
        .region-settings-table img { max-width: 80px; max-height: 50px; border-radius: 4px; }
        .error, .success { color: red; margin-bottom: 15px; }
        .success { color: green; }

        @media (max-width: 768px) {
            .dashboard-container { margin: 20px; padding: 15px; }
            .add-form { grid-template-columns: 1fr; }
            .add-form button { width: 100%; }
            .image-preview img { max-width: 150px; }
            .region-settings-table th, .region-settings-table td { font-size: 12px; padding: 6px; }
            .action-btn, .back-link { width: 100%; max-width: 180px; margin: 6px 0; font-size: 11px; }
            .action-buttons { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Manage Region Settings</h2>
        <p>Hello, <?php echo htmlspecialchars($_SESSION['admin_email']); ?>!</p>

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
        <form action="" method="POST" class="add-form" enctype="multipart/form-data">
            <select name="country" required>
                <option value="" disabled selected>Select Country</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="section_header" placeholder="Section Heading" required>
            <input type="text" name="channel" placeholder="Channel (e.g., Bank)" required>
            <input type="text" name="ch_name" placeholder="Channel Name" required>
            <input type="text" name="ch_value" placeholder="Channel Number" required>
            <input type="text" name="withdraw_currency" placeholder="Currency (e.g., NGN)" required>
            <div style="grid-column: 1 / -1;">
                <label>Payment Image (optional):</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png">
                <p style="font-size:11px; color:#777; margin:4px 0;">JPG/PNG, max 5MB</p>
            </div>
            <input type="hidden" name="action" value="add_dashboard">
            <button type="submit" class="action-btn add">Add Dashboard Settings</button>
        </form>

        <!-- Dashboard Table -->
        <?php if (!empty($region_settings)): ?>
            <div class="table-container">
                <table class="region-settings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Country</th>
                            <th>Heading</th>
                            <th>Channel</th>
                            <th>Name</th>
                            <th>Number</th>
                            <th>Currency</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($region_settings as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td><?php echo htmlspecialchars($s['country']); ?></td>
                                <td><?php echo htmlspecialchars($s['section_header']); ?></td>
                                <td><?php echo htmlspecialchars($s['channel']); ?></td>
                                <td><?php echo htmlspecialchars($s['ch_name']); ?></td>
                                <td><?php echo htmlspecialchars($s['ch_value']); ?></td>
                                <td><?php echo htmlspecialchars($s['withdraw_currency']); ?></td>
                                <td>
                                    <?php if ($s['images'] && file_exists("../images/".$s['images'])): ?>
                                        <img src="../images/<?php echo $s['images']; ?>" alt="Payment">
                                    <?php else: ?>
                                        <span style="color:#999; font-size:11px;">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="edit_region_setting.php?id=<?php echo $s['id']; ?>&section=dashboard" class="action-btn edit">Edit</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="action-btn delete" onclick="return confirm('Delete all settings for this country?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Verification/Upgrade Section -->
        <h3>Verification / Account Upgrade</h3>
>
        <form action="" method="POST" class="add-form" enctype="multipart/form-data">
            <select name="country" required>
                <option value="" disabled selected>Select Country (must have Dashboard)</option>
                <?php foreach ($region_settings as $s): ?>
                    <option value="<?php echo htmlspecialchars($s['country']); ?>"><?php echo htmlspecialchars($s['country']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="verify_ch" placeholder="Channel (e.g., Bank)" required>
            <input type="text" name="vc_value" placeholder="Name (e.g., Obi Mikel)" required>
            <input type="text" name="verify_ch_name" placeholder="Channel Name" required>
            <input type="text" name="verify_ch_value" placeholder="Channel Number" required>
            <input type="text" name="verify_medium" placeholder="Verify Medium (optional)">
            <input type="text" name="vcn_value" placeholder="Channel Name Value" required>
            <input type="text" name="vcv_value" placeholder="Channel Number Value" required>
            <input type="text" name="verify_currency" placeholder="Currency" required>
            <input type="number" name="verify_amount" placeholder="Charges" step="0.01" required>
            <input type="number" name="rate" placeholder="Rate (e.g., 1000)" step="0.01" required>
            <label class="checkbox-label">
                <input type="checkbox" name="account_upgrade" value="1"> Upgrade (checked) / Verification (unchecked)
            </label>

            <!-- Current Image Preview -->
            <?php 
            $current_country = $_POST['country'] ?? ($region_settings[0]['country'] ?? '');
            $current_img = '';
            foreach ($region_settings as $s) {
                if ($s['country'] === $current_country) { $current_img = $s['images']; break; }
            }
            ?>
            <div style="grid-column: 1 / -1;">
                <label>Update Payment Image:</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($current_img); ?>">
                <?php if ($current_img && file_exists("../images/".$current_img)): ?>
                    <div class="image-preview">
                        <p><strong>Current:</strong></p>
                        <img src="../images/<?php echo $current_img; ?>" alt="Current">
                        <p style="font-size:11px; color:#d33;">Upload new to replace</p>
                    </div>
                <?php endif; ?>
            </div>

            <input type="hidden" name="action" value="add_verification">
            <button type="submit" class="action-btn add">Update Verification/Upgrade</button>
        </form>

        <!-- Verification Table -->
        <?php if (!empty($region_settings)): ?>
            <div class="table-container">
                <table class="region-settings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Country</th>
                            <th>Type</th>
                            <th>Channel</th>
                            <th>Name</th>
                            <th>Ch. Name</th>
                            <th>Ch. Num</th>
                            <th>Medium</th>
                            <th>Ch. Name Val</th>
                            <th>Ch. Num Val</th>
                            <th>Currency</th>
                            <th>Charges</th>
                            <th>Rate</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($region_settings as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td><?php echo htmlspecialchars($s['country']); ?></td>
                                <td><?php echo $s['account_upgrade'] ? 'Upgrade' : 'Verification'; ?></td>
                                <td><?php echo htmlspecialchars($s['verify_ch'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($s['vc_value'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($s['verify_ch_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($s['verify_ch_value'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($s['verify_medium'] ?? 'Payment Method'); ?></td>
                                <td><?php echo htmlspecialchars($s['vcn_value'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($s['vcv_value'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($s['verify_currency'] ?? 'N/A'); ?></td>
                                <td><?php echo isset($s['verify_amount']) ? number_format($s['verify_amount'], 2) : 'N/A'; ?></td>
                                <td><?php echo isset($s['rate']) ? number_format($s['rate'], 2) : 'N/A'; ?></td>
                                <td>
                                    <?php if ($s['images'] && file_exists("../images/".$s['images'])): ?>
                                        <img src="../images/<?php echo $s['images']; ?>" alt="Img">
                                    <?php else: ?>
                                        <span style="color:#999; font-size:11px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="edit_region_setting.php?id=<?php echo $s['id']; ?>&section=verification" class="action-btn edit">Edit</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="action-btn delete" onclick="return confirm('Delete ALL settings for this country?');">Delete</button>
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
