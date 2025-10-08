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
        justify-content: center; /* Center the logo */
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

    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            text-align: center;
        }
    }
</style>
