<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();
session_start([
    'cookie_path' => '/',
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
]);
error_log('Session ID in process_change_password.php: ' . session_id() . ', User ID: ' . ($_SESSION['user_id'] ?? 'not set'), 3, '../debug.log');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log('No user_id in session, redirecting to signin', 3, '../debug.log');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    ob_end_flush();
    exit;
}

// Include database connection
try {
    require_once '../database/conn.php';
} catch (Exception $e) {
    error_log('Failed to include conn.php: ' . $e->getMessage(), 3, '../debug.log');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    ob_end_flush();
    exit;
}

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = trim($_POST['old_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate inputs
    if (empty($old_password)) {
        error_log('Old password missing for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Old password is required']);
        ob_end_flush();
        exit;
    }

    if (empty($new_password) || strlen($new_password) < 8) {
        error_log('Invalid new password for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'New password must be at least 8 characters']);
        ob_end_flush();
        exit;
    }

    if ($new_password !== $confirm_password) {
        error_log('Passwords do not match for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'New password and confirm password must match']);
        ob_end_flush();
        exit;
    }

    try {
        // Fetch current password hash
        $stmt = $pdo->prepare("SELECT passcode FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            error_log('User not found for ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'User not found']);
            ob_end_flush();
            exit;
        }

        // Verify old password
        if (!password_verify($old_password, $user['passcode'])) {
            error_log('Incorrect old password for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Incorrect old password']);
            ob_end_flush();
            exit;
        }

        // Hash new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $stmt = $pdo->prepare("UPDATE users SET passcode = ? WHERE id = ?");
        $success = $stmt->execute([$new_password_hash, $_SESSION['user_id']]);

        if ($success && $stmt->rowCount() > 0) {
            // Update session passcode
            $_SESSION['passcode'] = $new_password_hash;
            error_log('Password updated successfully for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            error_log('Failed to update password for user ID: ' . $_SESSION['user_id'] . ' (no rows affected)', 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to update password']);
        }
    } catch (PDOException $e) {
        error_log('Database error in process_change_password.php: ' . $e->getMessage() . ' | Code: ' . $e->getCode() . ' | Line: ' . $e->getLine(), 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database error occurred: ' . $e->getMessage()]);
        ob_end_flush();
        exit;
    }
} else {
    error_log('Invalid request method in process_change_password.php', 3, '../debug.log');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

ob_end_flush();
?>
