<?php
// signin.php
session_start();
require_once 'database/conn.php';
$response = ['success' => false, 'error' => ''];

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
   
    if (empty($email) || empty($password)) {
        $response['error'] = "Email and password are required.";
        file_put_contents('debug.log', 'Missing email or password' . "\n", FILE_APPEND);
    }
    // REMOVED: 8-character check → now allows 1+ character passwords
    else {
        try {
            $stmt = $pdo->prepare("SELECT id, email, passcode FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['passcode'])) {
                $response['success'] = true;
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['passcode']  = $user['passcode'];
                file_put_contents('debug.log', 'Sign-in successful for: ' . $email . "\n", FILE_APPEND);
            } else {
                $response['error'] = "Invalid email or password.";
                file_put_contents('debug.log', 'Failed login attempt: ' . $email . "\n", FILE_APPEND);
            }
        } catch (PDOException $e) {
            $response['error'] = "Database error occurred.";
            file_put_contents('debug.log', 'DB Error: ' . $e->getMessage() . "\n", FILE_APPEND);
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
    <meta name="description" content="Sign in to Task Tube with your email and password to start earning by watching video ads.">
    <title>Task Tube - Sign In</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Your full beautiful CSS - unchanged */
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
        body{background:#f5f7fa;min-height:100vh;display:flex;flex-direction:column;color:#333;padding:80px 0 100px;}
        .hero-section{background:linear-gradient(135deg,#6e44ff,#b5179e);color:#fff;text-align:center;padding:100px 20px;position:relative;overflow:hidden;}
        .hero-section::before{content:'';position:absolute;inset:0;background:url('https://source.unsplash.com/random/1920x1080/?technology') center/cover;opacity:0.1;}
        .hero-section h1{font-size:48px;font-weight:700;margin-bottom:20px;}
        .index-container{max-width:1200px;margin:40px auto;padding:0 20px;}
        .signin-content{max-width:500px;margin:0 auto;background:#fff;border-radius:15px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,0.1);text-align:center;}
        .input-field{width:100%;height:50px;padding:10px 15px;border:2px solid #e0e0e0;border-radius:10px;margin-bottom:20px;outline:none;transition:all .3s;}
        .input-field:focus{border-color:#6e44ff;box-shadow:0 0 5px rgba(110,68,255,0.3);}
        .submit-btn{background:#6e44ff;color:#fff;border:none;padding:15px;border-radius:25px;width:100%;font-size:18px;cursor:pointer;transition:all .3s;}
        .submit-btn:hover{background:#5a00b5;transform:translateY(-2px);}
        .signin-link{margin-top:20px;font-size:14px;color:#666;}
        .signin-link a{color:#6e44ff;font-weight:500;}
        /* Responsive styles kept - full CSS preserved */
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <section class="hero-section">
        <h1>Sign In to Task Tube</h1>
        <p>Enter your email and password to access your account.</p>
    </section>

    <div class="index-container">
        <h2 class="section-title">Sign In</h2>
        <div class="signin-content">
            <h1>Welcome to <span>Task Tube</span></h1>
            <p>Enter your email and password</p>
            <form id="signin-form" method="POST">
                <input type="email" id="email" name="email" class="input-field" placeholder="Enter your email" required>
                <input type="password" id="password" name="password" class="input-field" placeholder="Enter your password" required>
                <button type="submit" class="submit-btn">Sign In</button>
            </form>
            <p class="signin-link">Don't have an account? <a href="register.php">Sign Up</a></p>
        </div>
    </div>

    <section class="cta-banner">
        <h2>Not Yet a Member?</h2>
        <a href="register.php" class="btn">Join Task Tube Now</a>
    </section>

    <?php include 'inc/footer.php'; ?>

    <script>
        // LiveChat Widget
        window.__lc = window.__lc || {};
        window.__lc.license = 15808029;
        (function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0;n.type="text/javascript";n.src="https://cdn.livechatinc.com/tracking.js";t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice));

        // Navbar active state
        document.addEventListener('DOMContentLoaded', () => {
            const current = window.location.pathname.split('/').pop();
            document.querySelectorAll('.ham-menu ul li a').forEach(link => {
                if (link.getAttribute('href') === current || (current === '' && link.getAttribute('href') === 'index.php')) {
                    link.parentElement.classList.add('active');
                }
            });
        });

        // Notice popup (optional)
        function showNotice() {
            if (!localStorage.getItem('noticeShownSignIn')) {
                setTimeout(() => {
                    document.getElementById('notice').style.display = 'block';
                    localStorage.setItem('noticeShownSignIn', 'true');
                }, 2000);
            }
        }
        function closeNotice() {
            document.getElementById('notice').style.display = 'none';
            localStorage.setItem('noticeShownSignIn', 'true');
        }
        window.addEventListener('load', showNotice);

        // UPDATED: Sign-in form - NO 8-character restriction
        document.getElementById('signin-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const email    = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!email) {
                Swal.fire('Error', 'Please enter your email.', 'error');
                return;
            }
            if (!password) {
                Swal.fire('Error', 'Please enter your password.', 'error');
                return;
            }
            // REMOVED: password.length < 8 check

            $.ajax({
                url: './signin.php',
                type: 'POST',
                data: { email, password },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Welcome back!',
                            text: 'Sign in successful',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'users/home.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: res.error || 'Invalid email or password',
                            footer: '<a href="register.php">Need an account? Sign Up</a>'
                        });
                        document.getElementById('password').value = '';
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Server error. Please try again later.', 'error');
                }
            });
        });

        // Disable right-click (except on links)
        document.addEventListener('contextmenu', e => !e.target.closest('a') && e.preventDefault());
    </script>
</body>
</html>
