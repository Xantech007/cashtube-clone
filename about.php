<?php
// about.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background: linear-gradient(135deg, #6e44ff, #b5179e);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 20px 80px;
        }

        .about-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 800px;
            width: 100%;
            text-align: center;
        }

        .about-container h1 {
            font-size: 32px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .about-container h2 {
            font-size: 24px;
            font-weight: 500;
            color: #ff69b4;
            margin: 30px 0 15px;
        }

        .about-container p {
            font-size: 16px;
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
            text-align: left;
        }

        .about-container ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
            text-align: left;
        }

        .about-container ul li {
            font-size: 16px;
            color: #666;
            line-height: 1.8;
            margin-bottom: 10px;
            position: relative;
            padding-left: 25px;
        }

        .about-container ul li::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #6e44ff;
            position: absolute;
            left: 0;
            top: 2px;
        }

        .signup-link {
            font-size: 16px;
            color: #666;
            margin-top: 30px;
        }

        .signup-link a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .notice {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 20px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            display: none;
            z-index: 1002;
        }

        .notice h2 {
            font-size: 20px;
            color: #ff69b4;
            margin-bottom: 10px;
        }

        .notice p {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
            color: #999;
        }

        @media (max-width: 768px) {
            .about-container {
                padding: 20px;
            }

            .about-container h1 {
                font-size: 28px;
            }

            .about-container h2 {
                font-size: 22px;
            }

            .about-container p,
            .about-container ul li {
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .about-container {
                padding: 15px;
            }

            .about-container h1 {
                font-size: 24px;
            }

            .about-container h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div class="about-container">
        <h1>About Us</h1>
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
            Ready to start earning? <a href="register.php">Sign Up Now</a> and join the Task Tube community today!
        </p>
    </div>

    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Welcome to Task Tube</h2>
        <p>Discover how you can earn money by watching ad videos on our innovative platform.</p>
        <p>Join now and start your journey to effortless earnings with Task Tube!</p>
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
                if (link.getAttribute('href') === currentPath) {
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
                }, 1000);
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
