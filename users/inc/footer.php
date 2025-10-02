<?php
// users/inc/footer.php
?>

<footer>
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="img/top.png" alt="Task Tube Logo">
                <p>Task Tube &copy; <?php echo date('Y'); ?>. All rights reserved.</p>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="../terms.php">Terms and Conditions</a></li>
                    <li><a href="../privacy.php">Privacy Policy</a></li>
                    <li><a href="../contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-social">
                <h3>Follow Us</h3>
                <a href="https://twitter.com/tasktube" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="https://facebook.com/tasktube" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://instagram.com/tasktube" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
</footer>

<style>
    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        background: var(--card-bg);
        box-shadow: 0 -2px 4px var(--shadow-color);
        padding: 20px 0;
        z-index: 1000;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .footer-logo img {
        height: 40px;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .footer-logo p {
        font-size: 14px;
        color: var(--subtext-color);
    }

    .footer-links h3,
    .footer-social h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 10px;
    }

    .footer-links ul {
        list-style: none;
    }

    .footer-links ul li {
        margin-bottom: 8px;
    }

    .footer-links a,
    .footer-social a {
        color: #22c55e;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s ease;
    }

    .footer-links a:hover,
    .footer-social a:hover {
        color: #16a34a;
        text-decoration: underline;
    }

    .footer-social a {
        margin: 0 10px;
        font-size: 18px;
    }

    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            text-align: center;
        }

        .footer-links,
        .footer-social {
            margin: 20px 0;
        }

        .footer-social a {
            margin: 0 15px;
        }
    }
</style>
