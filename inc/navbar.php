<?php
// navbar.php
?>
<style>
    .ham-menu {
        position: fixed;
        top: 70px;
        left: 0;
        width: 100%;
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
        z-index: 999;
    }
    .ham-menu.on {
        transform: translateX(0);
    }
    .ham-menu ul {
        list-style: none;
        padding: 20px;
        margin: 0;
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
        color: #ff69b4;
    }
    .ham-menu ul li.active a {
        color: #6e44ff;
        font-weight: 600;
    }

    /* Translator Styles */
    #google_translate_element {
        margin: 12px 15px 12px 0;
    }
    .goog-te-gadget-simple {
        background: #ffffff !important;
        border: 1px solid #ddd !important;
        border-radius: 6px !important;
        padding: 6px 12px !important;
        font-size: 14px !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    }
    .goog-te-gadget-icon, .goog-te-gadget img {
        display: none !important;
    }

    /* Hide Google Translate top banner */
    .goog-te-banner-frame.skiptranslate,
    iframe.goog-te-banner-frame {
        display: none !important;
        height: 0 !important;
        visibility: hidden !important;
    }
    body {
        top: 0 !important;
    }

    @media (min-width: 768px) {
        .ham-menu {
            position: static;
            transform: none;
            box-shadow: none;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background: transparent;
            gap: 15px;
        }
        .ham-menu ul {
            display: flex;
            gap: 20px;
            padding: 0;
            margin: 0;
        }
        .ham-menu ul li {
            margin: 0;
        }
        .ham-menu ul li a {
            font-size: 15px;
        }

        /* Adjust translator position on desktop */
        #google_translate_element {
            margin: 0 15px 0 0;
        }
    }

    @media (max-width: 480px) {
        .ham-menu {
            top: 60px;
        }
        .ham-menu ul {
            padding: 15px;
        }
        .ham-menu ul li a {
            font-size: 14px;
        }
    }
</style>

<nav id="ham-navigation" class="ham-menu">
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="terms.php">Terms</a></li>
        <li><a href="privacy.php">Privacy</a></li>
    </ul>

    <!-- Language Translator -->
    <?php include('translate.php'); ?>

</nav>
