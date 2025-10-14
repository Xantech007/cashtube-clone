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
error_log('Session ID in process_profile_update.php: ' . session_id() . ', User ID: ' . ($_SESSION['user_id'] ?? 'not set'), 3, '../debug.log');

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

// Include countries list
try {
    require_once '../inc/countries.php';
    if (!isset($countries) || !is_array($countries)) {
        throw new Exception('Countries list not defined or invalid in countries.php');
    }
} catch (Exception $e) {
    error_log('Failed to include countries.php: ' . $e->getMessage(), 3, '../debug.log');
    $countries = ['United States', 'Nigeria', 'United Kingdom']; // Fallback
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $country = trim($_POST['country'] ?? '');

    // Validate inputs
    if (empty($name)) {
        error_log('Invalid name for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Name is required']);
        ob_end_flush();
        exit;
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('Invalid email for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid email address']);
        ob_end_flush();
        exit;
    }

    if (empty($country) || !in_array($country, $countries)) {
        error_log('Invalid country for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid country']);
        ob_end_flush();
        exit;
    }

    try {
        // Check if email is already in use by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            error_log('Email already in use for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Email is already in use']);
            ob_end_flush();
            exit;
        }

        // Update user data
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, country = ? WHERE id = ?");
        $success = $stmt->execute([$name, $email, $country, $_SESSION['user_id']]);

        if ($success) {
            error_log('Profile updated successfully for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            error_log('Failed to update profile for user ID: ' . $_SESSION['user_id'], 3, '../debug.log');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
        }
    } catch (PDOException $e) {
        error_log('Database error in process_profile_update.php: ' . $e->getMessage(), 3, '../debug.log');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }
} else {
    error_log('Invalid request method in process_profile_update.php', 3, '../debug.log');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

ob_end_flush();
?>
