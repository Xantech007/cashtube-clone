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
error_log('Session ID in process_change_passcode.php: ' . session_id() . ', User ID: ' . ($_SESSION['user_id'] ?? 'not set'), 3, '../debug.log');

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

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_passcode = trim($_POST['old_passcode'] ?? '');
    $new_passcode = trim($_POST['new_passcode'] ?? '');
    $confirm_passcode = trim($_POST['confirm_passcode'] ?? '');

    // Validate inputs
    if (empty($old_passcode) || !preg_match('/^\d{5}$/', $old_passcode)) {
        error_log('Invalid old passcode for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Old passcode must be exactly 5 digits']);
        ob_end_flush();
        exit;
    }

    if (empty($new_passcode) || !preg_match('/^\d{5}$/', $new_passcode)) {
        error_log('Invalid new passcode for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'New passcode must be exactly 5 digits']);
        ob_end_flush();
        exit;
    }

    if ($new_passcode !== $confirm_passcode) {
        error_log('Passcodes do not match for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'New passcode and confirm passcode must match']);
        ob_end_flush();
        exit;
    }

    try {
        // Fetch current passcode
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

        // Verify old passcode
        if ($user['passcode'] !== $old_passcode) {
            error_log('Incorrect old passcode for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Incorrect old passcode']);
            ob_end_flush();
            exit;
        }

        // Update passcode
        $stmt = $pdo->prepare("UPDATE users SET passcode = ? WHERE id = ?");
        $success = $stmt->execute([$new_passcode, $_SESSION['user_id']]);

        if ($success) {
            error_log('Passcode updated successfully for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            error_log('Failed to update passcode for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to update passcode']);
        }
    } catch (PDOException $e) {
        error_log('Database error in process_change_passcode.php: ' . $e->getMessage(), 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }
} else {
    error_log('Invalid request method in process_change_passcode.php', 3, '../debug.log');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

ob_end_flush();
?>
