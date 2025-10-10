<?php
session_start();
require_once '../database/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(null);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, title, url, reward 
        FROM videos 
        WHERE id NOT IN (
            SELECT video_id FROM activities 
            WHERE user_id = ? AND action LIKE 'Watched%'
        ) 
        ORDER BY RAND() LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($video);
} catch (PDOException $e) {
    error_log('Get random video error: ' . $e->getMessage(), 3, '../debug.log');
    echo json_encode(null);
}
?>
