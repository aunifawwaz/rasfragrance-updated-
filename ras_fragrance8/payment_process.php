<?php
session_start();

// Check if user details are available
if (!isset($_SESSION['user_details']) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Get user details and cart items
$user_details = $_SESSION['user_details'];
$cart = $_SESSION['cart'];
$grand_total = 0;

// Calculate grand total
foreach ($cart as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}

// Simulate payment processing (this is where you would integrate an actual payment gateway)
$payment_successful = false;

// Simulate payment process (randomly generate success or failure for demonstration purposes)
if (rand(0, 1) == 1) {
    $payment_successful = true;
}

if ($payment_successful) {
    // Clear the cart after successful payment
    unset($_SESSION['cart']);

    // Redirect to the success page (you can create a success page to display a success message)
    $_SESSION['message'] = "Your payment was successful! Thank you for your purchase.";
    header('Location: payment_success.php');
    exit();
} else {
    // Redirect to failure page if payment fails (you can create a failure page for handling errors)
    $_SESSION['message'] = "Payment failed. Please try again.";
    header('Location: payment_failure.php');
    exit();
}
?>
