<?php
session_start();
if (!isset($_SESSION['order_complete'])) {
    header("Location: cart.php");
    exit();
}
unset($_SESSION['order_complete']);

$order_id = $_GET['order_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Complete</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container my-5">
        <div class="text-center">
            <h1 class="text-success">Order Complete!</h1>
            <p class="lead">Thank you for your purchase.</p>
            <p>Your order ID is: <strong>#<?= htmlspecialchars($order_id) ?></strong></p>
            <p>We've sent a confirmation email to your registered address.</p>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>