<?php
session_start();
require_once '../database/conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

// Fetch user verification status
try {
    $stmt = $pdo->prepare("SELECT verification_status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: ../signin.php');
        exit;
    }
    $verification_status = $user['verification_status'];
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage(), 3, '../debug.log');
    header('Location: ../signin.php?error=database');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    // Assume ID document is uploaded; handle file upload in a production environment
    $id_document = $_FILES['id_document'] ?? null;

    if (!$full_name || !$id_document) {
        $error = 'Please provide your full name and ID document.';
    } else {
        try {
            // In a real application, save the document to a secure location and validate it
            // For now, update verification status to 'pending'
            $stmt = $pdo->prepare("UPDATE users SET verification_status = 'pending' WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $success = 'Verification request submitted successfully! It is now pending review.';
        } catch (PDOException $e) {
            error_log('Verification error: ' . $e->getMessage(), 3, '../debug.log');
            $error = 'An error occurred while submitting your verification request.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verify Account | Cash Tube</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      background: #f7f9fc;
    }
    .form-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
      max-width: 500px;
      width: 100%;
    }
    .form-card h2 {
      font-size: 24px;
      margin-bottom: 20px;
      text-align: center;
    }
    .form-card h2::before {
      content: '🔒';
      margin-right: 8px;
    }
    .input-container {
      position: relative;
      margin-bottom: 28px;
    }
    .input-container input,
    .input-container input[type="file"] {
      width: 100%;
      padding: 14px 0;
      font-size: 16px;
      border: none;
      border-bottom: 2px solid #d1d5db;
      background: transparent;
      color: #1a1a1a;
      outline: none;
      transition: border-color 0.3s ease;
    }
    .input-container input:focus,
    .input-container input:valid {
      border-bottom-color: #22c55e;
    }
    .input-container label {
      position: absolute;
      top: 14px;
      left: 0;
      font-size: 16px;
      color: #6b7280;
      pointer-events: none;
      transition: all 0.3s ease;
    }
    .input-container input:focus ~ label,
    .input-container input:valid ~ label {
      top: -18px;
      font-size: 12px;
      color: #22c55e;
    }
    .submit-btn {
      width: 100%;
      padding: 14px;
      background: #22c55e;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
    }
    .submit-btn:hover {
      background: #16a34a;
      transform: scale(1.02);
    }
    .message {
      text-align: center;
      margin-bottom: 20px;
    }
    .success {
      color: #22c55e;
    }
    .error {
      color: #d33;
    }
    a {
      color: #22c55e;
      text-decoration: none;
      font-weight: 500;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="form-card">
    <h2>Verify Your Account</h2>
    <?php if ($verification_status === 'verified'): ?>
      <p class="success">Your account is already verified!</p>
      <p><a href="home.php">Return to Dashboard</a></p>
    <?php elseif ($verification_status === 'pending'): ?>
      <p class="message">Your verification request is pending review.</p>
      <p><a href="home.php">Return to Dashboard</a></p>
    <?php else: ?>
      <?php if (isset($success)): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <p><a href="home.php">Return to Dashboard</a></p>
      <?php elseif (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>
      <form action="verify_account.php" method="POST" enctype="multipart/form-data">
        <div class="input-container">
          <input type="text" id="full_name" name="full_name" required>
          <label for="full_name">Full Name</label>
        </div>
        <div class="input-container">
          <input type="file" id="id_document" name="id_document" accept=".pdf,.jpg,.jpeg,.png" required>
          <label for="id_document">ID Document</label>
        </div>
        <button type="submit" class="submit-btn">Submit Verification</button>
      </form>
      <p style="text-align: center; margin-top: 20px;"><a href="home.php">Return to Dashboard</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
