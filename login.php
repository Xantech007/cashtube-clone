<?php
// login.php
session_start(); // Start session for user authentication
require_once 'database/conn.php';

$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['passcode'])) {
    $passcode = trim($_POST['passcode']);
    
    if (strlen($passcode) === 5) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE passcode = ?");
            $stmt->execute([$passcode]);
            if ($stmt->fetchColumn() > 0) {
                $response['success'] = true;
                $_SESSION['passcode'] = $passcode; // Store passcode in session
            } else {
                $response['error'] = "Invalid passcode.";
                file_put_contents('debug.log', 'Invalid passcode: ' . $passcode . "\n", FILE_APPEND);
            }
        } catch (PDOException $e) {
            $response['error'] = "Database error: " . $e->getMessage();
            file_put_contents('debug.log', 'Database error: ' . $e->getMessage() . "\n", FILE_APPEND);
        }
    } else {
        $response['error'] = "Passcode must be 5 digits.";
        file_put_contents('debug.log', 'Invalid passcode length: ' . $passcode . "\n", FILE_APPEND);
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Log in to Task Tube with your 5-digit passcode to start earning by watching video ads.">
    <meta name="keywords" content="Task Tube, login, earn money, watch ads, passcode">
    <meta name="author" content="Task Tube">
    <title>Task Tube - Secure Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #333;
            padding-top: 80px; /* Matches header height */
            padding-bottom: 100px; /* Matches footer height */
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #6e44ff, #b5179e);
            color: #fff;
            text-align: center;
            padding: 100px 20px;
            position: relative;
            overflow: hidden;
            z-index: 10;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://source.unsplash.com/random/1920x1080/?technology') no-repeat center center/cover;
            opacity: 0.1;
            z-index: 0;
        }

        .hero-section h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .hero-section p {
            font-size: 18px;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto 30px;
            position: relative;
            z-index: 1;
        }

        /* Main Container */
        .index-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-title {
            font-size: 36px;
            font-weight: 600;
            color: #333;
            text-align: center;
            margin-bottom: 40px;
        }

        .login-content {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .login-content h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .login-content p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .login-content p span {
            color: #6e44ff;
            font-weight: 500;
        }

        #passcode {
            width: 100%;
            height: 50px;
            font-size: 24px;
            text-align: center;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        #passcode:focus {
            border-color: #6e44ff;
            box-shadow: 0 0 5px rgba(110, 68, 255, 0.3);
        }

        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(5, auto);
            gap: 10px;
            margin-bottom: 20px;
        }

        .key {
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            font-size: 18px;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .key:hover {
            background: #6e44ff;
            color: #fff;
            transform: scale(1.05);
        }

        .key.action {
            background: #6e44ff;
            color: #fff;
            border: none;
        }

        .key.action:hover {
            background: #5a00b5;
        }

        .key.zero {
            grid-column: 2 / 3;
            grid-row: 4 / 5;
        }

        .key.action.signup {
            grid-column: 1 / 2;
            grid-row: 5 / 6;
        }

        .key.action.clear {
            grid-column: 2 / 3;
            grid-row: 5 / 6;
        }

        .key.action.enter {
            grid-column: 3 / 4;
            grid-row: 5 / 6;
        }

        /* Button Styles */
        .btn {
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-block;
        }

        .key.action.signup a {
            color: #fff;
            text-decoration: none;
            display: block;
            width: 100%;
            height: 100%;
        }

        .signin-link {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }

        .signin-link a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .signin-link a:hover {
            color: #ff69b4;
            text-decoration: underline;
        }

        /* CTA Banner */
        .cta-banner {
            background: linear-gradient(135deg, #6e44ff, #b5179e);
            color: #fff;
            text-align: center;
            padding: 60px 20px;
            border-radius: 15px;
            margin: 40px 20px;
        }

        .cta-banner h2 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .cta-banner .btn {
            background-color: #fff;
            color: #6e44ff;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            transition: background-color 0.3s ease;
        }

        .cta-banner .btn:hover {
            background-color: #f0f0f0;
        }

        /* Notice Popup */
        .notice {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            display: none;
            z-index: 1002;
        }

        .notice h2 {
            font-size: 24px;
            color: #6e44ff;
            margin-bottom: 15px;
        }

        .notice p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
            text-align: center;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: #333;
        }

        .notice .btn {
            background-color: #6e44ff;
            color: #fff;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .notice .btn:hover {
            background-color: #5a00b5;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .hero-section h1 {
                font-size: 36px;
            }

            .hero-section p {
                font-size: 16px;
            }

            .section-title {
                font-size: 30px;
            }

            .login-content {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 70px;
                padding-bottom: 80px;
            }

            .hero-section {
                padding: 80px 20px;
            }

            .hero-section h1 {
                font-size: 32px;
            }

            .hero-section p {
                font-size: 15px;
            }

            .section-title {
                font-size: 28px;
            }

            .login-content {
                padding: 20px;
                margin: 0 20px;
            }

            .keypad {
                gap: 8px;
            }

            .key {
                padding: 10px;
                font-size: 16px;
            }

            #passcode {
                height: 45px;
                font-size: 20px;
            }

            .cta-banner h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding-top: 60px;
                padding-bottom: 60px;
            }

            .hero-section {
                padding: 60px 15px;
            }

            .hero-section h1 {
                font-size: 28px;
            }

            .hero-section p {
                font-size: 14px;
            }

            .section-title {
                font-size: 24px;
            }

            .login-content {
                padding: 15px;
                margin: 0 15px;
            }

            .keypad {
                gap: 6px;
            }

            .key {
                padding: 8px;
                font-size: 14px;
            }

            #passcode {
                height: 40px;
                font-size: 18px;
            }

            .cta-banner {
                padding: 40px 15px;
            }

            .cta-banner h2 {
                font-size: 24px;
            }

            .cta-banner .btn {
                padding: 12px 30px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1>Log In to Task Tube</h1>
        <p>Enter your 5-digit passcode to access your account and start earning by watching video ads.</p>
    </section>

    <!-- Login Form -->
    <div class="index-container">
        <h2 class="section-title">Secure Login</h2>
        <div class="login-content">
            <h1>Welcome to <span>Task Tube</span></h1>
            <p>Enter your 5-digit passcode</p>
            <input type="password" id="passcode" readonly class="input-field" aria-label="Passcode input">
            <div class="keypad">
                <div class="key">1</div>
                <div class="key">2</div>
                <div class="key">3</div>
                <div class="key">4</div>
                <div class="key">5</div>
                <div class="key">6</div>
                <div class="key">7</div>
                <div class="key">8</div>
                <div class="key">9</div>
                <div class="key zero">0</div>
                <div class="key action signup"><a href="register.php">Sign Up</a></div>
                <div class="key action clear" id="clear">Clear</div>
                <div class="key action enter" id="enter">Login</div>
            </div>
            <p class="signin-link">Already have an account? <a href="signin.php">Sign In</a></p>
        </div>
    </div>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <h2>Not Yet a Member?</h2>
        <a href="register.php" class="btn" onclick="console.log('CTA button clicked')">Join Task Tube Now</a>
    </section>

    <!-- Notice Popup -->
    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Log In to Task Tube</h2>
        <p>Use your 5-digit passcode to access your account and start earning. Don’t have one? Sign up today!</p>
        <a href="register.php" class="btn" onclick="console.log('Notice button clicked')">Sign Up Now</a>
    </div>

    <?php include 'inc/footer.php'; ?>

    <!-- LiveChat Script -->
    <script>
        window.__lc = window.__lc || {};
        window.__lc.license = 15808029;
        (function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0,n.type="text/javascript",n.src="https://cdn.livechat.com/tracking.js",t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice))
    </script>
    <noscript><a href="https://www.livechat.com/chat-with/15808029/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechat.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a></noscript>

    <script>
        // Set Active Navbar Link
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname.split('/').pop();
            const links = document.querySelectorAll('.ham-menu ul li a');
            links.forEach(link => {
                if (link.getAttribute('href') === currentPath || (currentPath === '' && link.getAttribute('href') === 'index.php')) {
                    link.parentElement.classList.add('active');
                }
            });
        });

        // Notice Popup
        function isNoticeShown() {
            return localStorage.getItem('noticeShownLogin');
        }

        function setNoticeShown() {
            localStorage.setItem('noticeShownLogin', true);
        }

        function showNotice() {
            if (!isNoticeShown()) {
                const notice = document.getElementById('notice');
                setTimeout(() => {
                    notice.style.display = 'block';
                    setNoticeShown();
                }, 2000); // Match index.php timing
            }
        }

        function closeNotice() {
            document.getElementById('notice').style.display = 'none';
            setNoticeShown();
        }

        window.addEventListener('load', showNotice);

        // Passcode Logic
        const passcodeInput = document.getElementById("passcode");
        const keys = document.querySelectorAll(".key:not(.action)");
        const clearButton = document.getElementById("clear");
        const enterButton = document.getElementById("enter");

        keys.forEach(key => {
            key.addEventListener("click", () => {
                if (passcodeInput.value.length < 5) {
                    passcodeInput.value += key.textContent;
                }
            });
        });

        clearButton.addEventListener("click", () => {
            passcodeInput.value = "";
        });

        enterButton.addEventListener("click", validatePasscode);

        passcodeInput.addEventListener("input", () => {
            if (passcodeInput.value.length === 5) {
                validatePasscode();
            }
        });

        function validatePasscode() {
            const passcode = passcodeInput.value;
            if (passcode.length !== 5) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter a 5-digit passcode.',
                });
                return;
            }

            $.ajax({
                url: './login.php',
                type: 'POST',
                data: { passcode: passcode },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Good job!',
                            text: 'Login successful',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'users/home.php'; // Redirect to users/home.php
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.error || 'Invalid Passcode!',
                            footer: '<a href="register.php">Sign Up</a>'
                        });
                        passcodeInput.value = "";
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Server error. Please try again.',
                        footer: '<a href="register.php">Sign Up</a>'
                    });
                    passcodeInput.value = "";
                }
            });
        }

        // Prevent right-click only on non-link elements
        document.addEventListener('contextmenu', e => {
            if (!e.target.closest('a')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
