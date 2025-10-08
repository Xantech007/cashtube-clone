<?php
// footer.php
?>
<footer id="footer">
    <p>&copy; <?php echo date("Y"); ?> Task Tube. All rights reserved.</p>
</footer>

<style>
    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        background: #fff;
        padding: 20px;
        text-align: center;
        box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    footer.visible {
        opacity: 1;
        visibility: visible;
    }

    footer p {
        margin: 0;
        color: #333;
        font-size: 14px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        footer {
            padding: 15px;
        }

        footer p {
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        footer {
            padding: 10px;
        }

        footer p {
            font-size: 12px;
        }
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
