<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAS FRAGRANCE</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: black;
            color: white;
            border-radius: 50%;
            padding: 0.2rem 0.4rem;
            font-size: 10px;
        }

        .shipping-banner {
            background-color: #000;
            text-align: center;
            padding: 8px 0;
            font-size: 14px;
            color: white;
        }

        /* Main header container */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #8C3F49; /* Ensure the maroon header */
        }

        /* Logo styling - now on the left */
        .logo {
            width: 100px; /* Reduced logo size */
            height: auto;
            order: 1; /* Logo first */
        }

        /* Navigation menu styling - now on the right */
        .main-menu {
            display: flex;
            gap: 20px; /* Reduced gap between menu items */
            list-style: none;
            padding: 0;
            margin: 0;
            order: 3; /* Menu last */
            margin-left: auto; /* Pushes menu to the right */
        }

        .main-menu a {
            text-decoration: none;
            color: #fff; /* White text */
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Icons container - now in the middle */
        .header-icons {
            display: flex;
            gap: 20px;
            align-items: center;
            order: 2; /* Icons in the middle */
            margin: 0 30px; /* Space around icons */
        }

        .header-icons a {
            color: #fff; /* White icons */
            font-size: 16px;
            position: relative;
        }

        .points-notification {
            background-color: #f8f9fa;
            text-align: center;
            padding: 10px 0;
            font-size: 14px;
            border-bottom: 1px solid #e9ecef;
        }

        .whatsapp-link {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background-color: #25D366;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                padding: 10px;
            }
            .logo, .main-menu, .header-icons {
                order: initial;
                margin: 10px 0;
            }
            .main-menu {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Shipping Banner -->
    <div class="shipping-banner">
        Free shipping for orders over RM150
    </div>

    <!-- Main Header -->
    <div class="header-container">
        <!-- Logo (now on the left) -->
        <a href="home.php">
            <img src="images/logo.jpeg" alt="RAS FRAGRANCE Logo" class="logo">
        </a>

        <!-- Icons (now in the middle) -->
        <div class="header-icons">
            <a href="search.php" title="Search">
                <i class="fas fa-search"></i>
            </a>

            <?php if  (isset($_SESSION['user_id'])): ?>
                <!-- Logged in state - Show profile link -->
                <a href="profile.php" title="Profile">
                    <i class="fas fa-user"></i>
                </a>
                
                <!-- In your header.php, update the cart icon section: -->
<a href="cart.php" title="Cart" style="position: relative;">
    <i class="fas fa-shopping-bag"></i>
    <?php 
    // Calculate cart count if not set
    if (!isset($_SESSION['cart_count']) && isset($_SESSION['cart'])) {
        $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
    }
    ?>
    <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
        <span class="cart-count"><?= $_SESSION['cart_count'] ?></span>
    <?php endif; ?>
</a>

                    <a href="logout.php" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            <?php else: ?>
                <!-- Not logged in state - Show login link -->
                </a>
                <a href="login.php" title="Login">
                    <i class="fas fa-user"></i>
                </a>
                <a href="cart.php" title="Cart" style="position: relative;">
                    <i class="fas fa-shopping-bag"></i>
                    <?php if (isset($_SESSION['cart_count'])): ?>
                        <span class="cart-count"><?= $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Navigation Menu (now on the right) -->
        <nav>
            <ul class="main-menu">
                <li><a href="home.php">HOME</a></li>
                <li><a href="products.php">SHOP</a></li>
                <li><a href="quiz.php">QUIZ</a></li>
                <li><a href="blog.php">BLOG</a></li>
                <li><a href="about.php">ABOUT</a></li>
                <li><a href="contact.php">CONTACT</a></li>
            </ul>
        </nav>
    </div>

    <!-- Points Notification -->
    <div class="points-notification">
        Did you know you can earn points for signing up, making purchases and more?
    </div>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/yourphonenumber" class="whatsapp-link">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
