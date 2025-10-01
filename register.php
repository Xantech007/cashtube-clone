<?php
// register.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Register</title>
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

        .hamburger-menu-button {
            width: 40px;
            height: 40px;
            background: #6e44ff;
            border: 3px solid #fff;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hamburger-menu-button span {
            width: 20px;
            height: 2px;
            background: #fff;
            position: absolute;
            transition: all 0.3s ease;
        }

        .hamburger-menu-button span::before,
        .hamburger-menu-button span::after {
            content: '';
            width: 20px;
            height: 2px;
            background: #fff;
            position: absolute;
            transition: all 0.3s ease;
        }

        .hamburger-menu-button span::before {
            transform: translateY(-6px);
        }

        .hamburger-menu-button span::after {
            transform: translateY(6px);
        }

        .hamburger-menu-button-close span {
            background: transparent;
        }

        .hamburger-menu-button-close span::before {
            transform: translateY(0) rotate(45deg);
        }

        .hamburger-menu-button-close span::after {
            transform: translateY(0) rotate(-45deg);
        }

        .ham-menu {
            position: absolute;
            top: 70px;
            left: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        .ham-menu.on {
            transform: translateX(0);
        }

        .ham-menu ul {
            list-style: none;
            padding: 20px;
        }

        .ham-menu ul li {
            margin: 10px 0;
        }

        .ham-menu ul li a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .ham-menu ul li a:hover {
            color: #6e44ff;
        }

        .register-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .register-container h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .register-container p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .register-container p span {
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

        @media (max-width: 768px) {
            .register-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div class="register-container">
        <h1>Register for <span>Task Tube</span></h1>
        <p>Create your account to start earning</p>
        <form id="register-form" method="POST">
            <input type="text" id="name" name="name" class="input-field" placeholder="Full Name" required aria-label="Full Name">
            <input type="email" id="email" name="email" class="input-field" placeholder="Email Address" required aria-label="Email Address">
            <div class="gender-options">
                <label><input type="radio" name="gender" value="male" required> Male</label>
                <label><input type="radio" name="gender" value="female"> Female</label>
                <label><input type="radio" name="gender" value="other"> Other</label>
            </div>
            <button type="submit" class="submit-btn">Submit</button>
        </form>
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
        // Hamburger Menu
        const button = document.getElementById('hamburger-menu');
        button.addEventListener('click', function() {
            const span = button.getElementsByTagName('span')[0];
            span.classList.toggle('hamburger-menu-button-close');
            document.getElementById('ham-navigation').classList.toggle('on');
        });

        $('.menu li a').on('click', function() {
            $('#hamburger-menu').click();
        });

        // Form Submission
        document.getElementById('register-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form values
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const gender = document.querySelector('input[name="gender"]:checked')?.value;

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

            // Prepare data
            const data = { name, email, gender };
            console.log('Form data prepared:', data);

            // Send data via AJAX
            $.ajax({
                url: './register-finish.php', // Ensure correct path
                type: 'POST',
                data: { registerData: JSON.stringify(data) },
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                dataType: 'json', // Expect JSON response
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
                            window.location.href = './register-finish.php';
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
                }
            });
        });

        // Prevent right-click
        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>
</body>
</html>
