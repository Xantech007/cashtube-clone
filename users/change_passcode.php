<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
session_start([
    'cookie_path' => '/',
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
]);

error_log('Session ID in change_passcode.php: ' . session_id() . ', User ID: ' . ($_SESSION['user_id'] ?? 'not set'), 3, '../debug.log');

if (!isset($_SESSION['user_id'])) {
    error_log('No user_id in session, redirecting to signin', 3, '../debug.log');
    header('Location: ../signin.php');
    ob_end_flush();
    exit;
}

try {
    require_once '../database/conn.php';
} catch (Exception $e) {
    error_log('Failed to include conn.php: ' . $e->getMessage(), 3, '../debug.log');
    echo 'Failed to connect to database. Check logs for details.';
    ob_end_flush();
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        error_log('User not found for ID: ' . $_SESSION['user_id'], 3, '../debug.log');
        session_destroy();
        header('Location: ../signin.php?error=user_not_found');
        ob_end_flush();
        exit;
    }
    $username = htmlspecialchars($user['name']);
} catch (PDOException $e) {
    error_log('Database error in change_passcode.php: ' . $e->getMessage(), 3, '../debug.log');
    if (file_exists('../error.php')) {
        include '../error.php';
    } else {
        echo 'Database error occurred: ' . htmlspecialchars($e->getMessage());
    }
    ob_end_flush();
    exit;
}

$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : null;
$error_message   = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Change your Task Tube account password.">
    <title>Change Password | Task Tube</title>
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
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:var(--bg-color); color:var(--text-color); min-height:100vh; padding-bottom:100px; transition:all .3s ease; }
        .container { max-width:1200px; margin:0 auto; padding:24px; position:relative; }
        .header { display:flex; align-items:center; justify-content:space-between; padding:24px 0; animation:slideIn .5s ease-out; }
        .header img { width:64px; height:64px; margin-right:16px; border-radius:8px; }
        .header-text h1 { font-size:26px; font-weight:700; }
        .header-text p { font-size:16px; color:var(--subtext-color); margin-top:4px; }
        .theme-toggle { background:var(--accent-color); color:#fff; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; font-size:14px; font-weight:500; transition:background .3s,transform .2s; }
        .theme-toggle:hover { background:var(--accent-hover); transform:scale(1.02); }
        .passcode-card { background:var(--card-bg); border-radius:16px; padding:28px; box-shadow:0 6px 16px var(--shadow-color); animation:slideIn .5s ease-out .3s backwards; }
        .passcode-card h2 { font-size:24px; font-weight:600; margin-bottom:20px; text-align:center; }
        .passcode-card h2::before { content:'Lock'; font-size:1.2rem; margin-right:8px; }
        .input-container { position:relative; margin-bottom:28px; }
        .input-container input { width:100%; padding:16px 8px; font-size:16px; border:none; border-bottom:2px solid var(--border-color); background:transparent; color:var(--text-color); outline:none; transition:border-color .3s; }
        .input-container input:focus { border-bottom-color:var(--accent-color); }
        .input-container label { position:absolute; top:16px; left:8px; font-size:16px; color:var(--subtext-color); pointer-events:none; transition:all .3s; }
        .input-container input:focus ~ label,
        .input-container input:not(:placeholder-shown) ~ label,
        .input-container input.has-value ~ label { top:-18px; left:0; font-size:12px; color:var(--accent-color); }
        .submit-btn { width:100%; padding:14px; background:var(--accent-color); color:#fff; font-size:16px; font-weight:600; border:none; border-radius:8px; cursor:pointer; transition:background .3s,transform .2s; }
        .submit-btn:hover { background:var(--accent-hover); transform:scale(1.02); }
        .notification { position:fixed; top:20px; right:20px; background:var(--card-bg); color:var(--text-color); padding:16px 24px; border-radius:12px; border:2px solid var(--accent-color); box-shadow:0 4px 12px var(--shadow-color),0 0 8px var(--accent-color); z-index:1000; display:flex; align-items:center; animation:slideInRight .5s,fadeOut .5s 3s forwards; max-width:300px; }
        .notification.error { border-color:#ef4444; }
        .notification.error::before { content:'Warning'; }
        .notification::before { content:'Lock'; font-size:1.2rem; margin-right:12px; color:var(--accent-color); }
        .bottom-menu { position:fixed; bottom:0; left:0; width:100%; background:var(--menu-bg); display:flex; justify-content:space-around; align-items:center; padding:14px 0; box-shadow:0 -2px 8px var(--shadow-color); }
        .bottom-menu a, .bottom-menu button { color:var(--menu-text); text-decoration:none; font-size:14px; font-weight:500; padding:10px 18px; background:none; border:none; cursor:pointer; }
        .bottom-menu a.active, .bottom-menu a:hover, .bottom-menu button:hover { color:var(--accent-color); }
        #gradient { position:fixed; top:0; left:0; width:100%; height:100%; z-index:-1; background:var(--gradient-bg); animation:gradientAnimation 10s ease infinite; }
        @keyframes slideIn { from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }
        @keyframes slideInRight { from {opacity:0; transform:translateX(100px);} to {opacity:1; transform:translateX(0);} }
        @keyframes fadeOut { to {opacity:0; transform:translateY(-20px);} }
        @keyframes gradientAnimation { 0%{background:linear-gradient(135deg,rgb(62,35,255),rgb(60,255,60))} 50%{background:linear-gradient(135deg,rgb(255,35,98),rgb(45,175,230))} 100%{background:linear-gradient(135deg,rgb(62,35,255),rgb(60,255,60))} }
        @media (max-width:768px) { .container{padding:16px;} .header-text h1{font-size:22px;} .passcode-card h2{font-size:20px;} }
    </style>
</head>
<body>
    <div id="gradient"></div>
    <div class="container" role="main">
        <div class="header">
            <div style="display:flex;align-items:center;">
                <img src="img/top.png" alt="Task Tube Logo">
                <div class="header-text">
                    <h1>Hello, <?php echo $username; ?>!</h1>
                    <p>Change your account password.</p>
                </div>
            </div>
            <button class="theme-toggle" id="themeToggle">Toggle Dark Mode</button>
        </div>

        <?php if ($success_message): ?>
            <div class="notification success" role="alert"><span><?php echo $success_message; ?></span></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="notification error" role="alert"><span><?php echo $error_message; ?></span></div>
        <?php endif; ?>

        <div class="passcode-card">
            <h2>Change Password</h2>
            <form id="passwordForm" action="process_change_password.php" method="POST">
                <div class="input-container">
                    <input type="password" id="old_password" name="old_password" placeholder=" " required>
                    <label for="old_password">Old Password</label>
                    <small style="color:var(--subtext-color);font-size:12px;">Enter your current password.</small>
                </div>

                <div class="input-container">
                    <input type="password" id="new_password" name="new_password" placeholder=" " required>
                    <label for="new_password">New Password</label>
                    <small style="color:var(--subtext-color);font-size:12px;">Create a new password you can remember.</small>
                </div>

                <div class="input-container">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder=" " required>
                    <label for="confirm_password">Confirm New Password</label>
                    <small style="color:var(--subtext-color);font-size:12px;">Re-enter your new password.</small>
                </div>

                <button type="submit" class="submit-btn">Change Password</button>
            </form>
        </div>
        <div id="notificationContainer"></div>
    </div>

    <div class="bottom-menu" role="navigation">
        <a href="home.php">Home</a>
        <a href="profile.php" class="active">Profile</a>
        <a href="history.php">History</a>
        <a href="support.php">Support</a>
        <button id="logoutBtn">Logout</button>
    </div>

    <script>
        // LiveChat Widget
        window.__lc = window.__lc || {};
        window.__lc.license = 15808029;
        (function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0;n.type="text/javascript";n.src="https://cdn.livechatinc.com/tracking.js";t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice));

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        if (localStorage.getItem('theme') === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
            themeToggle.textContent = 'Toggle Light Mode';
        }
        themeToggle.addEventListener('click', () => {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            document.body.setAttribute('data-theme', isDark ? 'light' : 'dark');
            themeToggle.textContent = isDark ? 'Toggle Dark Mode' : 'Toggle Light Mode';
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
        });

        // Label animation
        document.querySelectorAll('.input-container input').forEach(input => {
            const label = input.nextElementSibling;
            const update = () => {
                if (input.value !== '') input.classList.add('has-value');
                else input.classList.remove('has-value');
            };
            input.addEventListener('input', update);
            input.addEventListener('focus', () => label?.classList.add('active'));
            input.addEventListener('blur', update);
            update();
        });

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', () => {
            Swal.fire({
                title: 'Log out?',
                text: 'Are you sure?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#22c55e',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, log out'
            }).then(result => {
                if (result.isConfirmed) {
                    $.post('logout.php', {}, () => location.href = '../signin.php');
                }
            });
        });

        // UPDATED: Allow password ≥1 character
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const oldPassword = document.getElementById('old_password').value.trim();
            const newPassword = document.getElementById('new_password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();

            if (!oldPassword) {
                Swal.fire('Error', 'Please enter your current password.', 'error');
                return;
            }

            if (newPassword.length < 1) {
                Swal.fire('Error', 'New password must be at least 1 character long.', 'error');
                return;
            }

            if (newPassword !== confirmPassword) {
                Swal.fire('Error', 'New password and confirmation do not match.', 'error');
                return;
            }

            $.ajax({
                url: 'process_change_password.php',
                type: 'POST',
                data: { old_password: oldPassword, new_password: newPassword, confirm_password: confirmPassword },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Success!', 'Password updated successfully!', 'success').then(() => {
                            location.href = 'change_passcode.php?success=Password changed successfully';
                        });
                    } else {
                        Swal.fire('Error', res.error || 'Something went wrong.', 'error');
                    }
                },
                error: () => Swal.fire('Error', 'Server error. Try again.', 'error')
            });
        });

        // Notifications (if you have fetch_notifications.php)
        const notifContainer = document.getElementById('notificationContainer');
        function loadNotifs() {
            $.get('fetch_notifications.php', data => {
                notifContainer.innerHTML = '';
                (data || []).forEach((n, i) => {
                    const div = document.createElement('div');
                    div.className = `notification ${n.type || 'success'}`;
                    div.innerHTML = `<span>${n.text}</span>`;
                    div.style.top = `${20 + i*80}px`;
                    notifContainer.appendChild(div);
                    setTimeout(() => div.remove(), 4000);
                });
            });
        }
        loadNotifs();
        setInterval(loadNotifs, 20000);
    </script>
</body>
</html>
<?php ob_end_flush(); ?>
