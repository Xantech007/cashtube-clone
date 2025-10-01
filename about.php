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
            padding: 30px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .about-container h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .about-container h2 {
            font-size: 22px;
            font-weight: 500;
            color: #ff69b4;
            margin-bottom: 20px;
        }

        .about-container p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .signup-link {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }

        .signup-link a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
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
            Task Tube is your gateway to earning money online effortlessly. Instead of spending time on social media, get paid in USD for watching video ads right from your smartphone.
        </p>
        <p>
            With minimal data, you can unlock earnings of up to $1,000 daily. Join our crypto-powered platform and start earning rewards instantly.
        </p>
        <p class="signup-link">Ready to start earning? <a href="register.php">Sign Up Now</a></p>
    </div>

    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Welcome to Task Tube</h2>
        <p>Learn how you can earn money by watching ad videos on our platform.</p>
        <p>Join now and start earning with Task Tube!</p>
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
