<?php
// privacy.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tube - Privacy Policy</title>
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

        .privacy-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 600px;
            width: 100%;
            text-align: left;
        }

        .privacy-container h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .privacy-container h2 {
            font-size: 20px;
            font-weight: 500;
            color: #333;
            margin: 20px 0 10px;
        }

        .privacy-container p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .privacy-container p span {
            color: #ff69b4;
            font-weight: 500;
        }

        .privacy-container a {
            color: #6e44ff;
            text-decoration: none;
            font-weight: 500;
        }

        .privacy-container a:hover {
            text-decoration: underline;
        }

        .back-link {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .privacy-container {
                padding: 20px;
            }

            .privacy-container h1 {
                font-size: 22px;
            }

            .privacy-container h2 {
                font-size: 18px;
            }

            .privacy-container p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <div class="privacy-container">
        <h1>Privacy Policy</h1>
        <p>Welcome to <span>Task Tube</span>. We are committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your personal information when you use our website and services.</p>

        <h2>1. Information We Collect</h2>
        <p>We may collect personal information such as your email address and 5-digit passcode when you register or sign in. We also collect usage data, such as browsing activity, to improve our services.</p>

        <h2>2. How We Use Your Information</h2>
        <p>Your information is used to provide and improve Task Tube services, process account activities, and communicate with you. We may use data for analytics to enhance user experience.</p>

        <h2>3. Cookies and Tracking</h2>
        <p>We use cookies to track user activity and improve functionality. You can manage cookie preferences through your browser settings.</p>

        <h2>4. Data Sharing</h2>
        <p>We do not sell or share your personal information with third parties, except as required by law or to provide our services (e.g., with trusted service providers).</p>

        <h2>5. Data Security</h2>
        <p>We implement reasonable security measures to protect your data. However, no system is completely secure, and you share information at your own risk.</p>

        <h2>6. Your Rights</h2>
        <p>You may request access, correction, or deletion of your personal information by contacting us via our <a href="contact.php">Contact page</a>.</p>

        <h2>7. Changes to This Policy</h2>
        <p>We may update this Privacy Policy periodically. Changes will be posted on this page, and continued use of Task Tube constitutes acceptance.</p>

        <h2>8. Contact Us</h2>
        <p>For questions about this Privacy Policy, please visit our <a href="contact.php">Contact page</a>.</p>

        <p class="back-link">Return to <a href="index.php">Home</a></p>
    </div>

    <?php include 'inc/footer.php'; ?>
</body>
</html>
