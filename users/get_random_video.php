<?php
require_once '../database/conn.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT id, title, url, reward FROM videos ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($video) {
        // Use absolute URL
        $video['url'] = 'https://tasktube.app/' . $video['url'];
        // Verify file exists
        $file_path = '../' . ltrim(parse_url($video['url'], PHP_URL_PATH), '/');
        if (!file_exists($file_path)) {
            error_log('Video file not found in get_random_video: ' . $file_path, 3, '../debug.log');
            echo json_encode(null);
            exit;
        }
        echo json_encode($video);
    } else {
        error_log('No videos found in get_random_video', 3, '../debug.log');
        echo json_encode(null);
    }
} catch (PDOException $e) {
    error_log('get_random_video error: ' . $e->getMessage(), 3, '../debug.log');
    echo json_encode(null);
}
?>
