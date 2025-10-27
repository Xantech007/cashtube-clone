<?php
// process_change_password.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
session_start([
    'cookie_path' => '/',
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
]);

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$response = ['success' => false, 'error' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'You must be logged in to change your password.';
    error_log('No user_id in session in process_change_password.php', 3, '../debug.log');
    header('Content-Type: application/json');
    echo json_encode($response);
    ob_end_flush();
    exit;
}

// Include database connection
try {
    require_once '../database/conn.php';
} catch (Exception $e) {
    $response['error'] = 'Failed to connect to database.';
    error_log('Failed to include conn.php: ' . $e->getMessage(), 3, '../debug.log');
    header('Content-Type: application/json');
    echo json_encode($response);
    ob_end_flush();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['old_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($old_password)) {
        $response['error'] = 'Old password is required.';
        error_log('Old password missing', 3, '../debug.log');
    } elseif (strlen($new_password) < 8) {
        $response['error'] = 'New password must be at least 8 characters.';
        error_log('New password too short: ' . strlen($new_password), 3, '../debug.log');
    } elseif ($new_password !== $confirm_password) {
        $response['error'] = 'New password and confirm password do not match.';
        error_log('Password mismatch', 3, '../debug.log');
    } else {
        try {
            // Fetch current password hash
            $stmt = $pdo->prepare("SELECT passcode FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($old_password, $user['passcode'])) {
                // Hash new password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in database
                $stmt = $pdo->prepare("UPDATE users SET passcode = ? WHERE id = ?");
                $stmt->execute([$new_password_hash, $_SESSION['user_id']]);

                // Update session with new password hash
                $_SESSION['passcode'] = $new_password_hash;
                error_log('Password changed successfully for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
                $response['success'] = true;
            } else {
                $response['error'] = 'Incorrect old password.';
                error_log('Incorrect old password for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            }
        } catch (PDOException $e) {
            $response['error'] = 'Database error: ' . $e->getMessage();
            error_log('Database error in process_change_password.php: ' . $e->getMessage(), 3, '../debug.log');
        }
    }
} else {
    $response['error'] = 'Invalid request.';
    error_log('Invalid request in process_change_password.php', 3, '../debug.log');
}

header('Content-Type: application/json');
echo json_encode($response);
ob_end_flush();
exit;
?>
