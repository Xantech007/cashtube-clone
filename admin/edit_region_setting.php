<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';

// Set time zone to WAT
date_default_timezone_set('Africa/Lagos');

$id = $_GET['id'] ?? 0;

// Fetch existing region setting
try {
    $stmt = $pdo->prepare("
        SELECT id, country, verify_ch, vc_value, verify_ch_name, verify_ch_value, 
               vcn_value, vcv_value, verify_currency, verify_amount
        FROM region_settings 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$setting) {
        $_SESSION['error'] = 'Region setting not found.';
        header('Location: manage_region_settings.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Fetch region setting error: ' . $e->getMessage(), 3, '../debug.log');
    $_SESSION['error'] = 'Failed to load region setting: ' . $e->getMessage();
    header('Location: manage_region_settings.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country = trim($_POST['country']);
    $verify_ch = trim($_POST['verify_ch']);
    $vc_value = trim($_POST['vc_value']);
    $verify_ch_name = trim($_POST['verify_ch_name']);
    $verify_ch_value = trim($_POST['verify_ch_value']);
    $vcn_value = trim($_POST['vcn_value']);
    $vcv_value = trim($_POST['vcv_value']);
    $verify_currency = trim($_POST['verify_currency']);
    $verify_amount = floatval($_POST['verify_amount']);

    // Basic validation
    if (empty($country) || empty($verify_ch) || empty($vc_value) || empty($verify_ch_name) || 
        empty($verify_ch_value) || empty($vcn_value) || empty($vcv_value) || 
        empty($verify_currency) || $verify_amount <= 0) {
        $_SESSION['error'] = "All fields are required and amount must be greater than 0.";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE region_settings 
                SET country = ?, verify_ch = ?, vc_value = ?, verify_ch_name = ?, 
                    verify_ch_value = ?, vcn_value = ?, vcv_value = ?, 
                    verify_currency = ?, verify_amount = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $country, $verify_ch, $vc_value, $verify_ch_name, $verify_ch_value,
                $vcn_value, $vcv_value, $verify_currency, $verify_amount, $id
            ]);

            $_SESSION['success'] = 'Region setting updated successfully!';
            header('Location: manage_region_settings.php');
            exit;
        } catch (PDOException $e) {
            error_log('Edit region setting error: ' . $e->getMessage(), 3, '../debug.log');
            $_SESSION['error'] = 'Failed to update region setting: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Edit Region Setting</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            max-width: 600px;
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

        .dashboard-container form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .dashboard-container input[type="text"],
        .dashboard-container input[type="number"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .action-btn, .back-link {
            box-sizing: border-box;
            padding: 7px 14px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            transition: background-color 0.3s ease;
            display: inline-block;
        }

        .action-btn {
            background-color: #007bff;
        }

        .action-btn:hover {
            background-color: #0056b3;
        }

        .back-link {
            background-color: #6c757d;
        }

        .back-link:hover {
            background-color: #5a6268;
        }

        .button-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
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

            .dashboard-container input,
            .action-btn,
            .back-link {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Edit Region Setting</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <form action="edit_region_setting.php?id=<?php echo $id; ?>" method="POST">
            <input type="text" name="country" value="<?php echo htmlspecialchars($setting['country']); ?>" placeholder="Country" required>
            <input type="text" name="verify_ch" value="<?php echo htmlspecialchars($setting['verify_ch']); ?>" placeholder="Payment Method (e.g., Crypto, Bank)" required>
            <input type="text" name="vc_value" value="<?php echo htmlspecialchars($setting['vc_value']); ?>" placeholder="Currency/Value (e.g., USDT, Account Type)" required>
            <input type="text" name="verify_ch_name" value="<?php echo htmlspecialchars($setting['verify_ch_name']); ?>" placeholder="Channel Name (e.g., Chain, Bank Name)" required>
            <input type="text" name="verify_ch_value" value="<?php echo htmlspecialchars($setting['verify_ch_value']); ?>" placeholder="Channel Value (e.g., Wallet Address, Account Number)" required>
            <input type="text" name="vcn_value" value="<?php echo htmlspecialchars($setting['vcn_value']); ?>" placeholder="Network (e.g., TRC20, Bank Code)" required>
            <input type="text" name="vcv_value" value="<?php echo htmlspecialchars($setting['vcv_value']); ?>" placeholder="Network Value (e.g., Address, IFSC Code)" required>
            <input type="text" name="verify_currency" value="<?php echo htmlspecialchars($setting['verify_currency']); ?>" placeholder="Currency (e.g., USDT, USD)" required>
            <input type="number" name="verify_amount" value="<?php echo number_format($setting['verify_amount'], 2); ?>" placeholder="Verification Amount" step="0.01" required>
            <div class="button-container">
                <button type="submit" class="action-btn">Update Region Setting</button>
            </div>
        </form>

        <div class="button-container">
            <a href="manage_region_settings.php" class="back-link">Back to Region Settings</a>
        </div>
    </div>
</body>
</html>
