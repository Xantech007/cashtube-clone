<?php
// inc/top-translator.php
?>

<style>
    .top-translator-bar {
        background: var(--card-bg);
        border-bottom: 1px solid var(--border-color);
        padding: 12px 0;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1050;
        box-shadow: 0 2px 8px var(--shadow-color);
    }

    .top-translator-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #google_translate_element {
        display: inline-block;
    }

    .goog-te-gadget-simple {
        background: #ffffff !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 8px !important;
        padding: 8px 18px !important;
        font-size: 15px !important;
        box-shadow: 0 2px 6px var(--shadow-color);
    }

    .goog-te-gadget-icon,
    .goog-te-gadget img {
        display: none !important;
    }

    /* Hide the annoying Google Translate top banner */
    .goog-te-banner-frame.skiptranslate,
    iframe.goog-te-banner-frame {
        display: none !important;
        height: 0 !important;
        visibility: hidden !important;
    }

    /* Push main content down to avoid overlap */
    .main-content {
        margin-top: 68px !important;   /* Adjust this value if needed */
    }

    @media (max-width: 768px) {
        .top-translator-bar {
            padding: 10px 0;
        }
        .top-translator-container {
            padding: 0 16px;
        }
        .main-content {
            margin-top: 62px !important;
        }
    }
</style>

<!-- Top Centered Translator Bar -->
<div class="top-translator-bar">
    <div class="top-translator-container">
        <?php include('translate.php'); ?>
    </div>
</div>
