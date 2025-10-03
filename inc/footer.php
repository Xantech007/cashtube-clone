<?php
// footer.php
?>
<footer id="footer">
    <a href="https://tasktube.app/terms.php">Terms of Service</a>
    <a href="https://tasktube.app/privacy.php">Privacy Policy</a>
    <p>&copy; <?php echo date("Y"); ?> Task Tube. All rights reserved.</p>
</footer>

<style>
    footer {
        width: 100%;
        background: #fff;
        padding: 20px;
        text-align: center;
        box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
        display: none; /* Hidden by default */
    }

    footer.visible {
        display: block; /* Show when visible */
    }

    footer a {
        color: #6e44ff;
        font-weight: 500;
        text-decoration: none;
        margin: 0 10px;
    }

    footer a:hover {
        text-decoration: underline;
    }

    footer p {
        margin: 10px 0 0;
        color: #333;
        font-size: 14px;
    }
</style>

<script>
    window.addEventListener('scroll', function() {
        const footer = document.getElementById('footer');
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        const scrollPosition = window.scrollY || window.pageYOffset;

        // Show footer when scrolled to bottom
        if (scrollPosition + windowHeight >= documentHeight - 50) {
            footer.classList.add('visible');
        } else {
            footer.classList.remove('visible');
        }
    });
</script>
