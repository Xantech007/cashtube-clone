<?php
// index.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Earn money by watching video ads with Task Tube. Join our crypto-powered platform and turn your screen time into income!">
    <meta name="keywords" content="earn money online, watch ads, Task Tube, passive income, crypto earnings">
    <meta name="author" content="Task Tube">
    <title>Task Tube - Earn Money Watching Ads</title>
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
            /* Add padding to prevent header/footer overlap */
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
        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-register {
            background-color: #6e44ff;
            color: #fff;
            border: none;
        }

        .btn-register:hover {
            background-color: #5a00b5;
        }

        .btn-signin {
            background-color: transparent;
            color: #ff69b4;
            border: 2px solid #ff69b4;
        }

        .btn-signin:hover {
            background-color: #ff69b4;
            color: #fff;
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

            .feature-card, .testimonial-card {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 70px; /* Adjust for smaller header */
                padding-bottom: 80px; /* Adjust for smaller footer */
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

            .button-group {
                flex-direction: column;
                gap: 15px;
            }

            .btn {
                padding: 10px 20px;
                font-size: 15px;
            }

            .cta-banner h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding-top: 60px; /* Further adjust for smaller screens */
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

            .feature-card, .testimonial-card {
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
        <h1>Welcome to Task Tube</h1>
        <p>Earn money effortlessly by watching video ads from your smartphone or computer. Join our crypto-powered platform and turn your screen time into income!</p>
        <div class="button-group">
            <a href="register.php" class="btn btn-register">Get Started</a>
            <a href="signin.php" class="btn btn-signin">Sign In</a>
        </div>
    </section>

    <!-- Features Section -->
    <div class="index-container">
        <h2 class="section-title">Why Choose Task Tube?</h2>
        <div class="features">
            <div class="feature-card">
                <i class="fas fa-dollar-sign"></i>
                <h3>Earn Money Easily</h3>
                <p>Get paid in USD for watching short video ads, with earnings up to $1,000 daily using minimal data.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-mobile-alt"></i>
                <h3>Anytime, Anywhere</h3>
                <p>Watch ads on your smartphone or computer at your own pace, from anywhere in the world.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-lock"></i>
                <h3>Secure Withdrawals</h3>
                <p>Withdraw your earnings securely through our blockchain-based system with full transparency.</p>
            </div>
        </div>
    </div>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <h2 class="section-title">What Our Users Say</h2>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p>"Task Tube has been a game-changer for me! I earn extra cash in my free time just by watching ads. The withdrawals are fast and secure!"</p>
                <h4>Sarah M.</h4>
                <span>Freelancer</span>
            </div>
            <div class="testimonial-card">
                <p>"I love how easy it is to use Task Tube. It’s perfect for students like me who want to make money without a complicated process."</p>
                <h4>James K.</h4>
                <span>Student</span>
            </div>
            <div class="testimonial-card">
                <p>"The platform is reliable, and the support team is amazing. I’ve been earning consistently and highly recommend Task Tube!"</p>
                <h4>Emily R.</h4>
                <span>Entrepreneur</span>
            </div>
        </div>
    </section>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <h2>Join Thousands Earning with Task Tube</h2>
        <a href="register.php" class="btn">Start Earning Now</a>
    </section>

    <!-- Notice Popup -->
    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Join Task Tube Today</h2>
        <p>Start earning money by watching video ads with our easy-to-use platform. Register now and turn your screen time into income!</p>
        <a href="register.php" class="btn btn-register">Get Started</a>
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

        // Prevent right-click
        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>
</body>
</html>
