<?php
// signin.php
require_once 'database/conn.php';

$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['passcode'])) {
    $email = trim($_POST['email']);
    $passcode = trim($_POST['passcode']);
    
    if (empty($email) || empty($passcode)) {
        $response['error'] = "Please enter both email and passcode.";
        file_put_contents('debug.log', 'Empty email or passcode: ' . $email . "\n", FILE_APPEND);
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Invalid email format.";
        file_put_contents('debug.log', 'Invalid email format: ' . $email . "\n", FILE_APPEND);
    } elseif (strlen($passcode) !== 5) {
        $response['error'] = "Passcode must be 5 digits.";
        file_put_contents('debug.log', 'Invalid passcode length: ' . $passcode . "\n", FILE_APPEND);
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND passcode = ?");
            $stmt->execute([$email, $passcode]);
            if ($stmt->fetchColumn() > 0) {
                $response['success'] = true;
            } else {
                $response['error'] = "Invalid email or passcode.";
                file_put_contents('debug.log', 'Invalid email/passcode: ' . $email . "\n", FILE_APPEND);
            }
        } catch (PDOException $e) {
            $response['error'] = "Database error: " . $e->getMessage();
            file_put_contents('debug.log', 'Database error: ' . $e->getMessage() . "\n", FILE_APPEND);
        }
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
    <title>Task Tube - Sign In</title>
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

        .signin-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .signin-container h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .signin-container p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .signin-container p span {
            color: #ff69b4;
            font-weight: 500;
        }

        .input-field {
            width: 100%;
            height: 50px;
            font-size: 16px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .input-field:focus {
            border-color: #6e44ff;
        }

        #passcode {
            font-size: 24px;
            text-align: center;
        }

        .submit-btn {
            background: #6e44ff;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background: #5a33cc;
        }

        .register-link {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }

        .register-link a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
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
            color: #ff4d94;
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
            .signin-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div class="signin-container">
        <h1>Welcome to <span>Task Tube</span></h1>
        <p>Sign in with your email and passcode</p>
        <form id="signin-form" method="POST">
            <input type="email" id="email" name="email" class="input-field" placeholder="Email Address" required aria-label="Email Address">
            <input type="password" id="passcode" name="passcode" class="input-field" placeholder="5-Digit Passcode" required aria-label="Passcode">
            <button type="submit" class="submit-btn">Sign In</button>
        </form>
        <p class="register-link">Don't have an account? <a href="register.php">Sign Up</a></p>
    </div>

    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Welcome to Task Tube</h2>
        <p>If you've been looking for a way to earn money by watching ad videos, you're in the right place!</p>
        <p>Sign in with your email and 5-digit passcode to start earning today.</p>
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
        // Form Submission
        document.getElementById('signin-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const passcode = document.getElementById('passcode').value.trim();

            // Client-side validation
            if (!email || !passcode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter both email and passcode.',
                });
                return;
            }

            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter a valid email address.',
                });
                return;
            }

            // Validate passcode length
            if (passcode.length !== 5) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Passcode must be 5 digits.',
                });
                return;
            }

            // Send data via AJAX
            $.ajax({
                url: './signin.php',
                type: 'POST',
                data: { email: email, passcode: passcode },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Good job!',
                            text: 'Sign-in successful',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'home.html';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.error || 'Invalid email or passcode.',
                            footer: '<a href="register.php">Sign Up</a>'
                        });
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
                }
            });
        });

        // Notice Popup
        function isNoticeShown() {
            return localStorage.getItem('noticeShown');
        }

        function setNoticeShown() {
            localStorage.setItem('noticeShown', true);
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
