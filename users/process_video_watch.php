<?php
session_start();
require_once '../database/conn.php'; // Database connection

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Check if video_id is provided
if (!isset($_POST['video_id']) || empty($_POST['video_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid video ID']);
    exit;
}

$video_id = (int)$_POST['video_id'];
$user_id = (int)$_SESSION['user_id'];

try {
    // Verify video exists
    $stmt = $pdo->prepare("SELECT reward FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$video) {
        echo json_encode(['success' => false, 'error' => 'Video not found']);
        exit;
    }

    // Check if user has already watched this video
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM activities WHERE user_id = ? AND video_id = ? AND action = 'Watched video'");
    $stmt->execute([$user_id, $video_id]);
    $watch_count = $stmt->fetchColumn();

    if ($watch_count > 0) {
        echo json_encode(['success' => false, 'error' => 'Video already watched']);
        exit;
    }

    // Update user balance
    $reward = $video['reward'];
    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$reward, $user_id]);

    // Log activity
    $stmt = $pdo->prepare("INSERT INTO activities (user_id, video_id, action, amount) VALUES (?, ?, 'Watched video', ?)");
    $stmt->execute([$user_id, $video_id, $reward]);

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode(['success' => true, 'reward' => number_format($reward, 2)]);
} catch (PDOException $e) {
    // Rollback on error
    $pdo->rollBack();
    error_log('Video watch error: ' . $e->getMessage(), 3, '../debug.log');
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('General error: ' . $e->getMessage(), 3, '../debug.log');
    echo json_encode(['success' => false, 'error' => 'An unexpected error occurred']);
}
?>
