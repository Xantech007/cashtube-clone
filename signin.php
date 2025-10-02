<?php
// signin.php
require_once 'database/conn.php';

$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['passcode'])) {
    $email = trim($_POST['email']);
    $passcode = trim($_POST['passcode']);
    
    if (empty($email) || empty($passcode)) {
        $response['error'] = "Email and passcode are required.";
        file_put_contents('debug.log', 'Missing email or passcode' . "\n", FILE_APPEND);
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
                file_put_contents('debug.log', 'Invalid email or passcode: ' . $email . ', ' . $passcode . "\n", FILE_APPEND);
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

        #email, #passcode {
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

        #email:focus, #passcode:focus {
            border-color: #6e44ff;
        }

        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(5, auto);
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

        .key.zero {
            grid-column: 2 / 3;
            grid-row: 4 / 5;
        }

        .key.action.signup {
            grid-column: 1 / 2;
            grid-row: 5 / 6;
        }

        .key.action.clear {
            grid-column: 2 / 3;
            grid-row: 5 / 6;
        }

        .key.action.enter {
            grid-column: 3 / 4;
            grid-row: 5 / 6;
        }

        .signin-link {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }

        .signin-link a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
        }

        .signin-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .signin-container {
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

    <div class="signin-container">
        <h1>Welcome to <span>Task Tube</span></h1>
        <p>Enter your email and 5-digit code</p>
        <input type="email" id="email" placeholder="Enter your email" aria-label="Email input">
        <input type="password" id="passcode" readonly style="font-size: 24px;" placeholder="Enter 5-digit code" aria-label="Passcode input">
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
            <div class="key zero">0</div>
            <div class="key action signup"><a href='register.php' style='color: #fff;'>Sign Up</a></div>
            <div class="key action clear" id="clear">Clear</div>
            <div class="key action enter" id="enter">Sign In</div>
        </div>
        <p class="signin-link">Don't have an account? <a href="register.php">Sign Up</a></p>
    </div>

    <?php include 'inc/footer.php'; ?>

    <!-- LiveChat Script -->
    <script>
        window.__lc = window.__lc || {};
        window.__lc.license = 15808029;
        (function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h,null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0,n.type="text/javascript",n.src="https://cdn.livechat.com/tracking.js",t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice))
    </script>
    <noscript><a href="https://www.livechat.com/chat-with/15808029/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechat.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a></noscript>

    <script>
        // Passcode Logic
        const emailInput = document.getElementById("email");
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

        enterButton.addEventListener("click", validateSignIn);

        passcodeInput.addEventListener("input", () => {
            if (passcodeInput.value.length === 5) {
                validateSignIn();
            }
        });

        function validateSignIn() {
            const email = emailInput.value.trim();
            const passcode = passcodeInput.value;

            if (!email) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter your email.',
                });
                return;
            }

            if (passcode.length !== 5) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please enter a 5-digit passcode.',
                });
                return;
            }

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
                            text: 'Sign In successful',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'users/home.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.error || 'Invalid email or passcode!',
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

        // Prevent right-click
        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>
</body>
</html>
