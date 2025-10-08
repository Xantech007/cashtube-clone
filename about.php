<?php
// about.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about Task Tube, the crypto-powered platform that lets you earn money by watching video ads. Discover our mission and how it works.">
    <meta name="keywords" content="Task Tube, earn money online, watch ads, passive income, crypto earnings">
    <meta name="author" content="Task Tube">
    <title>Task Tube - About Us</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .about-content {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .about-content h2 {
            font-size: 24px;
            font-weight: 600;
            color: #6e44ff;
            margin: 30px 0 15px;
            text-align: left;
        }

        .about-content p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: left;
        }

        .about-content ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
            text-align: left;
        }

        .about-content ul li {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 10px;
            position: relative;
            padding-left: 30px;
        }

        .about-content ul li::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #6e44ff;
            position: absolute;
            left: 0;
            top: 2px;
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

        /* Button Styles */
        .signup-link .btn {
            background-color: #6e44ff;
            color: #fff;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .signup-link .btn:hover {
            background-color: #5a00b5;
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

            .about-content {
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

            .about-content {
                padding: 15px;
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
        <h1>About Task Tube</h1>
        <p>Discover how Task Tube empowers you to earn money by watching video ads. Learn about our mission and why thousands trust us!</p>
    </section>

    <!-- About Content -->
    <div class="index-container">
        <h2 class="section-title">Who We Are</h2>
        <div class="about-content">
            <h2>Welcome to Task Tube</h2>
            <p>
                Task Tube is an innovative platform designed to transform how you spend your time online. Instead of scrolling through social media, Task Tube empowers you to earn real money in USD by watching video advertisements directly from your smartphone or computer. Our mission is to make online earning accessible, seamless, and rewarding for everyone, anywhere in the world.
            </p>
            <p>
                Built on a secure, crypto-powered rewards system, Task Tube allows users to unlock earnings of up to $1,000 daily with minimal data usage. Whether you’re looking to supplement your income or explore a new way to earn, Task Tube offers a straightforward and engaging opportunity to get paid for your time and attention.
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
                <li><strong>Sign Up:</strong> Create your account in minutes with basic details and receive a unique 5-digit passcode.</li>
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
                Join thousands of users who are already turning their screen time into income with Task Tube. Whether you’re a student, professional, or simply looking for a side hustle, our platform offers a fun and rewarding way to make money online.
            </p>
            <p class="signup-link">
                Ready to start earning? <a href="register.php" class="btn">Sign Up Now</a>
            </p>
        </div>
    </div>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <h2>Join Task Tube Today</h2>
        <a href="register.php" class="btn" onclick="console.log('CTA button clicked')">Start Earning Now</a>
    </section>

    <!-- Notice Popup -->
    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Join Task Tube Today</h2>
        <p>Start earning money by watching video ads with our easy-to-use platform. Register now and turn your screen time into income!</p>
        <a href="register.php" class="btn" onclick="console.log('Notice button clicked')">Get Started</a>
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
        });

        // Notice Popup
        function isNoticeShown() {
            return localStorage.getItem('noticeShownAbout');
        }

        function setNoticeShown() {
            localStorage.setItem('noticeShownAbout', true);
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
