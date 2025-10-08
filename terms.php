<?php
// terms.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Read the Terms and Conditions for using Task Tube. Understand our policies for account registration, user conduct, and more.">
    <meta name="keywords" content="Task Tube, terms and conditions, user agreement, policies">
    <meta name="author" content="Task Tube">
    <title>Task Tube - Terms and Conditions</title>
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

        .terms-content {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        .terms-content h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .terms-content h2 {
            font-size: 20px;
            font-weight: 500;
            color: #333;
            margin: 20px 0 10px;
        }

        .terms-content p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .terms-content p span {
            color: #6e44ff;
            font-weight: 500;
        }

        .terms-content a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .terms-content a:hover {
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

            .terms-content {
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

            .terms-content {
                padding: 20px;
                margin: 0 20px;
            }

            .terms-content h1 {
                font-size: 24px;
            }

            .terms-content h2 {
                font-size: 18px;
            }

            .terms-content p {
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

            .terms-content {
                padding: 15px;
                margin: 0 15px;
            }

            .terms-content h1 {
                font-size: 22px;
            }

            .terms-content h2 {
                font-size: 16px;
            }

            .terms-content p {
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
        <h1>Terms and Conditions</h1>
        <p>Understand the rules and policies for using Task Tube to ensure a fair and secure experience.</p>
    </section>

    <!-- Terms Content -->
    <div class="index-container">
        <h2 class="section-title">Our Terms</h2>
        <div class="terms-content">
            <h1>Terms and Conditions</h1>
            <p>Welcome to <span>Task Tube</span>. By using our website and services, you agree to comply with and be bound by the following terms and conditions. Please read them carefully.</p>

            <h2>1. Acceptance of Terms</h2>
            <p>By accessing or using Task Tube, you agree to these Terms and Conditions and our <a href="privacy.php">Privacy Policy</a>. If you do not agree, please do not use our services.</p>

            <h2>2. Account Registration</h2>
            <p>To access certain features, you must register an account with a valid email and a 5-digit passcode. You are responsible for maintaining the confidentiality of your account credentials.</p>

            <h2>3. Use of Services</h2>
            <p>You agree to use Task Tube for lawful purposes only. You may not use our services to engage in any illegal activities or to violate the rights of others.</p>

            <h2>4. User Conduct</h2>
            <p>Do not attempt to hack, disrupt, or misuse our platform. Any unauthorized access or activity may result in account suspension or legal action.</p>

            <h2>5. Termination</h2>
            <p>We reserve the right to suspend or terminate your account if you violate these terms or engage in prohibited activities.</p>

            <h2>6. Changes to Terms</h2>
            <p>We may update these Terms and Conditions periodically. Continued use of Task Tube after changes constitutes acceptance of the new terms.</p>

            <h2>7. Contact Us</h2>
            <p>If you have questions about these terms, please contact us via our <a href="contact.php">Contact page</a>.</p>

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
        <p>Review our Terms and Conditions to understand how to use our platform responsibly. Ready to start earning?</p>
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
            return localStorage.getItem('noticeShownTerms');
        }

        function setNoticeShown() {
            localStorage.setItem('noticeShownTerms', true);
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
