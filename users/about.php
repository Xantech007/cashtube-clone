<?php
// users/about.php
session_start();
require_once '../database/conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        error_log('User not found for ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        session_destroy();
        header('Location: ../signin.php');
        exit;
    }
    $username = htmlspecialchars($user['name']);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage(), 3, '../debug.log');
    session_destroy();
    header('Location: ../signin.php?error=database');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about Task Tube, the platform that lets you earn money by watching video ads.">
    <meta name="keywords" content="Task Tube, about, cryptocurrency, earn online, passive income">
    <meta name="author" content="Task Tube">
    <title>About | Task Tube</title>
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

        .about-section {
            width: 100%;
            max-width: 1200px;
            margin: 24px auto;
            padding: 28px;
            box-sizing: border-box;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 0;
            animation: slideIn 0.5s ease-out;
        }

        .page-header img {
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

        .about-section {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 6px 16px var(--shadow-color);
            animation: slideIn 0.5s ease-out 0.2s backwards;
        }

        .about-section h2 {
            font-size: 24px;
            font-weight: 600;
            margin: 20px 0 15px;
            color: var(--accent-color);
        }

        .about-section p {
            font-size: 16px;
            color: var(--subtext-color);
            line-height: 1.8;
            margin-bottom: 20px;
            text-align: left;
        }

        .about-section ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
            text-align: left;
        }

        .about-section ul li {
            font-size: 16px;
            color: var(--subtext-color);
            line-height: 1.8;
            margin-bottom: 10px;
            position: relative;
            padding-left: 25px;
        }

        .about-section ul li::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--accent-color);
            position: absolute;
            left: 0;
            top: 2px;
        }

        .notification {
            position: fixed;
            top: 80px;
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

        .bottom-menu a {
            color: var(--menu-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 10px 18px;
            transition: color 0.3s ease;
        }

        .bottom-menu a.active,
        .bottom-menu a:hover {
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
            .about-section {
                width: 100%;
                padding: 20px 12px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .about-section h2 {
                font-size: 20px;
            }

            .notification {
                max-width: 250px;
                right: 10px;
                top: 70px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div id="gradient"></div>
    <div class="main-content">
        <div class="container" role="main">
            <div class="page-header">
                <div style="display: flex; align-items: center;">
                    <img src="img/top.png" alt="Task Tube Logo" aria-label="Task Tube Logo">
                    <div class="header-text">
                        <h1>About Task Tube, <?php echo $username; ?>!</h1>
                        <p>Learn about our mission and how we help you earn.</p>
                    </div>
                </div>
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">Toggle Dark Mode</button>
            </div>

            <div class="about-section">
                <h2>Welcome to Task Tube</h2>
                <p>
                    Task Tube is an innovative platform designed to transform how you spend your time online. Instead of scrolling through social media, Task Tube empowers you to earn real money in USD by watching video advertisements directly from your smartphone or computer. Our mission is to make online earning accessible, seamless, and rewarding for everyone, anywhere in the world.
                </p>
                <p>
                    Built on a secure, crypto-powered rewards system, Task Tube allows users to earn up to $1,000 daily with minimal data usage. Whether you’re looking to supplement your income or explore a new way to earn, Task Tube offers a straightforward and engaging opportunity to get paid for your time and attention.
                </p>

                <h2>Our Mission</h2>
                <p>
                    At Task Tube, we believe that everyone deserves the chance to earn money effortlessly. Our mission is to create a user-friendly platform that connects advertisers with viewers, rewarding you for engaging with content you already enjoy. By leveraging blockchain technology, we ensure secure, transparent, and instant reward distribution, making Task Tube a trusted choice for online earners.
                </p>

                <h2>How It Works</h2>
                <p>
                    Getting started with Task Tube is simple:
                </p>
                <ul>
                    <li><strong>Watch Ads:</strong> Browse and watch video advertisements from our partners at your convenience.</li>
                    <li><strong>Earn Rewards:</strong> Get paid in USD for each ad you watch, with earnings credited instantly to your account.</li>
                    <li><strong>Withdraw Earnings:</strong> Cash out your rewards securely using our crypto-powered payment system.</li>
                </ul>

                <h2>Why Choose Task Tube?</h2>
                <p>
                    Task Tube stands out as a leading platform for earning online due to its unique features and user-centric approach:
                </p>
                <ul>
                    <li><strong>Effortless Earning:</strong> No special skills or equipment needed—just your smartphone and an internet connection.</li>
                    <li><strong>High Rewards:</strong> Unlock the potential to earn up to $1,000 daily by watching ads at your own pace.</li>
                    <li><strong>Low Data Usage:</strong> Optimized for minimal data consumption, making it accessible even in low-bandwidth areas.</li>
                    <li><strong>Secure Transactions:</strong> Our crypto-powered system ensures fast, safe, and transparent payouts.</li>
                    <li><strong>Global Access:</strong> Available to users worldwide, Task Tube lets you earn from anywhere, anytime.</li>
                </ul>
                <p>
                    Thank you for being part of the Task Tube community. Continue earning by watching ads and managing your rewards from your dashboard!
                </p>
            </div>

            <div id="notificationContainer"></div>
        </div>
    </div>

    <div class="bottom-menu" role="navigation">
        <a href="home.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="history.php">History</a>
        <a href="support.php">Support</a>
        <a href="../about.php" class="active">About</a>
    </div>

    <?php include 'inc/footer.php'; ?>

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

        // Menu Interactions
        const menuItems = document.querySelectorAll('.bottom-menu a');
        menuItems.forEach((item) => {
            item.addEventListener('click', () => {
                menuItems.forEach((menuItem) => menuItem.classList.remove('active'));
                item.classList.add('active');
            });
        });

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
                        notification.style.top = `${80 + index * 80}px`;
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
            $('#gradient').css({
                background: `linear-gradient(135deg, ${color1}, ${color2})`
            });
            step += gradientSpeed;
            if (step >= 1) {
                step %= 1;
                colorIndices[0] = colorIndices[1];
                colorIndices[2] = colorIndices[3];
                colorIndices[1] = (colorIndices[1] + Math.floor(1 + Math.random() * (colors.length - 1))) % colors.length;
                colorIndices[3] = (colorIndices[3] + Math.floor(1 + Math.random() * (colors.length - 1))) % colors.length;
            }
        }

        setInterval(updateGradient, 10);

        // Context Menu Disable
        document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
        });
    </script>
</body>
</html>
