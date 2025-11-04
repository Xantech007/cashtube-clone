<?php
session_start();
require_once '../database/conn.php';

date_default_timezone_set('Africa/Lagos');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT name, email, upgrade_status, country FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: ../signin.php');
        exit;
    }
    $username = htmlspecialchars($user['name']);
    $email = htmlspecialchars($user['email']);
    $upgrade_status = $user['upgrade_status'] ?? 'not_upgraded';
    $user_country = htmlspecialchars($user['country']);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage(), 3, '../debug.log');
    header('Location: ../signin.php?error=database');
    exit;
}

// === FETCH SETTINGS + IMAGE ===
$region_image = '';
try {
    $stmt = $pdo->prepare("
        SELECT crypto, account_upgrade, verify_ch, vc_value, verify_ch_name, verify_ch_value, 
               COALESCE(verify_medium, 'Payment Method') AS verify_medium, 
               vcn_value, vcv_value, verify_currency, verify_amount,
               images
        FROM region_settings 
        WHERE country = ?
    ");
    $stmt->execute([$user_country]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings && !empty($settings['images'])) {
        $region_image = htmlspecialchars(trim($settings['images']));
    }

    if (!$settings || empty($settings['account_upgrade'])) {
        $error = 'Account upgrade settings not found for your country. Please contact support.';
        $crypto = 0;
        $account_upgrade = 'Payment Method';
        $verify_ch = 'Payment Method';
        $vc_value = 'Obi Mikel';
        $verify_ch_name = 'Account Name';
        $verify_ch_value = 'Account Number';
        $verify_medium = 'Payment Method';
        $vcn_value = 'First Bank';
        $vcv_value = '8012345678';
        $verify_currency = 'NGN';
        $verify_amount = 0.00;
        error_log('No account upgrade settings found for country: ' . $user_country, 3, '../debug.log');
    } else {
        $crypto = $settings['crypto'] ?? 0;
        $account_upgrade = htmlspecialchars($settings['account_upgrade'] ?: 'Payment Method');
        $verify_ch = htmlspecialchars($settings['verify_ch'] ?: 'Payment Method');
        $vc_value = htmlspecialchars($settings['vc_value'] ?: 'Obi Mikel');
        $verify_ch_name = htmlspecialchars($settings['verify_ch_name'] ?: 'Account Name');
        $verify_ch_value = htmlspecialchars($settings['verify_ch_value'] ?: 'Account Number');
        $verify_medium = htmlspecialchars($settings['verify_medium'] ?: 'Payment Method');
        $vcn_value = htmlspecialchars($settings['vcn_value'] ?: 'First Bank');
        $vcv_value = htmlspecialchars($settings['vcv_value'] ?: '8012345678');
        $verify_currency = htmlspecialchars($settings['verify_currency'] ?: 'NGN');
        $verify_amount = floatval($settings['verify_amount'] ?: 0.00);
    }
} catch (PDOException $e) {
    error_log('Settings fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $error = 'Failed to load upgrade settings. Please try again later.';
    $crypto = 0;
    $account_upgrade = 'Payment Method';
    $verify_ch = 'Payment Method';
    $vc_value = 'Obi Mikel';
    $verify_ch_name = 'Account Name';
    $verify_ch_value = 'Account Number';
    $verify_medium = 'Payment Method';
    $vcn_value = 'First Bank';
    $vcv_value = '8012345678';
    $verify_currency = 'NGN';
    $verify_amount = 0.00;
}

// Handle form submission (unchanged)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proof_file = $_FILES['proof_file'] ?? null;

    if (!$proof_file || $proof_file['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please upload a payment receipt.';
    } else {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024;
        if (!in_array($proof_file['type'], $allowed_types) || $proof_file['size'] > $max_size) {
            $error = 'Invalid file type or size. Please upload a JPG or PNG file (max 5MB).';
        } else {
            $upload_dir = '../users/proofs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_ext = pathinfo($proof_file['name'], PATHINFO_EXTENSION);
            $file_name = 'upgrade_proof_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $file_name;

            if (move_uploaded_file($proof_file['tmp_name'], $upload_path)) {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("UPDATE users SET upgrade_status = 'pending' WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);

                    $stmt = $pdo->prepare("
                        INSERT INTO upgrade_requests 
                        (user_id, payment_amount, name, email, upload_path, file_name, status, payment_method, currency)
                        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)
                    ");
                    $stmt->execute([
                        $_SESSION['user_id'], $verify_amount, $username, $email, 
                        $upload_path, $file_name, $account_upgrade, $verify_currency
                    ]);

                    $pdo->commit();
                    header('Location: home.php?success=Upgrade+request+submitted+successfully');
                    exit;
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    error_log('Upgrade error: ' . $e->getMessage(), 3, '../debug.log');
                    $error = 'An error occurred while submitting your upgrade request. Please try again.';
                    if (file_exists($upload_path)) unlink($upload_path);
                }
            } else {
                $error = 'Failed to upload payment receipt. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Upgrade your Cash Tube account to unlock Currency Exchange." />
    <meta name="keywords" content="Cash Tube, upgrade account, currency exchange, payment" />
    <meta name="author" content="Cash Tube" />
    <title>Upgrade Account | Cash Tube</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #f7f9fc;
            --gradient-bg: linear-gradient(135deg, #f7f9fc, #e5e7eb);
            --card-bg: #ffffff;
            --text-color: #1a1a1a;
            --subtext-color: #6b7280;
            --border-color: #d1d5db;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --accent-color: #22c55e;
            --accent-hover: #16a34a;
            --menu-bg: #1a1a1a;
            --menu-text: #ffffff;
        }

        [data-theme="dark"] {
            --bg-color: #1f2937;
            --gradient-bg: linear-gradient(135deg, #1f2937, #374151);
            --card-bg: #2d3748;
            --text-color: #e5e7eb;
            --subtext-color: #9ca3af;
            --border-color: #4b5563;
            --shadow-color: rgba(0, 0, 0, 0.3);
            --accent-color: #34d399;
            --accent-hover: #22c55e;
            --menu-bg: #111827;
            --menu-text: #e5e7eb;
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
            padding-bottom: 100px;
            transition: all 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
            position: relative;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 0;
            animation: slideIn 0.5s ease-out;
        }

        .header img {
            width: 64px;
            height: 64px;
            margin-right: 16px;
            border-radius: 8px;
        }

        .header-text h1 {
            font-size: 26px;
            font-weight: 700;
        }

        .header-text p {
            font-size: 16px;
            color: var(--subtext-color);
            margin-top: 4px;
        }

        .theme-toggle {
            background: var(--accent-color);
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .theme-toggle:hover {
            background: var(--accent-hover);
            transform: scale(1.02);
        }

        .form-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 6px 16px var(--shadow-color);
            margin: 24px 0;
            animation: slideIn 0.5s ease-out 0.6s backwards;
        }

        /* === CHANGED: Only lock icon + "Account Upgrade" === */
        .form-card h2 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-card h2::before {
            content: 'Lock';
            font-size: 1.2rem;
            margin-right: 8px;
        }
        /* =============================================== */

        .instructions {
            margin-bottom: 24px;
            font-size: 16px;
            color: var(--subtext-color);
            line-height: 1.6;
        }

        .instructions h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 12px;
        }

        .instructions p {
            margin-bottom: 12px;
        }

        .instructions strong {
            color: var(--text-color);
        }

        .instructions ul {
            list-style-type: disc;
            padding-left: 24px;
            margin-bottom: 12px;
        }

        .instructions ul li {
            margin-bottom: 8px;
        }

        .copyable {
            cursor: pointer;
            padding: 2px 4px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        .copyable:hover {
            background-color: var(--border-color);
        }

        /* === PAYMENT IMAGE STYLING === */
        .payment-image {
            text-align: center;
            margin: 20px 0;
        }

        .payment-image img {
            max-width: 100%;
            width: 300px;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px var(--shadow-color);
            border: 1px solid var(--border-color);
        }
        /* ============================= */

        .input-container {
            position: relative;
            margin-bottom: 28px;
        }

        .input-container input,
        .input-container input[type="file"] {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--card-bg);
            color: var(--text-color);
            outline: none;
            transition: border-color 0.3s ease;
        }

        .input-container input[type="file"] {
            padding: 12px;
            cursor: pointer;
        }

        .input-container input:focus,
        .input-container input:valid {
            border-color: var(--accent-color);
        }

        .input-container label {
            position: absolute;
            top: -10px;
            left: 12px;
            font-size: 12px;
            color: var(--subtext-color);
            background: var(--card-bg);
            padding: 0 4px;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .input-container input:placeholder-shown ~ label {
            top: 14px;
            font-size: 16px;
            color: var(--subtext-color);
        }

        .input-container input:focus ~ label,
        .input-container input:not(:placeholder-shown) ~ label {
            top: -10px;
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

        .error {
            text-align: center;
            color: red;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success {
            text-align: center;
            color: var(--accent-color);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            color: var(--text-color);
            padding: 16px 24px;
            border-radius: 12px;
            border: 2px solid var(--accent-color);
            box-shadow: 0 4px 12px var(--shadow-color), 0 0 8px var(--accent-color);
            z-index: 1000;
            display: flex;
            align-items: center;
            animation: slideInRight 0.5s ease-out, fadeOut 0.5s ease-out 3s forwards;
            max-width: 300px;
            transition: transform 0.2s ease;
        }

        .notification:hover {
            transform: scale(1.05);
        }

        .notification::before {
            content: 'Lock';
            font-size: 1.2rem;
            margin-right: 12px;
            color: var(--accent-color);
        }

        .notification span {
            font-size: 14px;
            font-weight: 500;
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeOut {
            to { opacity: 0; transform: translateY(-20px); }
        }

        .bottom-menu {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: var(--menu-bg);
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 14px 0;
            box-shadow: 0 -2px 8px var(--shadow-color);
        }

        .bottom-menu a,
        .bottom-menu button {
            color: var(--menu-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 10px 18px;
            transition: color 0.3s ease;
            background: none;
            border: none;
            cursor: pointer;
        }

        .bottom-menu a.active,
        .bottom-menu a:hover,
        .bottom-menu button:hover {
            color: var(--accent-color);
        }

        #gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: var(--gradient-bg);
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .container { padding: 16px; }
            .header-text h1 { font-size: 22px; }
            .form-card { padding: 20px; }
            .notification { max-width: 250px; right: 10px; top: 10px; }
            .instructions { font-size: 14px; }
            .instructions h3 { font-size: 16px; }
            .payment-image img { width: 100%; max-width: 280px; }
        }
    </style>
</head>
<body>
    <div id="gradient"></div>
    <div class="container" role="main">
        <div class="header">
            <div style="display: flex; align-items: center;">
                <img src="img/top.png" alt="Cash Tube Logo" aria-label="Cash Tube Logo">
                <div class="header-text">
                    <h1>Upgrade Account</h1>
                    <p>Unlock Currency Exchange feature</p>
                </div>
            </div>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">Toggle Dark Mode</button>
        </div>

        <div class="form-card">
            <!-- === CHANGED: Only lock + "Account Upgrade" === -->
            <h2>Account Upgrade</h2>
            <!-- ============================================= -->

            <?php if ($upgrade_status === 'upgraded'): ?>
                <p class="success">Your account is already upgraded!</p>
                <p style="text-align: center;"><a href="home.php">Return to Dashboard</a></p>
            <?php elseif ($upgrade_status === 'pending'): ?>
                <p class="success">Your upgrade request is pending review.</p>
                <p style="text-align: center;"><a href="home.php">Return to Dashboard</a></p>
            <?php else: ?>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <div class="instructions">
                    <h3>Upgrade Instructions</h3>
                    <p>To upgrade your account and unlock Currency Exchange, please make a payment of <strong><?php echo htmlspecialchars($verify_currency); ?> <?php echo number_format($verify_amount, 2); ?></strong> via <strong><?php echo htmlspecialchars($account_upgrade); ?></strong> using the details below:</p>

                    <!-- === IMAGE DISPLAYED HERE (after instructions) === -->
                    <?php if (!empty($region_image) && file_exists("../images/{$region_image}")): ?>
                        <div class="payment-image">
                            <img src="../images/<?php echo $region_image; ?>" alt="Payment Instructions">
                        </div>
                    <?php endif; ?>
                    <!-- ================================================= -->

                    <p><strong><?php echo htmlspecialchars($verify_medium); ?>:</strong> <?php echo htmlspecialchars($vcn_value); ?></p>
                    <p><strong><?php echo htmlspecialchars($verify_ch_name); ?>:</strong> <?php echo htmlspecialchars($vc_value); ?></p>
                    <p><strong><?php echo htmlspecialchars($verify_ch_value); ?>:</strong> <span class="copyable" data-copy="<?php echo htmlspecialchars($vcv_value); ?>" title="Tap to copy on mobile, press and hold on desktop"><?php echo htmlspecialchars($vcv_value); ?></span></p>
                    <p>After completing the payment, upload a payment receipt below. Your upgrade request will be reviewed within 48 hours.</p>
                    
                    <h3>Important Notes</h3>
                    <?php if ($crypto): ?>
                        <ul>
                            <li>Ensure the payment is made via <strong><?php echo htmlspecialchars($account_upgrade); ?></strong> to the specified <strong><?php echo htmlspecialchars($verify_ch_value); ?></strong>.</li>
                            <li>Upload a clear payment receipt.</li>
                            <li>Supported file types: JPG, PNG (max size: 5MB).</li>
                            <li>Upgrade may take up to 48 hours to process.</li>
                        </ul>
                    <?php else: ?>
                        <ul>
                            <li>Ensure the payment is made via <strong><?php echo htmlspecialchars($account_upgrade); ?></strong> to the specified <strong><?php echo htmlspecialchars($verify_ch_value); ?></strong>.</li>
                            <li>Upload a clear payment receipt.</li>
                            <li>Supported file types: JPG, PNG (max size: 5MB).</li>
                            <li>Upgrade may take up to 48 hours to process.</li>
                        </ul>
                    <?php endif; ?>
                </div>

                <form action="upgrade_account.php" method="POST" enctype="multipart/form-data">
                    <div class="input-container">
                        <input type="file" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png" required placeholder=" ">
                        <label for="proof_file">Upload Payment Receipt</label>
                    </div>
                    <button type="submit" class="submit-btn">Submit Upgrade Request</button>
                </form>
                <p style="text-align: center; margin-top: 20px;"><a href="home.php">Return to Dashboard</a></p>
            <?php endif; ?>
        </div>

        <div id="notificationContainer"></div>
    </div>

    <div class="bottom-menu" role="navigation">
        <a href="home.php">Home</a>
        <a href="profile.php" class="active">Profile</a>
        <a href="history.php">History</a>
        <a href="support.php">Support</a>
        <button id="logoutBtn" aria-label="Log out">Logout</button>
    </div>

    <!-- JavaScript remains unchanged -->
    <script>
        // ... (all your existing JS: LiveChat, theme, copy, notifications, etc.) ...
        // (No changes needed in JS)
    </script>
</body>
</html>
