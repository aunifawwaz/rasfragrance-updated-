<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Payment Successful!</h1>
    </header>

    <main>
        <p><?= isset($_SESSION['message']) ? $_SESSION['message'] : "Thank you for your purchase!" ?></p>
        <a href="home.php" class="cta-button">Go to Home</a>
    </main>

    <footer>
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
// Clear the success message after it is displayed
unset($_SESSION['message']);
?>
