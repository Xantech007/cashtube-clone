<?php
// index.php
session_start(); // Start session to check user login status
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Earn money by watching video ads with Task Tube. Join our crypto-powered platform and turn your screen time into passive income!">
    <meta name="keywords" content="earn money online, watch ads, Task Tube, passive income, crypto earnings, make money">
    <meta name="author" content="Task Tube">
    <title>Task Tube - Earn Money Watching Ads</title>
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
            padding: 120px 20px;
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
            background: url('https://source.unsplash.com/random/1920x1080/?crypto,technology') no-repeat center center/cover;
            opacity: 0.15;
            z-index: 0;
        }

        .hero-section h1 {
            font-size: 52px;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            animation: fadeInDown 1s ease-out;
        }

        .hero-section p {
            font-size: 20px;
            line-height: 1.6;
            max-width: 700px;
            margin: 0 auto 30px;
            position: relative;
            z-index: 1;
            animation: fadeIn 1.2s ease-out;
        }

        /* Button Styles */
        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            position: relative;
            z-index: 50;
        }

        .btn {
            padding: 14px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            pointer-events: auto;
        }

        .btn-register, .btn-dashboard {
            background-color: #6e44ff;
            color: #fff;
            border: none;
        }

        .btn-register:hover, .btn-dashboard:hover {
            background-color: #5a00b5;
            transform: scale(1.05);
        }

        .btn-signin {
            background-color: transparent;
            color: #ff69b4;
            border: 2px solid #ff69b4;
        }

        .btn-signin:hover {
            background-color: #ff69b4;
            color: #fff;
            transform: scale(1.05);
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
            animation: fadeIn 1s ease-out;
        }

        /* How It Works Section */
        .how-it-works {
            margin-bottom: 60px;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .step-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .step-card:hover {
            transform: translateY(-10px);
        }

        .step-card i {
            font-size: 36px;
            color: #6e44ff;
            margin-bottom: 20px;
        }

        .step-card h3 {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .step-card p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .feature-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-card i {
            font-size: 40px;
            color: #6e44ff;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .feature-card p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            background: #f9f9f9;
            padding: 60px 20px;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 36px;
            font-weight: 700;
            color: #6e44ff;
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 16px;
            color: #666;
        }

        /* Testimonials Section */
        .testimonials {
            background: #f9f9f9;
            padding: 60px 20px;
            text-align: center;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .testimonial-card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 15px;
            object-fit: cover;
        }

        .testimonial-card p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .testimonial-card h4 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .testimonial-card span {
            font-size: 14px;
            color: #999;
        }

        /* FAQ Section */
        .faq {
            margin-bottom: 60px;
        }

        .faq-grid {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background: #fff;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .faq-item h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            padding: 20px;
            margin: 0;
            cursor: pointer;
            position: relative;
        }

        .faq-item h3::after {
            content: '\f078'; /* Font Awesome chevron-down */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            transition: transform 0.3s ease;
        }

        .faq-item.active h3::after {
            transform: rotate(180deg);
        }

        .faq-item p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            padding: 0 20px 20px;
            display: none;
        }

        .faq-item.active p {
            display: block;
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
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .cta-banner .btn:hover {
            background-color: #f0f0f0;
            transform: scale(1.05);
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

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .hero-section h1 {
                font-size: 40px;
            }

            .hero-section p {
                font-size: 18px;
            }

            .section-title {
                font-size: 32px;
            }

            .step-card, .feature-card, .testimonial-card, .stat-card {
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
                font-size: 36px;
            }

            .hero-section p {
                font-size: 16px;
            }

            .section-title {
                font-size: 28px;
            }

            .button-group {
                flex-direction: column;
                gap: 15px;
            }

            .btn {
                padding: 12px 30px;
                font-size: 16px;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .stat-card h3 {
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

            .step-card, .feature-card, .testimonial-card, .stat-card {
                padding: 15px;
            }

            .stat-card h3 {
                font-size: 24px;
            }

            .faq-item h3 {
                font-size: 16px;
            }

            .faq-item p {
                font-size: 14px;
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
        <h1>Earn Money with Task Tube</h1>
        <p>Join our crypto-powered platform to turn your screen time into passive income. Watch video ads from your smartphone or computer and earn up to $1,000 daily!</p>
        <div class="button-group">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="users/home.php" class="btn btn-dashboard" onclick="console.log('Dashboard button clicked')">Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-register" onclick="console.log('Register button clicked')">Get Started</a>
                <a href="signin.php" class="btn btn-signin" onclick="console.log('Sign In button clicked')">Sign In</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- How It Works Section -->
    <div class="index-container how-it-works">
        <h2 class="section-title">How Task Tube Works</h2>
        <div class="steps">
            <div class="step-card">
                <i class="fas fa-user-plus"></i>
                <h3>1. Sign Up</h3>
                <p>Create an account with your email and a 5-digit passcode in just a few clicks.</p>
            </div>
            <div class="step-card">
                <i class="fas fa-video"></i>
                <h3>2. Watch Ads</h3>
                <p>Choose from a variety of short video ads and earn money for each view.</p>
            </div>
            <div class="step-card">
                <i class="fas fa-wallet"></i>
                <h3>3. Withdraw Earnings</h3>
                <p>Cash out your earnings securely via our blockchain-based system.</p>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="index-container">
        <h2 class="section-title">Why Choose Task Tube?</h2>
        <div class="features">
            <div class="feature-card">
                <i class="fas fa-dollar-sign"></i>
                <h3>High Earnings Potential</h3>
                <p>Earn up to $1,000 daily by watching short video ads, using minimal data (as low as 10MB per ad).</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-mobile-alt"></i>
                <h3>Flexible Access</h3>
                <p>Watch ads anytime, anywhere, on your smartphone, tablet, or computer with a stable internet connection.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-lock"></i>
                <h3>Secure & Transparent</h3>
                <p>Withdraw earnings securely through our blockchain system, with full transparency and no hidden fees.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-headset"></i>
                <h3>24/7 Support</h3>
                <p>Access our dedicated support team via LiveChat or our <a href="contact.php">Contact page</a> for any questions.</p>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <section class="stats">
        <h2 class="section-title">Our Impact</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>50K+</h3>
                <p>Active Users</p>
            </div>
            <div class="stat-card">
                <h3>$1M+</h3>
                <p>Paid Out</p>
            </div>
            <div class="stat-card">
                <h3>10M+</h3>
                <p>Ads Watched</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <h2 class="section-title">What Our Users Say</h2>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <img src="https://randomuser.me/api/portraits/women/1.jpg" alt="Sarah M.">
                <p>"Task Tube transformed my downtime into dollars! I earn extra cash daily, and the withdrawals are lightning-fast."</p>
                <h4>Sarah M.</h4>
                <span>Freelancer</span>
            </div>
            <div class="testimonial-card">
                <img src="https://randomuser.me/api/portraits/men/2.jpg" alt="James K.">
                <p>"As a student, Task Tube is perfect. It’s so easy to earn money watching ads during breaks!"</p>
                <h4>James K.</h4>
                <span>Student</span>
            </div>
            <div class="testimonial-card">
                <img src="https://randomuser.me/api/portraits/women/3.jpg" alt="Emily R.">
                <p>"Reliable platform, great support, and consistent earnings. Task Tube is a must-try!"</p>
                <h4>Emily R.</h4>
                <span>Entrepreneur</span>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <div class="index-container faq">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <div class="faq-grid">
            <div class="faq-item">
                <h3>How do I start earning with Task Tube?</h3>
                <p>Sign up with your email and a 5-digit passcode, then start watching video ads to earn money instantly.</p>
            </div>
            <div class="faq-item">
                <h3>What are the requirements to join?</h3>
                <p>You need a smartphone or computer with an internet connection. No prior experience is required!</p>
            </div>
            <div class="faq-item">
                <h3>How are earnings paid out?</h3>
                <p>Earnings are paid via our secure blockchain system. You can withdraw to your preferred wallet with low fees.</p>
            </div>
            <div class="faq-item">
                <h3>Is Task Tube safe to use?</h3>
                <p>Yes, we prioritize security with encrypted data and secure withdrawals. Read our <a href="privacy.php">Privacy Policy</a> for details.</p>
            </div>
        </div>
    </div>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <h2>Join Thousands Earning with Task Tube</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="users/home.php" class="btn" onclick="console.log('Dashboard CTA clicked')">Go to Dashboard</a>
        <?php else: ?>
            <a href="register.php" class="btn" onclick="console.log('CTA button clicked')">Start Earning Now</a>
        <?php endif; ?>
    </section>

    <!-- Notice Popup -->
    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Join Task Tube Today</h2>
        <p>Start earning money by watching video ads with our crypto-powered platform. Sign up now and turn your screen time into income!</p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="users/home.php" class="btn btn-dashboard" onclick="console.log('Notice dashboard clicked')">Go to Dashboard</a>
        <?php else: ?>
            <a href="register.php" class="btn btn-register" onclick="console.log('Notice button clicked')">Get Started</a>
        <?php endif; ?>
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
            return localStorage.getItem('noticeShownIndex');
        }

        function setNoticeShown() {
            localStorage.setItem('noticeShownIndex', true);
        }

        function showNotice() {
            if (!isNoticeShown()) {
                const notice = document.getElementById('notice');
                setTimeout(() => {
                    notice.style.display = 'block';
                    setNoticeShown();
                }, 2000);
            }
        }

        function closeNotice() {
            document.getElementById('notice').style.display = 'none';
            setNoticeShown();
        }

        window.addEventListener('load', showNotice);

        // FAQ Toggle
        document.querySelectorAll('.faq-item').forEach(item => {
            item.addEventListener('click', () => {
                item.classList.toggle('active');
            });
        });

        // Prevent right-click only on non-link elements
        document.addEventListener('contextmenu', e => {
            if (!e.target.closest('a')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
