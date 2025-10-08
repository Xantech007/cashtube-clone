<?php
// privacy.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Read Task Tube's Privacy Policy to understand how we collect, use, and protect your personal information.">
    <meta name="keywords" content="Task Tube, privacy policy, data protection, user information">
    <meta name="author" content="Task Tube">
    <title>Task Tube - Privacy Policy</title>
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

        .privacy-content {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        .privacy-content h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .privacy-content h2 {
            font-size: 20px;
            font-weight: 500;
            color: #333;
            margin: 20px 0 10px;
        }

        .privacy-content p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .privacy-content p span {
            color: #6e44ff;
            font-weight: 500;
        }

        .privacy-content a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .privacy-content a:hover {
            color: #ff69b4;
            text-decoration: underline;
        }

        .back-link {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
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
            text-decoration: none;
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
            text-decoration: none;
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

            .privacy-content {
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

            .privacy-content {
                padding: 20px;
                margin: 0 20px;
            }

            .privacy-content h1 {
                font-size: 24px;
            }

            .privacy-content h2 {
                font-size: 18px;
            }

            .privacy-content p {
                font-size: 14px;
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

            .privacy-content {
                padding: 15px;
                margin: 0 15px;
            }

            .privacy-content h1 {
                font-size: 22px;
            }

            .privacy-content h2 {
                font-size: 16px;
            }

            .privacy-content p {
                font-size: 13px;
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
        <h1>Privacy Policy</h1>
        <p>Learn how Task Tube collects, uses, and protects your personal information to ensure a secure experience.</p>
    </section>

    <!-- Privacy Content -->
    <div class="index-container">
        <h2 class="section-title">Our Privacy Policy</h2>
        <div class="privacy-content">
            <h1>Privacy Policy</h1>
            <p>Welcome to <span>Task Tube</span>. We are committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your personal information when you use our website and services.</p>

            <h2>1. Information We Collect</h2>
            <p>We may collect personal information such as your email address and 5-digit passcode when you register or sign in. We also collect usage data, such as browsing activity, to improve our services.</p>

            <h2>2. How We Use Your Information</h2>
            <p>Your information is used to provide and improve Task Tube services, process account activities, and communicate with you. We may use data for analytics to enhance user experience.</p>

            <h2>3. Cookies and Tracking</h2>
            <p>We use cookies to track user activity and improve functionality. You can manage cookie preferences through your browser settings.</p>

            <h2>4. Data Sharing</h2>
            <p>We do not sell or share your personal information with third parties, except as required by law or to provide our services (e.g., with trusted service providers).</p>

            <h2>5. Data Security</h2>
            <p>We implement reasonable security measures to protect your data. However, no system is completely secure, and you share information at your own risk.</p>

            <h2>6. Your Rights</h2>
            <p>You may request access, correction, or deletion of your personal information by contacting us via our <a href="contact.php">Contact page</a>.</p>

            <h2>7. Changes to This Policy</h2>
            <p>We may update this Privacy Policy periodically. Changes will be posted on this page, and continued use of Task Tube constitutes acceptance.</p>

            <h2>8. Contact Us</h2>
            <p>For questions about this Privacy Policy, please visit our <a href="contact.php">Contact page</a>.</p>

            <p class="back-link">Return to <a href="index.php">Home</a></p>
        </div>
    </div>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <h2>Join Task Tube Today</h2>
        <a href="register.php" class="btn" onclick="console.log('CTA button clicked')">Get Started</a>
    </section>

    <!-- Notice Popup -->
    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Welcome to Task Tube</h2>
        <p>Review our Privacy Policy to understand how we protect your data. Ready to start earning?</p>
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
            return localStorage.getItem('noticeShownPrivacy');
        }

        function setNoticeShown() {
            localStorage.setItem('noticeShownPrivacy', true);
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
