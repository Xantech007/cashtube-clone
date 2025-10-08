<?php
// users/process_support.php
session_start();
require_once '../database/conn.php';

$response = ['success' => false, 'error' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'Unauthorized';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject)) {
        $response['error'] = 'Subject is required';
    } elseif (empty($message)) {
        $response['error'] = 'Message is required';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $subject, $message]);
            $response['success'] = true;
        } catch (PDOException $e) {
            $response['error'] = 'Database error: ' . $e->getMessage();
            error_log('Support ticket error: ' . $e->getMessage(), 3, '../debug.log');
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
