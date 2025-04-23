<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Payment Failed!</h1>
    </header>

    <main>
        <p><?= isset($_SESSION['message']) ? $_SESSION['message'] : "There was an issue with your payment. Please try again." ?></p>
        <a href="cart.php" class="cta-button">Go Back to Cart</a>
    </main>

    <footer>
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
// Clear the failure message after it is displayed
unset($_SESSION['message']);
?>
