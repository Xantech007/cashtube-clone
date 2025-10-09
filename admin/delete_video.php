<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../database/conn.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $_SESSION['success'] = 'Video deleted successfully.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete video: ' . $e->getMessage();
        error_log('Delete video error: ' . $e->getMessage(), 3, '../debug.log');
    }
}

header("Location: dashboard.php");
exit;
?>
