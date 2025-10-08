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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Access your Task Tube dashboard to earn up to $1,000 daily by watching video ads. Withdraw your crypto earnings instantly!">
    <meta name="keywords" content="Task Tube, dashboard, earn money online, cryptocurrency, watch ads, passive income">
    <meta name="author" content="Task Tube">
    <title>Dashboard | Task Tube</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 16px;
        }

        .balance-card,
        .earnings-summary,
        .video-section,
        .form-card,
        .activity-section,
        .faq-section {
            width: 100%;
            max-width: 1200px;
            margin: 24px auto;
            padding: 28px;
            box-sizing: border-box;
        }

        .balance-card {
            background: linear-gradient(135deg, var(--accent-color), var(--accent-hover));
            color: #fff;
            border-radius: 16px;
            padding: 28px;
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

        .form-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 6px 16px var(--shadow-color);
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

        .input-container input {
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
            z-index: 1002;
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
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeOut {
            to { opacity: 0; transform: translateY(-20px); }
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
            z-index: 1000;
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
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .container,
            .balance-card,
            .earnings-summary,
            .video-section,
            .form-card,
            .activity-section,
            .faq-section {
                width: 100%;
                padding: 20px 12px;
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
    <div class="main-content">
        <div class="container" role="main">
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
                    <h4 id="video-reward">Earn <span>$<?php echo number_format($video['reward'], 2); ?></span> by watching <span><?php echo htmlspecialchars($video['title']); ?></span></h4>
                <?php else: ?>
                    <p>No videos available at the moment.</p>
                <?php endif; ?>
            </div>

            <div class="form-card">
                <h2>Withdraw Crypto Funds</h2>
                <form id="fundForm" role="form">
                    <div class="input-container">
                        <input type="text" id="cryptoAddress" name="cryptoAddress" required aria-required="true">
                        <label for="cryptoAddress">Crypto Wallet Address</label>
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
                    <h3>How do I earn crypto on Task Tube?</h3>
                    <p>Watch video ads to earn crypto rewards. The more videos you watch, the higher your earnings!</p>
                </div>
                <div class="faq-item">
                    <h3>What are the withdrawal options?</h3>
                    <p>You can withdraw your earnings via Cryptocurrency. Ensure your wallet address is correct to avoid delays.</p>
                </div>
                <div class="faq-item">
                    <h3>Is my data secure?</h3>
                    <p>We use industry-standard encryption to protect your data. See our <a href="../privacy.php">Privacy Policy</a> for details.</p>
                </div>
            </div>

            <div id="notificationContainer"></div>
        </div>
    </div>

    <div class="bottom-menu" role="navigation">
        <a href="home.php" class="active">Home</a>
        <a href="profile.php">Profile</a>
        <a href="history.php">History</a>
        <a href="support.php">Support</a>
        <button id="logoutBtn" aria-label="Log out">Logout</button>
    </div>

    <!-- LiveChat Integration -->
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
        // Menu Interactions
        const menuItems = document.querySelectorAll('.bottom-menu a');
        menuItems.forEach((item) => {
            item.addEventListener('click', () => {
                menuItems.forEach((menuItem) => menuItem.classList.remove('active'));
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

        // Form Submission
        document.getElementById('fundForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const cryptoAddress = document.getElementById('cryptoAddress').value;
            const amount = parseFloat(document.getElementById('amount').value);
            const balance = parseFloat(document.getElementById('balance').textContent);

            if (!cryptoAddress) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Wallet Address',
                    text: 'Please enter a valid crypto wallet address.'
                });
                return;
            }

            if (amount <= 0 || isNaN(amount)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Amount',
                    text: 'Please enter a valid withdrawal amount.'
                });
                return;
            }

            if (amount > balance) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Balance',
                    text: 'Withdrawal amount exceeds your available balance.'
                });
                return;
            }

            $.ajax({
                url: 'process_withdrawal.php',
                type: 'POST',
                data: { cryptoAddress: cryptoAddress, amount: amount },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Withdrawal Requested',
                            text: 'Your withdrawal request has been submitted successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Update pending withdrawals without reload
                            const newPending = parseFloat(document.getElementById('pending-withdrawals').textContent.replace('$', '')) + amount;
                            document.getElementById('pending-withdrawals').textContent = `$${newPending.toFixed(2)}`;
                            // Update balance
                            const newBalance = balance - amount;
                            document.getElementById('balance').textContent = newBalance.toFixed(2);
                            // Append to activity if needed (assuming process_withdrawal logs activity)
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Failed to process withdrawal. Please try again.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'An error occurred. Please try again later.'
                    });
                }
            });
        });

        // Video Watch Tracking and Auto-Play Next
        const videoPlayer = document.getElementById('videoPlayer');
        if (videoPlayer) {
            videoPlayer.addEventListener('ended', function() {
                const videoId = videoPlayer.getAttribute('data-video-id');
                $.ajax({
                    url: 'process_video_watch.php',
                    type: 'POST',
                    data: { video_id: videoId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Video Watched',
                                text: `You earned $${response.reward}!`,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Update balance
                            const currentBalance = parseFloat(document.getElementById('balance').textContent);
                            const newBalance = currentBalance + parseFloat(response.reward);
                            document.getElementById('balance').textContent = newBalance.toFixed(2);

                            // Update earnings summary
                            const currentTotalEarned = parseFloat(document.getElementById('total-earned').textContent.replace('$', ''));
                            const newTotalEarned = currentTotalEarned + parseFloat(response.reward);
                            document.getElementById('total-earned').textContent = `$${newTotalEarned.toFixed(2)}`;

                            const currentVideosWatched = parseInt(document.getElementById('videos-watched').textContent);
                            document.getElementById('videos-watched').textContent = currentVideosWatched + 1;

                            // Append to recent activity
                            const activityTableBody = document.querySelector('#activity-table tbody');
                            if (activityTableBody) {
                                const newRow = document.createElement('tr');
                                newRow.innerHTML = `
                                    <td>${response.action}</td>
                                    <td class="amount">$${parseFloat(response.reward).toFixed(2)}</td>
                                    <td>${new Date().toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric' })}</td>
                                `;
                                activityTableBody.insertBefore(newRow, activityTableBody.firstChild);
                                // Limit to 5 rows
                                if (activityTableBody.children.length > 5) {
                                    activityTableBody.removeChild(activityTableBody.lastChild);
                                }
                            }

                            // Load next video
                            loadNextVideo();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error || 'Failed to record video watch.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'An error occurred while tracking video watch.'
                        });
                    }
                });
            });
        }

        // Function to load next random video via AJAX
        function loadNextVideo() {
            $.ajax({
                url: 'get_random_video.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data) {
                        videoPlayer.src = data.url;
                        videoPlayer.setAttribute('data-video-id', data.id);
                        document.getElementById('video-reward').innerHTML = `Earn <span>$${parseFloat(data.reward).toFixed(2)}</span> by watching <span>${data.title}</span>`;
                        videoPlayer.load();
                        videoPlayer.play();
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'No More Videos',
                            text: 'No more videos available at the moment.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Failed to load next video.'
                    });
                }
            });
        }

        // Withdrawal Notifications
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

        // Gradient Animation (optimized with requestAnimationFrame for better performance)
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
