<?php
// login.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Login</title>
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

        .login-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .login-container h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .login-container .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .login-container label {
            font-size: 14px;
            font-weight: 500;
            color: #666;
            display: block;
            margin-bottom: 5px;
        }

        .login-container input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .login-container input:focus {
            border-color: #6e44ff;
        }

        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .keypad-button {
            background: #f0e9ff;
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 18px;
            font-weight: 500;
            color: #6e44ff;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .keypad-button:hover {
            background: #6e44ff;
            color: #fff;
        }

        .keypad-button.delete {
            background: #ffe9ec;
            color: #ff69b4;
        }

        .keypad-button.delete:hover {
            background: #ff69b4;
            color: #fff;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #6e44ff;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background: #5a00b5;
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
            .login-container {
                padding: 20px;
            }

            .login-container h1 {
                font-size: 24px;
            }

            .keypad-button {
                padding: 12px;
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }

            .login-container h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div class="login-container">
        <h1>Login to Task Tube</h1>
        <form id="login-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="passcode">Passcode</label>
                <input type="text" id="passcode" name="passcode" placeholder="Enter 5-digit passcode" maxlength="5" readonly required>
                <div class="keypad" id="keypad"></div>
            </div>
            <button type="submit" class="submit-btn">Login</button>
        </form>
        <p class="signup-link">
            Don't have an account? <a href="register.php">Sign Up</a>
        </p>
    </div>

    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Welcome Back</h2>
        <p>Login to continue earning money by watching video ads!</p>
        <p>Use your email and 5-digit passcode to access your account.</p>
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
        // Keypad Generation
        document.addEventListener('DOMContentLoaded', function() {
            const keypad = document.getElementById('keypad');
            const passcodeInput = document.getElementById('passcode');

            // Create keypad buttons for digits 0-9
            const digits = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
            digits.forEach(digit => {
                const button = document.createElement('button');
                button.className = 'keypad-button';
                button.textContent = digit;
                button.addEventListener('click', () => {
                    if (passcodeInput.value.length < 5) {
                        passcodeInput.value += digit;
                    }
                });
                keypad.appendChild(button);
            });

            // Add delete button
            const deleteButton = document.createElement('button');
            deleteButton.className = 'keypad-button delete';
            deleteButton.innerHTML = '<i class="fas fa-backspace"></i>';
            deleteButton.addEventListener('click', () => {
                passcodeInput.value = passcodeInput.value.slice(0, -1);
            });
            keypad.appendChild(deleteButton);

            // Set active navbar link
            const currentPath = window.location.pathname.split('/').pop();
            const links = document.querySelectorAll('.ham-menu ul li a');
            links.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.parentElement.classList.add('active');
                }
            });

            // Notice popup
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
                    }, 1000);
                }
            }

            function closeNotice() {
                document.getElementById('notice').style.display = 'none';
                setNoticeShown();
            }

            window.addEventListener('load', showNotice);

            // Form submission
            document.getElementById('login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const email = document.getElementById('email').value;
                const passcode = passcodeInput.value;

                if (passcode.length !== 5) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Passcode',
                        text: 'Please enter a 5-digit passcode.'
                    });
                    return;
                }

                // AJAX submission
                fetch('database/login_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(email)}&passcode=${encodeURIComponent(passcode)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Successful',
                            text: 'Redirecting to your dashboard...',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'home.html';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: data.message || 'Invalid email or passcode.'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again.'
                    });
                });
            });

            // Prevent right-click
            document.addEventListener('contextmenu', e => e.preventDefault());
        });
    </script>
</body>
</html>
