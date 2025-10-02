<?php
// terms.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Terms and Conditions</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        .terms-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 600px;
            width: 100%;
            text-align: left;
        }

        .terms-container h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .terms-container h2 {
            font-size: 20px;
            font-weight: 500;
            color: #333;
            margin: 20px 0 10px;
        }

        .terms-container p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .terms-container p span {
            color: #ff69b4;
            font-weight: 500;
        }

        .terms-container a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
        }

        .terms-container a:hover {
            text-decoration: underline;
        }

        .back-link {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .terms-container {
                padding: 20px;
            }

            .terms-container h1 {
                font-size: 22px;
            }

            .terms-container h2 {
                font-size: 18px;
            }

            .terms-container p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div class="terms-container">
        <h1>Terms and Conditions</h1>
        <p>Welcome to <span>Task Tube</span>. By using our website and services, you agree to comply with and be bound by the following terms and conditions. Please read them carefully.</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By accessing or using Task Tube, you agree to these Terms and Conditions and our <a href="privacy.php">Privacy Policy</a>. If you do not agree, please do not use our services.</p>

        <h2>2. Account Registration</h2>
        <p>To access certain features, you must register an account with a valid email and a 5-digit passcode. You are responsible for maintaining the confidentiality of your account credentials.</p>

        <h2>3. Use of Services</h2>
        <p>You agree to use Task Tube for lawful purposes only. You may not use our services to engage in any illegal activities or to violate the rights of others.</p>

        <h2>4. User Conduct</h2>
        <p>Do not attempt to hack, disrupt, or misuse our platform. Any unauthorized access or activity may result in account suspension or legal action.</p>

        <h2>5. Termination</h2>
        <p>We reserve the right to suspend or terminate your account if you violate these terms or engage in prohibited activities.</p>

        <h2>6. Changes to Terms</h2>
        <p>We may update these Terms and Conditions periodically. Continued use of Task Tube after changes constitutes acceptance of the new terms.</p>

        <h2>7. Contact Us</h2>
        <p>If you have questions about these terms, please contact us via our <a href="contact.php">Contact page</a>.</p>

        <p class="back-link">Return to <a href="index.php">Home</a></p>
    </div>

    <?php include 'inc/footer.php'; ?>
</body>
</html>
