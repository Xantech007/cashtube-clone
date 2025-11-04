<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';
require_once '../inc/countries.php';

date_default_timezone_set('Africa/Lagos');

$uploadDir = '../images/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        $pdo->beginTransaction();
        $imageName = null;

        if ($action === 'add_verification' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $fileType = mime_content_type($file['tmp_name']);
            $fileSize = $file['size'];

            if (!in_array($fileType, $allowedTypes)) throw new Exception("Invalid file type.");
            if ($fileSize > $maxFileSize) throw new Exception("File too large.");

            $imageName = uniqid('img_', true) . '_' . basename($file['name']);
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageName)) {
                throw new Exception("Upload failed.");
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
                $_SESSION['error'] = "All Dashboard fields are required.";
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
                    $_SESSION['success'] = "Dashboard settings added.";
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
                $_SESSION['error'] = "All required fields must be filled.";
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
                    $_SESSION['error'] = "Add Dashboard settings first.";
                } else {
                    $_SESSION['success'] = "Verification settings updated.";
                }
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("SELECT images FROM region_settings WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && !empty($row['images']) && file_exists($uploadDir . $row['images'])) {
                unlink($uploadDir . $row['images']);
            }
            $stmt = $pdo->prepare("DELETE FROM region_settings WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Region setting deleted.";
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        if ($imageName && file_exists($uploadDir . $imageName)) unlink($uploadDir . $imageName);
        error_log('Error: ' . $e->getMessage(), 3, '../debug.log');
        $_SESSION['error'] = "Action failed: " . $e->getMessage();
    }
    
    header("Location: manage_region_settings.php");
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, country, section_header, channel, ch_name, ch_value, withdraw_currency, 
               verify_ch, vc_value, verify_ch_name, verify_ch_value, verify_medium, vcn_value, 
               vcv_value, verify_currency, verify_amount, rate, account_upgrade, images
        FROM region_settings ORDER BY country
    ");
    $stmt->execute();
    $region_settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $region_settings = [];
    $error = 'Failed to load settings.';
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
        .dashboard-container { max-width: 1400px; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        .dashboard-container h2 { color: #333; margin-bottom: 20px; }
        .dashboard-container h3 { color: #333; margin: 30px 0 15px; font-size: 18px; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        .button-container { display: flex; justify-content: center; gap: 10px; margin: 20px 0; flex-wrap: wrap; }
        .back-link, .action-btn { padding: 6px 10px; color: #fff; text-decoration: none; border-radius: 4px; font-size: 11px; transition: 0.3s; min-width: 60px; }
        .back-link { background-color: #6c757d; }
        .back-link:hover { background-color: #5a6268; }
        .action-btn.add { background-color: #28a745; }
        .action-btn.add:hover { background-color: #218838; }
        .action-btn.delete { background-color: #dc3545; }
        .action-btn.delete:hover { background-color: #c82333; }
        .action-btn.edit { background-color: #007bff; }
        .action-btn.edit:hover { background-color: #0056b3; }
        .action-buttons { display: flex; gap: 4px; justify-content: center; flex-wrap: wrap; }
        .action-buttons .action-btn { padding: 4px 8px; font-size: 10px; min-width: 50px; }
        .add-form { margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; max-width: 100%; margin-left: auto; margin-right: auto; }
        .add-form input, .add-form select, .add-form button { padding: 6px; font-size: 12px; }
        .add-form input[type="file"] { padding: 3px; }
        .add-form button { width: 180px; grid-column: 1 / -1; justify-self: center; }
        .table-container { overflow-x: auto; max-height: 400px; margin-top: 10px; }
        .region-settings-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .region-settings-table th, .region-settings-table td { padding: 6px; border: 1px solid #ddd; text-align: left; }
        .region-settings-table th { background-color: #f8f9fa; position: sticky; top: 0; z-index: 1; font-size: 11px; }
        .image-preview { max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px; }
        .error, .success { margin: 10px 0; font-weight: bold; }
        .success { color: green; }
        .error { color: red; }

        @media (max-width: 768px) {
            .dashboard-container { margin: 15px; padding: 15px; }
            .add-form { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; align-items: center; }
            .action-btn { width: 100%; max-width: 120px; margin: 2px 0; font-size: 10px; }
            .region-settings-table { font-size: 11px; }
            .region-settings-table th, .region-settings-table td { padding: 4px; }
            .image-preview { max-width: 35px; max-height: 35px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Manage Region Settings</h2>
        <p>Hello, <?php echo htmlspecialchars($_SESSION['admin_email']); ?>!</p>

        <?php if (isset($error)): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?><p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p><?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?><p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p><?php endif; ?>

        <div class="button-container">
            <a href="dashboard.php" class="back-link">Back</a>
        </div>

        <!-- Dashboard Section -->
        <h3>Dashboard Section</h3>
        <form action="" method="POST" class="add-form">
            <select name="country" required><option value="" disabled selected>Country</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="section_header" placeholder="Heading" required>
            <input type="text" name="channel" placeholder="Channel" required>
            <input type="text" name="ch_name" placeholder="Name" required>
            <input type="text" name="ch_value" placeholder="Number" required>
            <input type="text" name="withdraw_currency" placeholder="Currency" required>
            <input type="hidden" name="action" value="add_dashboard">
            <button type="submit" class="action-btn add">Add</button>
        </form>

        <?php if (empty($region_settings)): ?>
            <p>No Dashboard settings.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="region-settings-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Country</th><th>Heading</th><th>Channel</th><th>Name</th><th>Number</th><th>Currency</th><th>Actions</th>
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
                                <td><?php echo htmlspecialchars($s['withdraw_currency'] ?? ''); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_region_setting.php?id=<?php echo $s['id']; ?>&section=dashboard" class="action-btn edit" title="Edit">Edit</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="action-btn delete" title="Delete" onclick="return confirm('Delete all?');">Delete</button>
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
        <form action="" method="POST" class="add-form" enctype="multipart/form-data">
            <select name="country" required><option value="" disabled selected>Country</option>
                <?php foreach ($region_settings as $s): ?>
                    <option value="<?php echo htmlspecialchars($s['country']); ?>"><?php echo htmlspecialchars($s['country']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="verify_ch" placeholder="Channel" required>
            <input type="text" name="vc_value" placeholder="Name" required>
            <input type="text" name="verify_ch_name" placeholder="Bank Name" required>
            <input type="text" name="verify_ch_value" placeholder="Account No" required>
            <input type="text" name="verify_medium" placeholder="Medium">
            <input type="text" name="vcn_value" placeholder="MOMO PSB" required>
            <input type="text" name="vcv_value" placeholder="8012345678" required>
            <input type="text" name="verify_currency" placeholder="NGN" required>
            <input type="number" name="verify_amount" placeholder="15000" step="0.01" required>
            <input type="number" name="rate" placeholder="1000" step="0.01" required>
            <label style="grid-column:1/-1; justify-self:start; font-size:11px;">
                <input type="checkbox" name="account_upgrade" value="1"> Upgrade
            </label>
            <input type="file" name="image" accept="image/*">
            <input type="hidden" name="action" value="add_verification">
            <button type="submit" class="action-btn add">Add</button>
        </form>

        <?php if (empty($region_settings)): ?>
            <p>No Verification settings.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="region-settings-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Country</th><th>Type</th><th>Channel</th><th>Name</th><th>Bank</th><th>Acct</th>
                            <th>Medium</th><th>Val1</th><th>Val2</th><th>Curr</th><th>Charge</th><th>Rate</th><th>Img</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($region_settings as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td><?php echo htmlspecialchars($s['country']); ?></td>
                                <td><?php echo $s['account_upgrade'] == 1 ? 'Upgrade' : 'Verify'; ?></td>
                                <td><?php echo htmlspecialchars($s['verify_ch'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($s['vc_value'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($s['verify_ch_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($s['verify_ch_value'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($s['verify_medium'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($s['vcn_value'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($s['vcv_value'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($s['verify_currency'] ?? ''); ?></td>
                                <td><?php echo isset($s['verify_amount']) ? number_format($s['verify_amount'], 2) : ''; ?></td>
                                <td><?php echo isset($s['rate']) ? number_format($s['rate'], 2) : ''; ?></td>
                                <td>
                                    <?php if (!empty($s['images'])): ?>
                                        <img src="../images/<?php echo htmlspecialchars($s['images']); ?>" class="image-preview" alt="Img">
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="edit_region_setting.php?id=<?php echo $s['id']; ?>&section=verification" class="action-btn edit" title="Edit">Edit</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="action-btn delete" title="Delete" onclick="return confirm('Delete all?');">Delete</button>
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
