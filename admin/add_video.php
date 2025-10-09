<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $reward = filter_var($_POST['reward'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $upload_dir = '../users/videos/';
    $allowed_types = ['video/mp4', 'video/avi', 'video/quicktime'];
    $max_size = 100 * 1024 * 1024; // 100MB

    if (empty($title) || empty($reward) || empty($_FILES['video_file']['name'])) {
        $_SESSION['error'] = 'All fields are required.';
        header("Location: dashboard.php");
        exit;
    }

    // Validate file
    $file = $_FILES['video_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'File upload error: ' . $file['error'];
        header("Location: dashboard.php");
        exit;
    }

    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = 'Invalid file type. Only MP4, AVI, and MOV are allowed.';
        header("Location: dashboard.php");
        exit;
    }

    if ($file['size'] > $max_size) {
        $_SESSION['error'] = 'File size exceeds 100MB limit.';
        header("Location: dashboard.php");
        exit;
    }

    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('video_') . '_' . time() . '.' . $ext;
    $destination = $upload_dir . $filename;
    $url = 'users/videos/' . $filename; // Relative path for database

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO videos (title, url, reward) VALUES (?, ?, ?)");
            $stmt->execute([$title, $url, $reward]);
            $_SESSION['success'] = 'Video uploaded successfully.';
        } catch (PDOException $e) {
            unlink($destination); // Remove file if database insert fails
            $_SESSION['error'] = 'Failed to save video to database: ' . $e->getMessage();
            error_log('Add video error: ' . $e->getMessage(), 3, '../debug.log');
        }
    } else {
        $_SESSION['error'] = 'Failed to upload video file.';
        error_log('File upload failed: ' . $file['name'], 3, '../debug.log');
    }
}

header("Location: dashboard.php");
exit;
?>
