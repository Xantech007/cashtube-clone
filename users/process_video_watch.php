<?php
session_start();
require_once '../database/conn.php';
header('Content-Type: application/json');

// Initialize response
$response = ['success' => false, 'error' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'User not authenticated.';
    error_log('Video watch error: User not authenticated', 3, '../debug.log');
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate POST data
if (!isset($_POST['video_id']) || !is_numeric($_POST['video_id'])) {
    $response['error'] = 'Invalid video ID.';
    error_log('Video watch error: Invalid video ID - ' . ($_POST['video_id'] ?? 'null'), 3, '../debug.log');
    echo json_encode($response);
    exit;
}

$video_id = (int)$_POST['video_id'];

try {
    // Verify video exists
    $stmt = $pdo->prepare("SELECT id, reward FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$video) {
        $response['error'] = 'Video not found.';
        error_log('Video watch error: Video ID ' . $video_id . ' not found', 3, '../debug.log');
        echo json_encode($response);
        exit;
    }

    // Check for duplicate watch
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM activities WHERE user_id = ? AND video_id = ? AND action LIKE 'Watched%'");
    $stmt->execute([$user_id, $video_id]);
    $watch_count = $stmt->fetchColumn();

    if ($watch_count > 0) {
        $response['error'] = 'Video already watched by this user.';
        error_log('Video watch error: User ID ' . $user_id . ' already watched video ID ' . $video_id, 3, '../debug.log');
        echo json_encode($response);
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    // Update user balance
    $reward = $video['reward'];
    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$reward, $user_id]);

    // Log activity
    $action = "Watched video ID $video_id";
    $stmt = $pdo->prepare("INSERT INTO activities (user_id, video_id, action, amount) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $video_id, $action, $reward]);

    // Commit transaction
    $pdo->commit();

    $response['success'] = true;
    $response['reward'] = number_format($reward, 2);
    error_log('Video watch success: User ID ' . $user_id . ', Video ID ' . $video_id . ', Reward $' . $reward, 3, '../debug.log');

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['error'] = 'Database error: ' . $e->getMessage();
    error_log('Video watch database error: ' . $e->getMessage(), 3, '../debug.log');
} catch (Exception $e) {
    $pdo->rollBack();
    $response['error'] = 'Server error: ' . $e->getMessage();
    error_log('Video watch server error: ' . $e->getMessage(), 3, '../debug.log');
}

echo json_encode($response);
exit;
?>
