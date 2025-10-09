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
    $url = filter_var($_POST['url'], FILTER_SANITIZE_STRING);
    $reward = filter_var($_POST['reward'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    if (empty($title) || empty($url) || empty($reward)) {
        $_SESSION['error'] = 'All fields are required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO videos (title, url, reward) VALUES (?, ?, ?)");
            $stmt->execute([$title, $url, $reward]);
            $_SESSION['success'] = 'Video added successfully.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to add video: ' . $e->getMessage();
            error_log('Add video error: ' . $e->getMessage(), 3, '../debug.log');
        }
    }
}

header("Location: dashboard.php");
exit;
?>
