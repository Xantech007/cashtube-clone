<?php
// inc/header-actions.php
?>

<style>
    .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .translate-btn, .theme-toggle {
        background: var(--accent-color);
        color: #fff;
        border: none;
        padding: 9px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .translate-btn:hover, .theme-toggle:hover {
        background: var(--accent-hover);
        transform: translateY(-1px);
    }

    /* Google Translate Specific Styling */
    #google_translate_element {
        display: inline-block;
    }

    .goog-te-gadget-simple {
        background: #ffffff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        padding: 7px 14px !important;
        font-size: 14px !important;
        box-shadow: 0 2px 6px var(--shadow-color);
    }

    .goog-te-gadget-icon, .goog-te-gadget img {
        display: none !important;
    }

    /* Hide Google Translate top bar */
    .goog-te-banner-frame.skiptranslate,
    iframe.goog-te-banner-frame {
        display: none !important;
        height: 0 !important;
        visibility: hidden !important;
    }

    body {
        top: 0 !important;
    }

    @media (max-width: 480px) {
        .translate-btn, .theme-toggle {
            padding: 8px 12px;
            font-size: 13px;
        }
    }
</style>

<div class="header-actions">
    <!-- Language Translator -->
    <?php include('translate.php'); ?>

    <!-- Theme Toggle Button (already existing) -->
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
        <i class="fas fa-moon"></i>
    </button>
</div>
