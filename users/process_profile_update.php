<?php
session_start();
require_once '../database/conn.php';
require_once '../inc/countries.php'; // Include countries list for validation

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$passcode = filter_input(INPUT_POST, 'passcode', FILTER_SANITIZE_STRING);
$country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);

if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid name or email']);
    exit;
}

if ($passcode && !preg_match('/^\d{5}$/', $passcode)) {
    echo json_encode(['success' => false, 'error' => 'Passcode must be 5 digits']);
    exit;
}

if (!$country || !in_array($country, $countries)) {
    echo json_encode(['success' => false, 'error' => 'Invalid country selected']);
    exit;
}

try {
    $updates = ['name' => $name, 'email' => $email, 'country' => $country];
    if ($passcode) {
        $updates['passcode'] = $passcode; // Store passcode in plain text
    }

    $sql = "UPDATE users SET " . implode(', ', array_map(fn($key) => "$key = :$key", array_keys($updates))) . " WHERE id = :id";
    $updates['id'] = $_SESSION['user_id'];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($updates);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log('Profile update error: ' . $e->getMessage(), 3, '../debug.log');
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>
