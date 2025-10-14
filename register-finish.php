<?php
// register-finish.php
require_once 'database/conn.php';

// Initialize variables
$data = [];
$error = '';

// Get email from query string
$email = $_GET['email'] ?? '';
if ($email) {
    try {
        // Retrieve user data from database
        $stmt = $pdo->prepare("SELECT name, email, gender, passcode FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        if (!$data) {
            $error = "No user found with email: " . htmlspecialchars($email);
            file_put_contents('debug.log', 'No user found: ' . $email . "\n", FILE_APPEND);
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        file_put_contents('debug.log', 'Database error: ' . $e->getMessage() . "\n", FILE_APPEND);
    }
} else {
    $error = "No email provided.";
    file_put_contents('debug.log', 'No email in query string' . "\n", FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Complete your Task Tube registration and get your unique passcode to start earning by watching video ads.">
    <meta name="keywords" content="Task Tube, registration complete, earn money, watch ads, passcode">
    <meta name="author" content="Task Tube">
    <title>Task Tube - Registration Complete</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
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

        .finish-content {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .finish-content h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .finish-content p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .finish-content p span {
            color: #6e44ff;
            font-weight: 500;
        }

        .passcode-box {
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px;
            font-size: 24px;
            font-weight: 500;
            text-align: center;
            margin-bottom: 20px;
            user-select: all;
            cursor: pointer; /* Indicates the element is clickable */
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .passcode-box:hover {
            background: #e8e8e8; /* Subtle hover effect */
            border-color: #6e44ff; /* Matches theme color */
        }

        .error-message {
            color: #ff4d94;
            font-size: 16px;
            margin-bottom: 20px;
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

        .proceed-btn {
            background: #6e44ff;
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 15px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .proceed-btn:hover {
            background: #5a00b5;
            transform: translateY(-2px);
        }

        .error-link {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .error-link:hover {
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

            .finish-content {
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

            .finish-content {
                padding: 20px;
                margin: 0 20px;
            }

            .passcode-box {
                font-size: 20px;
            }

            .proceed-btn {
                padding: 12px;
                font-size: 16px;
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

            .finish-content {
                padding: 15px;
                margin: 0 15px;
            }

            .passcode-box {
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
        <h1>Registration Complete</h1>
        <p>Welcome to Task Tube! Your account is ready—use your unique passcode to log in and start earning.</p>
    </section>

    <!-- Finish Content -->
    <div class="index-container">
        <h2 class="section-title">Welcome to Task Tube</h2>
        <div class="finish-content">
            <?php if ($error): ?>
                <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
                <a href="register.php" class="btn error-link">Try Again</a>
            <?php else: ?>
                <h1>Welcome, <span><?php echo htmlspecialchars($data['name']); ?>!</span></h1>
                <p>Your registration is complete.</p>
                <p>Email: <span><?php echo htmlspecialchars($data['email']); ?></span></p>
                <p>Gender: <span><?php echo htmlspecialchars($data['gender']); ?></span></p>
                <p>Your unique passcode is:</p>
                <div class="passcode-box"><?php echo htmlspecialchars($data['passcode']); ?></div>
                <p>Please copy this passcode and keep it safe. You will need it to log in.</p>
                <a href="login.php" class="proceed-btn btn">Proceed to Login</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <h2>Start Earning Now</h2>
        <a href="login.php" class="btn" onclick="console.log('CTA button clicked')">Log In to Begin</a>
    </section>

    <!-- Notice Popup -->
    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Welcome to Task Tube</h2>
        <p>Your account is ready! Log in with your passcode to start earning by watching video ads.</p>
        <a href="login.php" class="btn" onclick="console.log('Notice button clicked')">Log In Now</a>
    </div>

    <?php include 'inc/footer.php'; ?>

    <!-- LiveChat Script -->
    <script>
        window.__lc = window.__lc || {};
        window.__lc.license = 15808029;
        (function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0,n.type="text/javascript",n.src="https://cdn.livechatinc.com/tracking.js",t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice))
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

            // Tap to Copy Passcode
            const passcodeBox = document.querySelector('.passcode-box');
            if (passcodeBox) {
                passcodeBox.addEventListener('click', function() {
                    const passcode = passcodeBox.textContent.trim();
                    if (navigator.clipboard && window.isSecureContext) {
                        // Use Clipboard API for secure contexts
                        navigator.clipboard.writeText(passcode).then(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Passcode Copied!',
                                text: 'Your passcode has been copied to the clipboard.',
                                showConfirmButton: false,
                                timer: 1500,
                                toast: true,
                                position: 'top-end',
                                background: '#fff',
                                color: '#333',
                            });
                        }).catch(err => {
                            console.error('Failed to copy passcode: ', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Copy Failed',
                                text: 'Unable to copy passcode. Please select and copy manually.',
                                confirmButtonColor: '#6e44ff',
                            });
                        });
                    } else {
                        // Fallback for non-secure contexts or older browsers
                        const textarea = document.createElement('textarea');
                        textarea.value = passcode;
                        document.body.appendChild(textarea);
                        textarea.select();
                        try {
                            document.execCommand('copy');
                            Swal.fire({
                                icon: 'success',
                                title: 'Passcode Copied!',
                                text: 'Your passcode has been copied to the clipboard.',
                                showConfirmButton: false,
                                timer: 1500,
                                toast: true,
                                position: 'top-end',
                                background: '#fff',
                                color: '#333',
                            });
                        } catch (err) {
                            console.error('Fallback copy failed: ', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Copy Failed',
                                text: 'Unable to copy passcode. Please select and copy manually.',
                                confirmButtonColor: '#6e44ff',
                            });
                        }
                        document.body.removeChild(textarea);
                    }
                });
            }
        });

        // Notice Popup
        function isNoticeShown() {
            return localStorage.getItem('noticeShownRegisterFinish');
        }

        function setNoticeShown() {
            localStorage.setItem('noticeShownRegisterFinish', true);
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

        // Prevent right-click only on non-link elements
        document.addEventListener('contextmenu', e => {
            if (!e.target.closest('a')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
