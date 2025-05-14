<?php
session_start();

include 'db_connection.php';

// Redirect to cart if the cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Check if form is submitted to proceed to payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['address'], $_POST['phone'])) {
    // Save user details
    $_SESSION['user_details'] = [
        'name' => $_POST['name'],
        'address' => $_POST['address'],
        'phone' => $_POST['phone'],
    ];

    // Redirect to the payment page
    header('Location: payment.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Your Order</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Confirm Your Order</h1>
        <a href="cart.php" class="cta-button">Back to Cart</a>
    </header>

    <main>
    <h2>Order Summary</h2>
    <?php if (!empty($_SESSION['cart'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Perfume</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $grand_total = 0; ?>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <?php 
                    $total = $item['price'] * $item['quantity']; 
                    $grand_total += $total;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['perfume_name']); ?></td>
                        <td>RM <?= number_format($item['price'], 2); ?></td>
                        <td><?= $item['quantity']; ?></td>
                        <td>RM <?= number_format($total, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="grand-total">Grand Total</td>
                    <td class="grand-total">RM <?= number_format($grand_total, 2); ?></td>
                </tr>
            </tfoot>
        </table>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>

    <h3>Enter Your Details</h3>
    <form action="confirmation.php" method="post">
        <label for="name">Full Name:</label>
        <input type="text" name="name" required>
        <label for="address">Address:</label>
        <textarea name="address" required></textarea>
        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" required>
        <button type="submit" class="cta-button">Proceed to Payment</button>
    </form>
</main>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f9f9f9;
    }

    main {
        padding: 20px;
        max-width: 1000px;
        margin: 0 auto;
    }

    h2, h3 {
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        background-color: white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    table thead {
        background-color: #8C3F49;
        color: white;
    }

    table th, table td {
        text-align: left;
        padding: 15px;
        border: 1px solid #ddd;
    }

    table td {
        text-align: center;
    }

    table th {
        text-align: center;
        font-size: 16px;
        font-weight: bold;
    }

    .grand-total {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }

    input[type="text"], textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }

    button.cta-button {
        display: inline-block;
        padding: 12px 20px;
        background-color: #8C3F49;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-size: 16px;
        margin-top: 20px;
        text-align: center;
    }

    button.cta-button:hover {
        background-color: #a64d4d;
    }

    footer {
        text-align: center;
        padding: 15px;
        background-color: #8C3F49;
        color: white;
        margin-top: 30px;
    }

    .empty-cart {
        text-align: center;
        color: #555;
        font-size: 18px;
    }
</style>
    <footer>
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>
</body>
</html>
