<?php
// register.php
require_once 'database/conn.php';

// Function to generate a unique 5-digit passcode
function generatePasscode($pdo) {
    do {
        $passcode = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE passcode = ?");
        $stmt->execute([$passcode]);
        $count = $stmt->fetchColumn();
    } while ($count > 0);
    return $passcode;
}

$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registerData'])) {
    $data = json_decode($_POST['registerData'], true);
    if (!empty($data['name']) && !empty($data['email']) && !empty($data['gender'])) {
        $name = trim($data['name']);
        $email = trim($data['email']);
        $gender = $data['gender'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['error'] = "Invalid email format.";
            file_put_contents('debug.log', 'Invalid email format: ' . $email . "\n", FILE_APPEND);
        } else {
            try {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $response['error'] = "Email already registered.";
                    file_put_contents('debug.log', 'Duplicate email: ' . $email . "\n", FILE_APPEND);
                } else {
                    // Generate unique passcode
                    $passcode = generatePasscode($pdo);

                    // Insert user into database
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, gender, passcode) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $gender, $passcode]);

                    $response['success'] = true;
                    $response['email'] = $email;
                }
            } catch (PDOException $e) {
                $response['error'] = "Database error: " . $e->getMessage();
                file_put_contents('debug.log', 'Database error: ' . $e->getMessage() . "\n", FILE_APPEND);
            }
        }
    } else {
        $response['error'] = "Incomplete registration data.";
        file_put_contents('debug.log', 'Invalid data: ' . print_r($data, true) . "\n", FILE_APPEND);
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
    <meta name="description" content="Register for Task Tube to start earning money by watching video ads. Join our crypto-powered platform today!">
    <meta name="keywords" content="Task Tube, register, earn money, watch ads, passive income">
    <meta name="author" content="Task Tube">
    <title>Task Tube - Register</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
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

        /* Register Section */
        .register-section {
            background: #fff;
            max-width: 500px;
            margin: 40px auto;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
            z-index: 10;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .register-section h1 {
            font-size: 32px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .register-section p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .register-section p span {
            color: #6e44ff;
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
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-field:focus {
            border-color: #6e44ff;
            box-shadow: 0 0 8px rgba(110, 68, 255, 0.2);
        }

        .gender-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .gender-options label {
            font-size: 16px;
            color: #333;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
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
            position: relative;
            z-index: 50; /* Ensure button is clickable */
            pointer-events: auto; /* Ensure button is clickable */
        }

        .submit-btn:hover {
            background: #5a33cc;
        }

        .submit-btn:disabled {
            background: #999;
            cursor: not-allowed;
        }

        .login-link {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }

        .login-link a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-section {
                padding: 20px;
                margin: 20px;
            }

            body {
                padding-top: 70px;
                padding-bottom: 80px;
            }

            .register-section h1 {
                font-size: 28px;
            }

            .register-section p {
                font-size: 15px;
            }

            .input-field {
                height: 45px;
                font-size: 15px;
            }

            .submit-btn {
                padding: 12px;
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding-top: 60px;
                padding-bottom: 60px;
            }

            .register-section h1 {
                font-size: 24px;
            }

            .register-section p {
                font-size: 14px;
            }

            .gender-options {
                flex-direction: column;
                gap: 10px;
            }

            .input-field {
                height: 40px;
                font-size: 14px;
            }

            .submit-btn {
                padding: 10px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <!-- Register Section -->
    <section class="register-section">
        <h1>Join <span>Task Tube</span> Today</h1>
        <p>Create your account to start earning by watching video ads</p>
        <form id="register-form" method="POST">
            <input type="text" id="name" name="name" class="input-field" placeholder="Full Name" required aria-label="Full Name">
            <input type="email" id="email" name="email" class="input-field" placeholder="Email Address" required aria-label="Email Address">
            <div class="gender-options">
                <label><input type="radio" name="gender" value="male" required> Male</label>
                <label><input type="radio" name="gender" value="female"> Female</label>
                <label><input type="radio" name="gender" value="other"> Other</label>
            </div>
            <button type="submit" class="submit-btn" onclick="console.log('Submit button clicked')">Submit</button>
        </form>
        <p class="login-link">Already have an account? <a href="signin.php">Sign In</a></p>
    </section>

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
        document.getElementById('register-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form values
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const gender = document.querySelector('input[name="gender"]:checked')?.value;
            const submitBtn = document.querySelector('.submit-btn');

            // Client-side validation
            if (!name || !email || !gender) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill out all fields and select a gender.',
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

            // Disable button to prevent multiple submissions
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            // Prepare data
            const data = { name, email, gender };
            console.log('Form data prepared:', data);

            // Send data via AJAX
            $.ajax({
                url: './register.php',
                type: 'POST',
                data: { registerData: JSON.stringify(data) },
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX success:', response);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful!',
                            text: 'Your account has been created.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = './register-finish.php?email=' + encodeURIComponent(response.email);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.error || 'Registration failed. Please try again.',
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to send data: ' + (xhr.responseText || 'Server error. Please try again.'),
                    });
                },
                complete: function() {
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit';
                }
            });
        });

        // Prevent right-click only on non-link elements
        document.addEventListener('contextmenu', e => {
            if (!e.target.closest('a') && !e.target.closest('button')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
