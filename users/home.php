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
    $stmt = $pdo->prepare("SELECT username, balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        // Log error and redirect to sign-in if user not found
        error_log('User not found for ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        session_destroy();
        header('Location: ../signin.php');
        exit;
    }
    $username = htmlspecialchars($user['username']);
    $balance = number_format($user['balance'], 2);
} catch (PDOException $e) {
    // Log error and redirect to sign-in
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
            padding: 80px 20px 120px;
            transition: all 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        /* Page Header */
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
            margin: 24px 0;
            box-shadow: 0 6px 16px var(--shadow-color);
            animation: slideIn 0.5s ease-out 0.3s backwards;
        }

        .earnings-summary h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .earnings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .earnings-item {
            text-align: center;
        }

        .earnings-item h3 {
            font-size: 18px;
            font-weight: 500;
            color: var(--subtext-color);
        }

        .earnings-item p {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-color);
            margin-top: 8px;
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

        .video-section iframe {
            border-radius: 16px;
            width: 100%;
            max-width: 640px;
            height: 360px;
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

        /* Recent Activity */
        .activity-section {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            margin: 24px 0;
            box-shadow: 0 6px 16px var(--shadow-color);
            animation: slideIn 0.5s ease-out 0.7s backwards;
        }

        .activity-section h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 16px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item p {
            font-size: 16px;
            color: var(--text-color);
        }

        .activity-item span {
            font-size: 14px;
            color: var(--subtext-color);
        }

        /* FAQ Section */
        .faq-section {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            margin: 24px 0;
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
            color: #22c55e;
            text-decoration: none;
        }

        .faq-item a:hover {
            color: #16a34a;
            text-decoration: underline;
        }

        /* Withdrawal Notifications */
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
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 16px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .balance-card h2 {
                font-size: 30px;
            }

            .video-section h1 {
                font-size: 26px;
            }

            .video-section iframe {
                height: 280px;
            }

            .form-card,
            .earnings-summary,
            .activity-section,
            .faq-section {
                padding: 20px;
            }

            .notification {
                max-width: 250px;
                right: 10px;
                top: 70px;
            }

            .earnings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div id="gradient"></div>
    <div class="container" role="main">
        <div class="page-header">
            <div style="display: flex; align-items: center;">
                <img src="img/top.png" alt="Task Tube Logo" aria-label="Task Tube Logo">
                <div class="header-text">
                    <h1>Hello, <?php echo $username; ?>!</h1>
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
            <div class="earnings-grid">
                <div class="earnings-item">
                    <h3>Total Earned</h3>
                    <p>$<?php echo number_format($balance, 2); ?></p>
                </div>
                <div class="earnings-item">
                    <h3>Videos Watched</h3>
                    <p><?php echo rand(10, 100); ?></p>
                </div>
                <div class="earnings-item">
                    <h3>Pending Withdrawals</h3>
                    <p>$<?php echo number_format(rand(0, 50), 2); ?></p>
                </div>
            </div>
        </div>

        <div class="video-section">
            <h1>Watch Videos to Earn Crypto</h1>
            <iframe src="videos/video1.mp4" title="Task Tube Video" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen></iframe>
            <h4>The more videos you watch, the more your <span>crypto balance</span> increases</h4>
        </div>

        <div class="form-card">
            <h2>Withdraw Crypto Funds</h2>
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
            <ul class="activity-list">
                <?php
                $activities = [
                    ['action' => 'Watched video', 'amount' => '+$1.00', 'time' => '5 min ago'],
                    ['action' => 'Withdrew funds', 'amount' => '-$50.00', 'time' => '1 hr ago'],
                    ['action' => 'Watched video', 'amount' => '+$1.50', 'time' => '2 hrs ago']
                ];
                foreach ($activities as $activity) {
                    echo '<li class="activity-item">';
                    echo '<p>' . htmlspecialchars($activity['action']) . '</p>';
                    echo '<span>' . htmlspecialchars($activity['amount']) . ' • ' . htmlspecialchars($activity['time']) . '</span>';
                    echo '</li>';
                }
                ?>
            </ul>
        </div>

        <div class="faq-section">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-item">
                <h3>How do I earn crypto on Task Tube?</h3>
                <p>Watch video ads to earn crypto rewards. The more videos you watch, the higher your earnings!</p>
            </div>
            <div class="faq-item">
                <h3>What are the withdrawal options?</h3>
                <p>You can withdraw via Cryptocurrency, Cash App, or Bank Transfer. Ensure your details are correct to avoid delays.</p>
            </div>
            <div class="faq-item">
                <h3>Is my data secure?</h3>
                <p>We use industry-standard encryption to protect your data. See our <a href="../privacy.php">Privacy Policy</a> for details.</p>
            </div>
        </div>

        <div id="notificationContainer"></div>
    </div>

    <div class="bottom-menu" role="navigation">
        <a href="home.php" class="active">Home</a>
        <a href="profile.php">Profile</a>
        <a href="history.php">History</a>
        <a href="support.php">Support</a>
        <a href="../about.php">About</a>
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

        // Withdrawal Method Logic
        const withdrawalMethod = document.getElementById('withdrawalMethod');
        const cryptoFields = document.getElementById('cryptoFields');
        const cashappFields = document.getElementById('cashappFields');
        const bankFields = document.getElementById('bankFields');
        const accountNumberField = document.getElementById('accountNumberField');
        const routingNumberField = document.getElementById('routingNumberField');

        withdrawalMethod.addEventListener('change', () => {
            cryptoFields.style.display = 'none';
            cashappFields.style.display = 'none';
            bankFields.style.display = 'none';
            accountNumberField.style.display = 'none';
            routingNumberField.style.display = 'none';

            if (withdrawalMethod.value === 'crypto') {
                cryptoFields.style.display = 'block';
            } else if (withdrawalMethod.value === 'cashapp') {
                cashappFields.style.display = 'block';
            } else if (withdrawalMethod.value === 'bank') {
                bankFields.style.display = 'block';
                accountNumberField.style.display = 'block';
                routingNumberField.style.display = 'block';
            }
        });

        // Form Submission
        document.getElementById('fundForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const withdrawalMethodValue = document.getElementById('withdrawalMethod').value;
            const amount = parseFloat(document.getElementById('amount').value);
            const balance = parseFloat(document.getElementById('balance').textContent);

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

            let withdrawalData = { method: withdrawalMethodValue, amount: amount };
            if (withdrawalMethodValue === 'crypto') {
                withdrawalData.cryptoAddress = document.getElementById('cryptoAddress').value;
                if (!withdrawalData.cryptoAddress) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Wallet Address',
                        text: 'Please enter a valid crypto wallet address.'
                    });
                    return;
                }
            } else if (withdrawalMethodValue === 'cashapp') {
                withdrawalData.cashappTag = document.getElementById('cashappTag').value;
                if (!withdrawalData.cashappTag) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Cashtag',
                        text: 'Please enter a valid Cash App $Cashtag.'
                    });
                    return;
                }
            } else if (withdrawalMethodValue === 'bank') {
                withdrawalData.bankName = document.getElementById('bankName').value;
                withdrawalData.accountNumber = document.getElementById('accountNumber').value;
                withdrawalData.routingNumber = document.getElementById('routingNumber').value;
                if (!withdrawalData.bankName || !withdrawalData.accountNumber || !withdrawalData.routingNumber) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Bank Details',
                        text: 'Please complete all bank details.'
                    });
                    return;
                }
            }

            $.ajax({
                url: 'process_withdrawal.php',
                type: 'POST',
                data: withdrawalData,
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
                            window.location.href = 'next-page.php';
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

        // Withdrawal Notifications
        const notificationContainer = document.getElementById('notificationContainer');
        const notifications = [
            'John D. withdrew $50 in USDT!',
            'Sarah K. cashed out $75 via Cash App!',
            'Mike T. withdrew $30 via Bank Transfer!',
            'Emma L. received $100 in Bitcoin!',
            'Alex P. cashed out $45 in Ethereum!'
        ];

        function showNotification() {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.setAttribute('role', 'alert');
            const randomMessage = notifications[Math.floor(Math.random() * notifications.length)];
            notification.innerHTML = `<span>${randomMessage}</span>`;
            notificationContainer.appendChild(notification);

            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach((notif, index) => {
                notif.style.top = `${80 + index * 80}px`;
            });

            setTimeout(() => notification.remove(), 3500);
        }

        showNotification();
        setInterval(showNotification, 20000);

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
