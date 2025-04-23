<!--?php
session_start();
include 'db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Get the order details
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id > 0) {
    // Fetch order details from `order_items`
    $query = "SELECT oi.*, p.perfume_name FROM order_items oi 
              JOIN perfume p ON oi.perfume_id = p.perfume_id
              WHERE oi.order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $order_query = "SELECT * FROM orders WHERE order_id = ?";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    $order = $order_result->fetch_assoc();
} else {
    echo "Invalid order ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Order Details</h1>
        <a href="admin_orders.php" class="cta-button">Back to Orders</a>
    </header>

    <main>
        <h2>Order ID: <?= $order['order_id']; ?></h2>
        <p><strong>User ID:</strong> <?= $order['user_id']; ?></p>
        <p><strong>Total Price:</strong> RM <?= number_format($order['total_price'], 2); ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['status']); ?></p>
        <p><strong>Order Date:</strong> <?= $order['created_at']; ?></p>

        <h3>Order Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Perfume Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['perfume_name']); ?></td>
                        <td><?= $item['quantity']; ?></td>
                        <td>RM <?= number_format($item['price'], 2); ?></td>
                        <td>RM <?= number_format($item['total'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>
</body>
</html>
