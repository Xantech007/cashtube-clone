<?php
session_start();
require_once '../database/conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT name, balance, verification_status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: ../signin.php');
        exit;
    }
    $username = htmlspecialchars($user['name']);
    $balance = $user['balance'];
    $verification_status = $user['verification_status'];
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage(), 3, '../debug.log');
    header('Location: ../signin.php?error=database');
    exit;
}

// Check verification status
if ($verification_status !== 'verified') {
    header('Location: home.php?error=Please+verify+your+account+before+withdrawing+funds');
    exit;
}

// Process withdrawal
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$wallet_address = isset($_GET['cryptoAddress']) ? trim($_GET['cryptoAddress']) : '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $amount > 0 && !empty($wallet_address)) {
    // Validate withdrawal
    if ($amount > $balance) {
        $error = 'Insufficient balance for withdrawal.';
    } elseif ($amount <= 0) {
        $error = 'Invalid withdrawal amount.';
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Deduct amount from balance
            $new_balance = $balance - $amount;
            $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$new_balance, $_SESSION['user_id']]);

            // Generate unique reference number
            $ref_number = strtoupper(substr(uniqid(), 0, 10));

            // Insert withdrawal record
            $stmt = $pdo->prepare("
                INSERT INTO withdrawals (user_id, amount, wallet_address, ref_number, status)
                VALUES (?, ?, ?, ?, 'success')
            ");
            $stmt->execute([$_SESSION['user_id'], $amount, $wallet_address, $ref_number]);

            // Commit transaction
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Withdrawal error: ' . $e->getMessage(), 3, '../debug.log');
            $error = 'An error occurred while processing your withdrawal.';
        }
    }
} else {
    $error = 'Invalid withdrawal request.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Withdrawal receipt for your Cash Tube crypto withdrawal." />
  <meta name="keywords" content="Cash Tube, withdrawal, cryptocurrency, receipt" />
  <meta name="author" content="Cash Tube" />
  <title>Withdrawal Receipt | Cash Tube</title>
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

    .receipt-card {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 6px 16px var(--shadow-color);
      margin: 24px 0;
      animation: slideIn 0.5s ease-out 0.6s backwards;
      text-align: center;
    }

    .receipt-card h2 {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
      color: var(--accent-color);
    }

    .receipt-card h2::before {
      content: '✅';
      font-size: 1.2rem;
      margin-right: 8px;
    }

    .receipt-card .amount {
      font-size: 36px;
      font-weight: 700;
      margin: 20px 0;
    }

    .receipt-table {
      width: 100%;
      max-width: 600px;
      margin: 20px auto;
      border-collapse: collapse;
      font-size: 16px;
    }

    .receipt-table th,
    .receipt-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }

    .receipt-table th {
      font-weight: 600;
      color: var(--subtext-color);
      width: 40%;
    }

    .receipt-table td {
      font-weight: 500;
      color: var(--text-color);
    }

    .back-btn {
      width: 100%;
      max-width: 200px;
      padding: 14px;
      background: var(--accent-color);
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
      margin-top: 20px;
    }

    .back-btn:hover {
      background: var(--accent-hover);
      transform: scale(1.02);
    }

    .error {
      text-align: center;
      color: red;
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

      .receipt-card {
        padding: 20px;
      }

      .receipt-card .amount {
        font-size: 30px;
      }

      .receipt-table {
        font-size: 14px;
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
          <h1>Withdrawal Receipt</h1>
          <p>Details of your crypto withdrawal</p>
        </div>
      </div>
      <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">Toggle Dark Mode</button>
    </div>

    <div class="receipt-card">
      <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <button class="back-btn" onclick="window.location.href='home.php'">Back to Home</button>
      <?php else: ?>
        <h2>Payment Success!</h2>
        <div class="amount">$<?php echo number_format($amount, 2); ?></div>
        <table class="receipt-table">
          <tr>
            <th>Ref Number</th>
            <td><?php echo htmlspecialchars($ref_number); ?></td>
          </tr>
          <tr>
            <th>Payment Time</th>
            <td><?php echo date('F j, Y, g:i A'); ?></td>
          </tr>
          <tr>
            <th>Withdrawal Method</th>
            <td>Crypto</td>
          </tr>
          <tr>
            <th>Crypto Wallet Address</th>
            <td><?php echo htmlspecialchars($wallet_address); ?></td>
          </tr>
          <tr>
            <th>From</th>
            <td>Task Tube</td>
          </tr>
          <tr>
            <th>Payment Status</th>
            <td>Success</td>
          </tr>
        </table>
        <button class="back-btn" onclick="window.location.href='home.php'">Back to Home</button>
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
