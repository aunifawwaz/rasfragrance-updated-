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

        footer {
            background-color: #f8f9fa; /* Light gray background */
            padding: 30px 0;
            font-size: 14px;
            color: #333;
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            padding: 0 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-section {
            flex: 1;
            padding: 10px;
        }

        .footer-section h4 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .footer-section a {
            text-decoration: none;
            color: #333;
            display: block;
            margin: 5px 0;
        }

        .footer-section a:hover {
            color: #8C3F49; /* Maroon color for hover */
        }

        .social-icons a {
            margin: 0 10px;
            color: #333;
            font-size: 18px;
            text-decoration: none;
        }

        .social-icons a:hover {
            color: #8C3F49; /* Maroon color for hover */
        }

        .email-subscription {
            text-align: center;
            margin: 30px 0;
        }

        .email-subscription h4 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .email-subscription input[type="email"] {
            padding: 10px;
            width: 300px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .email-subscription button {
            padding: 10px 15px;
            background-color: #8C3F49;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .email-subscription button:hover {
            background-color: #a64d4d;
        }

        .footer-bottom {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }

        .footer-bottom a {
            text-decoration: none;
            color: #777;
        }

        .footer-bottom a:hover {
            color: #8C3F49;
        }
    </style>
</head>
<body>

<!-- Footer -->
<footer>
    <div class="footer-container">
        <!-- Customer Care -->
        <div class="footer-section">
            <h4>CUSTOMER CARE</h4>
            <a href="shipping_policy.php">Shipping Policy</a>
            <a href="privacy_policy.php">Privacy Policy</a>
            <a href="contact.php">Contact</a>
        </div>

        <!-- Support -->
        <div class="footer-section">
            <h4>SUPPORT</h4>
            <a href="blog.php">Blog</a>
        </div>

        <!-- About Us -->
        <div class="footer-section">
            <h4>ABOUT RAS FRAGRANCE</h4>
            <p>RAS FRAGRANCE, based in Johor Bahru, creates affordable, high-quality perfumes inspired by iconic scents. With dupes and inspired creations, everyone can enjoy luxury fragrances that match their personality without overspending. More than just a scent—it's about confidence, individuality, and self-expression.✨  
</p>
        </div>
    </div>

    <!-- Email Subscription -->
    <div class="email-subscription">
        <h4>Join our RAS FRAGRANCE community for tips, event updates, and promos</h4>
        <form action="subscribe.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>

    <!-- Social Media Icons -->
    <div class="social-icons text-center">
        <a href="https://www.facebook.com/yourpage" target="_blank">
            <i class="fab fa-facebook"></i>
        </a>
        <a href="https://www.instagram.com/yourpage" target="_blank">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="https://www.tiktok.com/@yourpage" target="_blank">
            <i class="fab fa-tiktok"></i>
        </a>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
        <p><a href="refund_policy.php">Refund Policy</a> | <a href="terms_of_service.php">Terms of Service</a></p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
