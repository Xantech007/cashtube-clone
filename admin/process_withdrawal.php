<?php
session_start();
require_once '../database/conn.php';

// Set time zone to WAT
date_default_timezone_set('Africa/Lagos');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT name, balance, verification_status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: ../signin.php');
        exit;
    }
    $username = htmlspecialchars($user['name']);
    $balance = $user['balance'];
    $verification_status = $user['verification_status'];
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage(), 3, '../debug.log');
    header('Location: ../signin.php?error=database');
    exit;
}

// Check verification status
if ($verification_status !== 'verified') {
    header('Location: home.php?error=Please+verify+your+account+before+withdrawing+funds');
    exit;
}

// Process withdrawal
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$wallet_address = isset($_GET['cryptoAddress']) ? trim($_GET['cryptoAddress']) : '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $amount > 0 && !empty($wallet_address)) {
    // Validate withdrawal
    if ($amount > $balance) {
        $error = 'Insufficient balance for withdrawal.';
    } elseif ($amount <= 0) {
        $error = 'Invalid withdrawal amount.';
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Deduct amount from balance
            $new_balance = $balance - $amount;
            $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$new_balance, $_SESSION['user_id']]);

            // Generate unique reference number
            $ref_number = strtoupper(substr(uniqid(), 0, 10));

            // Insert withdrawal record
            $stmt = $pdo->prepare("
                INSERT INTO withdrawals (user_id, amount, wallet_address, ref_number, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$_SESSION['user_id'], $amount, $wallet_address, $ref_number]);

            // Commit transaction
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Withdrawal error: ' . $e->getMessage(), 3, '../debug.log');
            $error = 'An error occurred while processing your withdrawal.';
        }
    }
} else {
    $error = 'Invalid withdrawal request.';
}
?>

<!-- The HTML remains the same as previously provided, except for the receipt section -->
<div class="receipt-card">
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <button class="back-btn" onclick="window.location.href='home.php'">Back to Home</button>
    <?php else: ?>
        <h2>Withdrawal Request Submitted!</h2>
        <div class="amount">$<?php echo number_format($amount, 2); ?></div>
        <table class="receipt-table">
            <tr>
                <th>Ref Number</th>
                <td><?php echo htmlspecialchars($ref_number); ?></td>
            </tr>
            <tr>
                <th>Request Time</th>
                <td><?php echo date('F j, Y, g:i A T'); ?></td>
            </tr>
            <tr>
                <th>Withdrawal Method</th>
                <td>Crypto</td>
            </tr>
            <tr>
                <th>Crypto Wallet Address</th>
                <td><?php echo htmlspecialchars($wallet_address); ?></td>
            </tr>
            <tr>
                <th>From</th>
                <td>Task Tube</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>Pending</td>
            </tr>
        </table>
        <button class="back-btn" onclick="window.location.href='home.php'">Back to Home</button>
    <?php endif; ?>
</div>
<!-- Rest of the HTML and JavaScript remains unchanged -->
