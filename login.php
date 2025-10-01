<?php
// login.php
require_once 'database/conn.php';

$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['passcode'])) {
    $passcode = trim($_POST['passcode']);
    
    if (strlen($passcode) === 5) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE passcode = ?");
            $stmt->execute([$passcode]);
            if ($stmt->fetchColumn() > 0) {
                $response['success'] = true;
            } else {
                $response['error'] = "Invalid passcode.";
                file_put_contents('debug.log', 'Invalid passcode: ' . $passcode . "\n", FILE_APPEND);
            }
        } catch (PDOException $e) {
            $response['error'] = "Database error: " . $e->getMessage();
            file_put_contents('debug.log', 'Database error: ' . $e->getMessage() . "\n", FILE_APPEND);
        }
    } else {
        $response['error'] = "Passcode must be 5 digits.";
        file_put_contents('debug.log', 'Invalid passcode length: ' . $passcode . "\n", FILE_APPEND);
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
    <title>Task Tube - Secure Login</title>
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
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .login-container p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .login-container p span {
            color: #ff69b4;
            font-weight: 500;
        }

        #passcode {
            width: 100%;
            height: 50px;
            font-size: 24px;
            text-align: center;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        #passcode:focus {
            border-color: #6e44ff;
        }

        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .key {
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            font-size: 18px;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .key:hover {
            background: #6e44ff;
            color: #fff;
            transform: scale(1.05);
        }

        .key.action {
            background: #ff69b4;
            color: #fff;
            border: none;
        }

        .key.action:hover {
            background: #ff4d94;
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
            .login-container {
                padding: 20px;
            }

            .key {
                padding: 10px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div class="login-container">
        <h1>Welcome to <span>Task Tube</span></h1>
        <p>Enter your 5-digit code</p>
        <input type="password" id="passcode" readonly style="font-size: 24px;" aria-label="Passcode input">
        <div class="keypad">
            <div class="key">1</div>
            <div class="key">2</div>
            <div class="key">3</div>
            <div class="key">4</div>
            <div class="key">5</div>
            <div class="key">6</div>
            <div class="key">7</div>
            <div class="key">8</div>
            <div class="key">9</div>
            <div class="key action"><a href='register.php' style='color: #fff;'>Sign Up</a></div>
            <div class="key action" id="clear">Clear</div>
            <div class="key action" id="enter">Login</div>
        </div>
    </div>

    <div class="notice" id="notice">
        <span class="close-btn" onclick="closeNotice()" aria-label="Close notice">×</span>
        <h2>Welcome to Task Tube</h2>
        <p>If you've been looking for a way to earn money by watching ad videos, you're in the right place!</p>
        <p>Get your 5-digit login code and start earning today.</p>
    </div>

    <?php include 'inc/footer.php'; ?>

    <!-- LiveChat Script -->
    <script>
        window.__lc = window.__lc || {};
        window.__lc.license = 15808029;
        (function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0,n.type="text/javascript",n.src="https://cdn.livechatinc.com/tracking.js",t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice))
    </script>
    <noscript><a href="https://www.livechat.com/chat-with/15808029/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechat.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a></noscript>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script>
        // Passcode Logic
        const passcodeInput = document.getElementById("passcode");
        const keys = document.querySelectorAll(".key:not(.action)");
        const clearButton = document.getElementById("clear");
        const enterButton = document.getElementById("enter");

        keys.forEach(key => {
            key.addEventListener("click", () => {
                if (passcodeInput.value.length < 5) {
                    passcodeInput.value += key.textContent;
                }
            });
        });

        clearButton.addEventListener("click", () => {
            passcodeInput.value = "";
        });

        enterButton.addEventListener("click", validatePasscode);

        passcodeInput.addEventListener("input", () => {
            if (passcodeInput.value.length === 5) {
                validatePasscode();
            }
        });

        function validatePasscode() {
            const passcode = passcodeInput.value;
            if (passcode.length !== 5) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter a 5-digit passcode.',
                });
                return;
            }

            $.ajax({
                url: './login.php',
                type: 'POST',
                data: { passcode: passcode },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Good job!',
                            text: 'Login successful',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'home.html';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.error || 'Invalid Passcode!',
                            footer: '<a href="register.php">Sign Up</a>'
                        });
                        passcodeInput.value = "";
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
                    passcodeInput.value = "";
                }
            });
        }

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
