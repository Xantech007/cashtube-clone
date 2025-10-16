<?php
session_start();
require_once '../database/conn.php';

// Set time zone to WAT
date_default_timezone_set('Africa/Lagos');

// Check if user is logged in and upgraded
if (!isset($_SESSION['user_id']) || !isset($_SESSION['upgrade_status']) || $_SESSION['upgrade_status'] !== 'upgraded') {
    header('Location: ../signin.php');
    exit;
}

// Fetch user details
try {
    $stmt = $pdo->prepare("SELECT name, country FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: ../signin.php?error=user_not_found');
        exit;
    }
    $username = htmlspecialchars($user['name']);
    $user_country = htmlspecialchars($user['country']);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage(), 3, '../debug.log');
    header('Location: ../error.php');
    exit;
}

// Fetch available currencies and user balances
try {
    $stmt = $pdo->prepare("SELECT currency, balance FROM user_balances WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT DISTINCT from_currency, to_currency FROM currency_rates");
    $stmt->execute();
    $currency_pairs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT dash_currency FROM region_settings WHERE country = ?");
    $stmt->execute([$user_country]);
    $dash_currency = $stmt->fetchColumn() ?: '$';
} catch (PDOException $e) {
    error_log('Currency fetch error: ' . $e->getMessage(), 3, '../debug.log');
    $error = 'Failed to load currencies. Please try again later.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_currency = $_POST['from_currency'] ?? '';
    $to_currency = $_POST['to_currency'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    
    if ($amount <= 0) {
        $error = 'Amount must be greater than 0.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Fetch exchange rate
            $stmt = $pdo->prepare("SELECT exchange_rate FROM currency_rates WHERE from_currency = ? AND to_currency = ?");
            $stmt->execute([$from_currency, $to_currency]);
            $exchange_rate = $stmt->fetchColumn();
            
            if (!$exchange_rate) {
                throw new Exception('Invalid currency pair.');
            }
            
            // Check balance
            $stmt = $pdo->prepare("SELECT balance FROM user_balances WHERE user_id = ? AND currency = ?");
            $stmt->execute([$_SESSION['user_id'], $from_currency]);
            $current_balance = $stmt->fetchColumn();
            
            if ($current_balance < $amount) {
                throw new Exception('Insufficient balance.');
            }
            
            // Update balances
            $stmt = $pdo->prepare("UPDATE user_balances SET balance = balance - ? WHERE user_id = ? AND currency = ?");
            $stmt->execute([$amount, $_SESSION['user_id'], $from_currency]);
            
            $converted_amount = $amount * $exchange_rate;
            $stmt = $pdo->prepare("
                INSERT INTO user_balances (user_id, currency, balance) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE balance = balance + ?
            ");
            $stmt->execute([$_SESSION['user_id'], $to_currency, $converted_amount, $converted_amount]);
            
            // Log transaction
            $stmt = $pdo->prepare("
                INSERT INTO transactions (user_id, type, amount, currency, description, created_at)
                VALUES (?, 'currency_exchange', ?, ?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $converted_amount, $to_currency, "Exchanged $amount $from_currency to $converted_amount $to_currency"]);
            
            $pdo->commit();
            header('Location: home.php?success=Currency+exchanged+successfully');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Currency exchange error: ' . $e->getMessage(), 3, '../debug.log');
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Exchange your crypto balance between different currencies." />
    <meta name="keywords" content="Cash Tube, currency exchange, cryptocurrency" />
    <meta name="author" content="Cash Tube" />
    <title>Currency Exchange | Cash Tube</title>
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
            content: '💱';
            font-size: 1.2rem;
            margin-right: 8px;
        }

        .balance-list {
            margin-bottom: 24px;
        }

        .balance-list h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .balance-list ul {
            list-style-type: none;
            padding-left: 0;
        }

        .balance-list li {
            font-size: 16px;
            margin-bottom: 8px;
            color: var(--subtext-color);
        }

        .balance-list li span {
            color: var(--text-color);
            font-weight: 600;
        }

        .input-container {
            position: relative;
            margin-bottom: 28px;
        }

        .input-container select,
        .input-container input {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--card-bg);
            color: var(--text-color);
            outline: none;
            transition: border-color 0.3s ease;
        }

        .input-container select:focus,
        .input-container input:focus {
            border-color: var(--accent-color);
        }

        .input-container label {
            position: absolute;
            top: -10px;
            left: 12px;
            font-size: 12px;
            color: var(--subtext-color);
            background: var(--card-bg);
            padding: 0 4px;
            pointer-events: none;
            transition: all 0.3s ease;
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
            color: red;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
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
            content: '💱';
            font-size: 1.2rem;
            margin-right: 12px;
            color: var(--accent-color);
        }

        .notification.error::before {
            content: '⚠️';
        }

        .notification span {
            font-size: 14px;
            font-weight: 500;
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
                    <h1>Currency Exchange</h1>
                    <p>Convert your balance between currencies</p>
                </div>
            </div>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">Toggle Dark Mode</button>
        </div>

        <div class="form-card">
            <h2>Exchange Currency</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <div class="balance-list">
                <h3>Your Balances</h3>
                <ul>
                    <?php foreach ($balances as $balance): ?>
                        <li><?php echo htmlspecialchars($balance['currency']); ?>: <span><?php echo number_format($balance['balance'], 2); ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <form action="currency_exchange.php" method="POST">
                <div class="input-container">
                    <select id="from_currency" name="from_currency" required>
                        <option value="" disabled selected>Select currency</option>
                        <?php foreach ($balances as $balance): ?>
                            <option value="<?php echo htmlspecialchars($balance['currency']); ?>"><?php echo htmlspecialchars($balance['currency']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="from_currency">From Currency</label>
                </div>
                <div class="input-container">
                    <select id="to_currency" name="to_currency" required>
                        <option value="" disabled selected>Select currency</option>
                        <?php foreach ($currency_pairs as $pair): ?>
                            <option value="<?php echo htmlspecialchars($pair['to_currency']); ?>"><?php echo htmlspecialchars($pair['to_currency']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="to_currency">To Currency</label>
                </div>
                <div class="input-container">
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder=" ">
                    <label for="amount">Amount (<?php echo $dash_currency; ?>)</label>
                </div>
                <button type="submit" class="submit-btn">Exchange</button>
            </form>
            <p style="text-align: center; margin-top: 20px;"><a href="home.php">Return to Dashboard</a></p>
        </div>

        <div id="notificationContainer"></div>
    </div>

    <div class="bottom-menu" role="navigation">
        <a href="home.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="history.php">History</a>
        <a href="support.php">Support</a>
        <button id="logoutBtn" aria-label="Log out">Logout</button>
    </div>

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

        // Notification Handling
        const notificationContainer = document.getElementById('notificationContainer');
        function fetchNotifications() {
            $.ajax({
                url: 'fetch_notifications.php',
                type: 'GET',
                dataType: 'json',
                success: function(notifications) {
                    notificationContainer.innerHTML = '';
                    notifications.forEach((message, index) => {
                        const notification = document.createElement('div');
                        notification.className = `notification ${message.type || 'success'}`;
                        notification.setAttribute('role', 'alert');
                        notification.innerHTML = `<span>${message.text}</span>`;
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
    </script>
</body>
</html>
