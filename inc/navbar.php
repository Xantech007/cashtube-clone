<?php
// inc/navbar.php
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
        color: #6e44ff;
    }

    @media (min-width: 768px) {
        .ham-menu {
            position: static;
            transform: none;
            box-shadow: none;
            display: flex;
            justify-content: flex-end;
            background: transparent;
        }

        .ham-menu ul {
            display: flex;
            gap: 20px;
            padding: 0;
        }

        .ham-menu ul li {
            margin: 0;
        }
    }
</style>

<nav id="ham-navigation" class="ham-menu">
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>
</nav>
