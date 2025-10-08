<?php
// users/home.php
session_start();
require_once '../database/conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT name, balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        error_log('User not found for ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        session_destroy();
        header('Location: ../signin.php');
        exit;
    }
    $username = htmlspecialchars($user['name']);
    $balance = number_format($user['balance'], 2);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage(), 3, '../debug.log');
    session_destroy();
    header('Location: ../signin.php?error=database');
    exit;
}

// Fetch earnings summary (combined query for performance)
try {
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT SUM(amount) FROM activities WHERE user_id = ? AND amount > 0) AS total_earned,
            (SELECT COUNT(*) FROM activities WHERE user_id = ? AND action LIKE 'Watched%') AS videos_watched,
            (SELECT SUM(amount) FROM withdrawals WHERE user_id = ? AND status = 'pending') AS pending_withdrawals
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_earned = $summary['total_earned'] ?: 0.00;
    $videos_watched = $summary['videos_watched'] ?: 0;
    $pending_withdrawals = $summary['pending_withdrawals'] ?: 0.00;
} catch (PDOException $e) {
    error_log('Earnings summary error: ' . $e->getMessage(), 3, '../debug.log');
    $total_earned = 0.00;
    $videos_watched = 0;
    $pending_withdrawals = 0.00;
}

// Fetch recent activities
try {
    $stmt = $pdo->prepare("SELECT action, amount, created_at FROM activities WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Recent activities error: ' . $e->getMessage(), 3, '../debug.log');
    $activities = [];
}

// Fetch a random video from the videos table (assuming url points to local files like 'users/videos/video.mp4')
try {
    $stmt = $pdo->prepare("SELECT id, title, url, reward FROM videos ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Video fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $video = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Access your Cash Tube dashboard to earn up to $1,000 daily by watching video ads. Withdraw your crypto earnings instantly!" />
  <meta name="keywords" content="Cash Tube, dashboard, earn money online, cryptocurrency, watch ads, passive income" />
  <meta name="author" content="Cash Tube" />
  <title>Dashboard | Cash Tube</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      /* Light mode variables */
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
      /* Dark mode variables */
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

    /* Header */
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

    /* Balance Card */
    .balance-card {
      background: linear-gradient(135deg, var(--accent-color), var(--accent-hover));
      color: #fff;
      border-radius: 16px;
      padding: 28px;
      margin: 24px 0;
      box-shadow: 0 6px 16px var(--shadow-color);
      animation: slideIn 0.5s ease-out 0.2s backwards;
    }

    .balance-card p {
      font-size: 18px;
      font-weight: 500;
    }

    .balance-card h2 {
      font-size: 36px;
      font-weight: 700;
      margin-top: 8px;
    }

    /* Earnings Summary */
    .earnings-summary {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 6px 16px var(--shadow-color);
      animation: slideIn 0.5s ease-out 0.3s backwards;
    }

    .earnings-summary h2 {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .earnings-table {
      width: 100%;
      border-collapse: collapse;
    }

    .earnings-table th, .earnings-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }

    .earnings-table th {
      font-weight: 600;
      color: var(--subtext-color);
    }

    .earnings-table td {
      font-weight: 700;
      color: var(--accent-color);
    }

    /* Video Section */
    .video-section {
      text-align: center;
      margin: 48px 0;
      animation: slideIn 0.5s ease-out 0.4s backwards;
    }

    .video-section h1 {
      font-size: 30px;
      font-weight: 700;
      margin-bottom: 20px;
      color: var(--text-color);
    }

    .video-section video {
      border-radius: 16px;
      width: 100%;
      max-width: 640px;
      box-shadow: 0 6px 16px var(--shadow-color);
    }

    .video-section h4 {
      font-size: 16px;
      color: var(--subtext-color);
      margin-top: 20px;
    }

    .video-section span {
      color: var(--accent-color);
      font-weight: 600;
    }

    /* Form Section */
    .form-card {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 6px 16px var(--shadow-color);
      margin: 24px 0;
      animation: slideIn 0.5s ease-out 0.6s backwards;
    }

    .form-card h2::before {
      content: '💸';
      font-size: 1.2rem;
      margin-right: 8px;
    }

    .input-container {
      position: relative;
      margin-bottom: 28px;
    }

    .input-container input,
    .input-container select {
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

    .input-container select {
      padding: 14px 0;
      appearance: none;
      background: transparent;
    }

    .input-container input:focus,
    .input-container input:valid,
    .input-container select:focus,
    .input-container select:valid {
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
    .input-container input:valid ~ label,
    .input-container select:focus ~ label,
    .input-container select:valid ~ label {
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

    /* Activity Section */
    .activity-section {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 6px 16px var(--shadow-color);
      animation: slideIn 0.5s ease-out 0.7s backwards;
    }

    .activity-section h2 {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .activity-table {
      width: 100%;
      border-collapse: collapse;
    }

    .activity-table th, .activity-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }

    .activity-table th {
      font-weight: 600;
      color: var(--subtext-color);
    }

    .activity-table td {
      font-size: 16px;
      color: var(--text-color);
    }

    .activity-table .amount {
      font-weight: 700;
      color: var(--accent-color);
    }

    /* FAQ Section */
    .faq-section {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 6px 16px var(--shadow-color);
      animation: slideIn 0.5s ease-out 0.8s backwards;
    }

    .faq-section h2 {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .faq-item {
      margin-bottom: 20px;
    }

    .faq-item h3 {
      font-size: 18px;
      font-weight: 600;
      color: var(--text-color);
      margin-bottom: 10px;
    }

    .faq-item p {
      font-size: 16px;
      color: var(--subtext-color);
      line-height: 1.6;
    }

    .faq-item a {
      color: var(--accent-color);
      text-decoration: none;
    }

    .faq-item a:hover {
      color: var(--accent-hover);
      text-decoration: underline;
    }

    /* Withdrawal Notifications */
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

    /* Bottom Menu */
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

    /* Gradient Background */
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

    /* Animations */
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

    /* Responsive Design */
    @media (max-width: 768px) {
      .container {
        padding: 16px;
      }

      .header-text h1 {
        font-size: 22px;
      }

      .balance-card h2 {
        font-size: 30px;
      }

      .video-section h1 {
        font-size: 26px;
      }

      .video-section video {
        width: 100%;
      }

      .form-card {
        padding: 20px;
      }

      .notification {
        max-width: 250px;
        right: 10px;
        top: 10px;
      }

      .earnings-table, .activity-table {
        font-size: 14px;
      }

      .earnings-table th, .earnings-table td,
      .activity-table th, .activity-table td {
        padding: 8px;
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
          <h1>Hello, <?php echo $username; ?></h1>
          <p>Start Earning Crypto Today!</p>
        </div>
      </div>
      <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">Toggle Dark Mode</button>
    </div>

    <div class="balance-card">
      <p>Available Crypto Balance</p>
      <h2>$<span id="balance"><?php echo $balance; ?></span></h2>
    </div>

    <div class="earnings-summary">
      <h2>Earnings Summary</h2>
      <table class="earnings-table">
        <thead>
          <tr>
            <th>Metric</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Total Earned</td>
            <td id="total-earned">$<?php echo number_format($total_earned, 2); ?></td>
          </tr>
          <tr>
            <td>Videos Watched</td>
            <td id="videos-watched"><?php echo $videos_watched; ?></td>
          </tr>
          <tr>
            <td>Pending Withdrawals</td>
            <td id="pending-withdrawals">$<?php echo number_format($pending_withdrawals, 2); ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="video-section">
      <h1>Watch Videos to Earn Crypto</h1>
      <?php if ($video): ?>
        <video id="videoPlayer" 
               src="<?php echo htmlspecialchars($video['url']); ?>" 
               autoplay 
               controls 
               playsinline 
               data-video-id="<?php echo $video['id']; ?>"></video>
        <h4 id="video-reward">Earn <span>$<?php echo number_format($video['reward'], 2); ?></span> by watching <span><?php echo htmlspecialchars($video['title']); ?></span>. The more videos you watch, the more your <span>crypto balance</span> increases</h4>
      <?php else: ?>
        <p>No videos available at the moment.</p>
      <?php endif; ?>
    </div>

    <div class="form-card">
      <h2 style="font-size: 24px; margin-bottom: 20px; text-align: center;">Withdraw Crypto Funds</h2>
      <form id="fundForm" role="form">
        <div class="input-container">
          <select id="withdrawalMethod" name="withdrawalMethod" required aria-required="true">
            <option value="" disabled>Select Withdrawal Method</option>
            <option value="crypto" selected>Cryptocurrency</option>
            <option value="cashapp">Cash App</option>
            <option value="bank">Bank Transfer</option>
          </select>
          <label for="withdrawalMethod">Withdrawal Method</label>
        </div>
        <div class="input-container" id="cryptoFields">
          <input type="text" id="cryptoAddress" name="cryptoAddress" required aria-required="true">
          <label for="cryptoAddress">Crypto Wallet Address</label>
        </div>
        <div class="input-container" id="cashappFields" style="display: none;">
          <input type="text" id="cashappTag" name="cashappTag">
          <label for="cashappTag">Cash App $Cashtag</label>
        </div>
        <div class="input-container" id="bankFields" style="display: none;">
          <select id="bankName" name="bankName">
            <option value="" disabled selected>Select Bank</option>
            <option value="Bank of America">Bank of America</option>
            <option value="JPMorgan Chase">JPMorgan Chase</option>
            <option value="Wells Fargo">Wells Fargo</option>
            <option value="Citibank">Citibank</option>
            <option value="U.S. Bank">U.S. Bank</option>
            <option value="PNC Bank">PNC Bank</option>
            <option value="TD Bank">TD Bank</option>
            <option value="Capital One">Capital One</option>
            <option value="HSBC Bank USA">HSBC Bank USA</option>
            <option value="Fifth Third Bank">Fifth Third Bank</option>
            <option value="Regions Bank">Regions Bank</option>
            <option value="Truist Bank">Truist Bank</option>
            <option value="M&T Bank">M&T Bank</option>
            <option value="Huntington National Bank">Huntington National Bank</option>
            <option value="KeyBank">KeyBank</option>
            <option value="Citizens Bank">Citizens Bank</option>
            <option value="Ally Bank">Ally Bank</option>
            <option value="Discover Bank">Discover Bank</option>
            <option value="Synchrony Bank">Synchrony Bank</option>
            <option value="Chime">Chime</option>
          </select>
          <label for="bankName">Bank Name</label>
        </div>
        <div class="input-container" id="accountNumberField" style="display: none;">
          <input type="number" id="accountNumber" name="accountNumber">
          <label for="accountNumber">Account Number</label>
        </div>
        <div class="input-container" id="routingNumberField" style="display: none;">
          <input type="number" id="routingNumber" name="routingNumber">
          <label for="routingNumber">Routing Number</label>
        </div>
        <div class="input-container">
          <input type="number" id="amount" name="amount" step="0.01" required aria-required="true">
          <label for="amount">Amount ($)</label>
        </div>
        <button type="submit" class="submit-btn" aria-label="Withdraw funds">Withdraw</button>
      </form>
    </div>

    <div class="activity-section">
      <h2>Recent Activity</h2>
      <?php if ($activities): ?>
        <table class="activity-table" id="activity-table">
          <thead>
            <tr>
              <th>Action</th>
              <th>Amount</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($activities as $activity): ?>
              <tr>
                <td><?php echo htmlspecialchars($activity['action']); ?></td>
                <td class="amount">$<?php echo number_format($activity['amount'], 2); ?></td>
                <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No recent activity.</p>
      <?php endif; ?>
    </div>

    <div class="faq-section">
      <h2>Frequently Asked Questions</h2>
      <div class="faq-item">
        <h3>How do I earn crypto on Cash Tube?</h3>
        <p>Watch video ads to earn crypto rewards. The more videos you watch, the higher your earnings!</p>
      </div>
      <div class="faq-item">
        <h3>What are the withdrawal options?</h3>
        <p>You can withdraw your earnings via Cryptocurrency, Cash App, or Bank Transfer. Ensure your details are correct to avoid delays.</p>
      </div>
      <div class="faq-item">
        <h3>Is my data secure?</h3>
        <p>We use industry-standard encryption to protect your data. See our <a href="../privacy.php">Privacy Policy</a> for details.</p>
      </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>
  </div>

  <div class="bottom-menu" role="navigation">
    <a href="home.php" class="active">Home</a>
    <a href="profile.php">Profile</a>
    <a href="history.php">History</a>
    <a href="support.php">Support</a>
    <a href="about.php">About</a>
    <button id="logoutBtn" aria-label="Log out">Logout</button>
  </div>

  <!-- JavaScript -->
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
      themeToggle.textContent = isDark ? 'Toggle Dark Mode' : 'Toggle Light Mode
