<?php
session_start();
require_once '../database/conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

// Validate input
$cryptoAddress = filter_input(INPUT_GET, 'cryptoAddress', FILTER_SANITIZE_STRING);
$amount = filter_input(INPUT_GET, 'amount', FILTER_VALIDATE_FLOAT);

if (!$cryptoAddress || $amount === false || $amount <= 0) {
    $error = 'Invalid wallet address or amount.';
} else {
    try {
        // Fetch user balance
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'User not found.';
        } elseif ($amount > $user['balance']) {
            $error = 'Insufficient balance.';
        } else {
            // Insert withdrawal request
            $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, crypto_address, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $amount, $cryptoAddress]);

            // Deduct amount from balance
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $_SESSION['user_id']]);

            $success = 'Withdrawal request for $' . number_format($amount, 2) . ' submitted successfully!';
        }
    } catch (PDOException $e) {
        error_log('Withdrawal error: ' . $e->getMessage(), 3, '../debug.log');
        $error = 'An error occurred while processing your withdrawal.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Withdrawal | Cash Tube</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      background: #f7f9fc;
    }
    .message {
      text-align: center;
      padding: 20px;
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
  <div class="message">
    <?php if (isset($success)): ?>
      <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php elseif (isset($error)): ?>
      <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <p><a href="home.php">Return to Dashboard</a></p>
  </div>
</body>
</html>
