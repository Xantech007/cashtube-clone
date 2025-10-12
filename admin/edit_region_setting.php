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
        SELECT id, country, section_header, crypto, channel, ch_name, ch_value, 
               verify_ch, vc_value, verify_ch_name, verify_ch_value, 
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
        ($crypto == 1 && empty($channel))) {
        $_SESSION['error'] = "All fields are required, amount must be greater than 0, and channel is required if crypto is enabled.";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE region_settings 
                SET country = ?, section_header = ?, crypto = ?, channel = ?, 
                    ch_name = ?, ch_value = ?, verify_ch = ?, vc_value = ?, 
                    verify_ch_name = ?, verify_ch_value = ?, vcn_value = ?, 
                    vcv_value = ?, verify_currency = ?, verify_amount = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $country, $section_header, $crypto, $channel, $ch_name, $ch_value,
                $verify_ch, $vc_value, $verify_ch_name, $verify_ch_value,
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 600px;
            width: 90%;
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .dashboard-container h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .dashboard-container form {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 500px;
            gap: 12px;
        }

        .dashboard-container input[type="text"],
        .dashboard-container input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .dashboard-container .crypto-toggle {
            display: flex;
            align-items: center;
            justify-content: flex-start; /* Align content to the left */
            width: 100%;
            gap: 10px; /* Space between label and checkbox */
        }

        .dashboard-container .crypto-toggle label {
            font-size: 14px;
            color: #333;
            order: -1; /* Ensure label appears before checkbox */
        }

        .dashboard-container .crypto-toggle input[type="checkbox"] {
            width: auto; /* Checkbox should not take full width */
            margin: 0; /* Remove default margins */
        }

        .dashboard-container input#channel {
            display: none;
        }

        .dashboard-container input#channel.active {
            display: block;
        }

        .dashboard-container button {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
            max-width: 200px;
            align-self: center;
            margin-top: 10px;
        }

        .dashboard-container button:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px;
            background-color: #6c757d;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            width: 100%;
            max-width: 200px;
            text-align: center;
        }

        .back-link:hover {
            background-color: #5a6268;
        }

        .error, .success {
            color: red;
            margin-bottom: 15px;
            text-align: center;
            width: 100%;
        }

        .success {
            color: green;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin: 15px;
                padding: 15px;
            }

            .dashboard-container form,
            .dashboard-container input,
            .dashboard-container button,
            .back-link {
                max-width: 100%;
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
                    channelInput.value = ''; // Clear channel input when crypto is off
                }
            }

            cryptoCheckbox.addEventListener('change', toggleChannelInput);
            toggleChannelInput(); // Initial check
        });
    </script>
</head>
<body>
    <div class="dashboard-container">
        <h2>Edit Region Setting</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <form action="edit_region_setting.php?id=<?php echo $id; ?>" method="POST">
            <input type="text" name="country" value="<?php echo htmlspecialchars($setting['country']); ?>" placeholder="Country" required>
            <input type="text" name="section_header" value="<?php echo htmlspecialchars($setting['section_header']); ?>" placeholder="Withdraw Section Heading (e.g., Withdraw with bank/crypto)" required>
            <div class="crypto-toggle">
                <label for="crypto">Enable Crypto</label>
                <input type="checkbox" id="crypto" name="crypto" <?php echo $setting['crypto'] ? 'checked' : ''; ?>>
            </div>
            <input type="text" id="channel" name="channel" value="<?php echo htmlspecialchars($setting['channel']); ?>" placeholder="Channel (e.g., Coin)">
            <input type="text" name="ch_name" value="<?php echo htmlspecialchars($setting['ch_name']); ?>" placeholder="Channel Name (e.g., Bank Name/Network)" required>
            <input type="text" name="ch_value" value="<?php echo htmlspecialchars($setting['ch_value']); ?>" placeholder="Channel Number (e.g., Account Number/Wallet Address)" required>
            <input type="text" name="verify_ch" value="<?php echo htmlspecialchars($setting['verify_ch']); ?>" placeholder="Channel (e.g., Bank)" required>
            <input type="text" name="vc_value" value="<?php echo htmlspecialchars($setting['vc_value']); ?>" placeholder="Name (e.g., Obi Mikel)" required>
            <input type="text" name="verify_ch_name" value="<?php echo htmlspecialchars($setting['verify_ch_name']); ?>" placeholder="Channel Name (e.g., Bank Name)" required>
            <input type="text" name="verify_ch_value" value="<?php echo htmlspecialchars($setting['verify_ch_value']); ?>" placeholder="Channel Number (e.g., Account Number)" required>
            <input type="text" name="vcn_value" value="<?php echo htmlspecialchars($setting['vcn_value']); ?>" placeholder="Channel Name Value (e.g., MOMO PSB)" required>
            <input type="text" name="vcv_value" value="<?php echo htmlspecialchars($setting['vcv_value']); ?>" placeholder="Channel Number Value (e.g., 8012345678)" required>
            <input type="text" name="verify_currency" value="<?php echo htmlspecialchars($setting['verify_currency']); ?>" placeholder="Currency (e.g., NGN)" required>
            <input type="number" name="verify_amount" value="<?php echo number_format($setting['verify_amount'], 2); ?>" placeholder="Charges (e.g., 15000)" step="0.01" required>
            <button type="submit">Update Region Setting</button>
        </form>

        <a href="manage_region_settings.php" class="back-link">Back to Region Settings</a>
    </div>
</body>
</html>
