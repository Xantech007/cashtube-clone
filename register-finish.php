<?php
// register-finish.php
require_once 'database/conn.php';

// Initialize variables
$data = [];
$error = '';

// Get email from query string
$email = $_GET['email'] ?? '';
if ($email) {
    try {
        // Retrieve user data from database
        $stmt = $pdo->prepare("SELECT name, email, gender, passcode FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        if (!$data) {
            $error = "No user found with email: " . htmlspecialchars($email);
            file_put_contents('debug.log', 'No user found: ' . $email . "\n", FILE_APPEND);
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        file_put_contents('debug.log', 'Database error: ' . $e->getMessage() . "\n", FILE_APPEND);
    }
} else {
    $error = "No email provided.";
    file_put_contents('debug.log', 'No email in query string' . "\n", FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Registration Complete</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
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

        .finish-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .finish-container h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .finish-container p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .finish-container p span {
            color: #ff69b4;
            font-weight: 500;
        }

        .passcode-box {
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px;
            font-size: 24px;
            font-weight: 500;
            text-align: center;
            margin-bottom: 20px;
            user-select: all;
        }

        .proceed-btn {
            background: #6e44ff;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }

        .proceed-btn:hover {
            background: #5a33cc;
        }

        .error-message {
            color: #ff4d94;
            font-size: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div class="finish-container">
        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
            <a href="register.php" style="color: #6e44ff; text-decoration: none;">Try Again</a>
        <?php else: ?>
            <h1>Welcome, <span><?php echo htmlspecialchars($data['name']); ?>!</span></h1>
            <p>Your registration is complete.</p>
            <p>Email: <span><?php echo htmlspecialchars($data['email']); ?></span></p>
            <p>Gender: <span><?php echo htmlspecialchars($data['gender']); ?></span></p>
            <p>Your unique passcode is:</p>
            <div class="passcode-box"><?php echo htmlspecialchars($data['passcode']); ?></div>
            <p>Please copy this passcode and keep it safe. You will need it to log in.</p>
            <a href="login.php" class="proceed-btn">Proceed to Login</a>
        <?php endif; ?>
    </div>

    <?php include 'inc/footer.php'; ?>

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

        // Prevent right-click
        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>
</body>
</html>
