<?php
session_start();

// Check if user details are available, otherwise redirect to the cart page
if (!isset($_SESSION['user_details']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$user_details = $_SESSION['user_details'];
$cart = $_SESSION['cart'];
$grand_total = 0;

// Calculate grand total
foreach ($cart as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Confirm Your Payment</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        header {
            background-color: #8C3F49;
            color: white;
            padding: 15px 0;
            text-align: center;
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

        .cta-button {
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

        .cta-button:hover {
            background-color: #a64d4d;
        }

        footer {
            text-align: center;
            padding: 15px;
            background-color: #8C3F49;
            color: white;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Payment - Confirm Your Order</h1>
        <a href="cart.php" class="cta-button">Back to Cart</a>
    </header>

    <main>
        <h2>Order Summary</h2>
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
                <?php foreach ($cart as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['perfume_name']); ?></td>
                        <td>RM <?= number_format($item['price'], 2); ?></td>
                        <td><?= $item['quantity']; ?></td>
                        <td>RM <?= number_format($item['price'] * $item['quantity'], 2); ?></td>
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

        <h3>Billing Information</h3>
        <table>
            <tr>
                <th>Name</th>
                <td><?= htmlspecialchars($user_details['name']); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?= htmlspecialchars($user_details['address']); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?= htmlspecialchars($user_details['phone']); ?></td>
            </tr>
        </table>

        <h3>Payment Details</h3>
        <form action="payment_process.php" method="post">
            <button type="submit" class="cta-button">Proceed to Payment</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>
</body>
</html>
