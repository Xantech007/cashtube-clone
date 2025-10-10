<?php
session_start();
require_once '../database/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$video_id = filter_input(INPUT_POST, 'video_id', FILTER_VALIDATE_INT);
$reward = filter_input(INPUT_POST, 'reward', FILTER_VALIDATE_FLOAT);

if (!$video_id || $reward === false || $reward <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid video ID or reward']);
    exit;
}

try {
    // Verify video exists and get reward
    $stmt = $pdo->prepare("SELECT reward, title FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$video) {
        echo json_encode(['success' => false, 'error' => 'Video not found']);
        exit;
    }

    // Check if video was already watched
    $stmt = $pdo->prepare("SELECT id FROM activities WHERE user_id = ? AND video_id = ? AND action LIKE 'Watched%'");
    $stmt->execute([$_SESSION['user_id'], $video_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Video already watched']);
        exit;
    }

    // Update user balance
    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$reward, $_SESSION['user_id']]);

    // Log activity
    $stmt = $pdo->prepare("INSERT INTO activities (user_id, video_id, action, amount) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $video_id, "Watched video: {$video['title']}", $reward]);

    echo json_encode(['success' => true, 'reward' => $reward]);
} catch (PDOException $e) {
    error_log('Process video watch error: ' . $e->getMessage(), 3, '../debug.log');
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
