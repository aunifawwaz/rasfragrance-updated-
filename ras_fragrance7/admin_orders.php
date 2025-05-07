<?php
session_start();
include 'db_connection.php';

// Check if admin is logged in (assuming you have an admin session)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch all orders from the `orders` table
$query = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Orders</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin - Orders</h1>
        <a href="admin_dashboard.php" class="cta-button">Back to Dashboard</a>
    </header>

    <main>
        <h2>All Orders</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $order['order_id']; ?></td>
                            <td><?= htmlspecialchars($order['user_id']); ?></td>
                            <td>RM <?= number_format($order['total_price'], 2); ?></td>
                            <td><?= htmlspecialchars($order['status']); ?></td>
                            <td><?= $order['created_at']; ?></td>
                            <td><a href="view_order_details.php?order_id=<?= $order['order_id']; ?>" class="cta-button">View Details</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>
</body>
</html-->
