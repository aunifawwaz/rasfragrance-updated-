<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's orders
$user_id = $_SESSION['user_id'];
$orders = [];

// Get basic order info from orders table
$order_query = "SELECT o.order_id, o.customer_name, o.total, o.status, o.order_date 
                FROM orders o
                JOIN order_meta om ON o.order_id = om.order_id
                WHERE om.meta_key = 'user_id' AND om.meta_value = ?
                ORDER BY o.order_date DESC";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($order = $result->fetch_assoc()) {
    // Get additional order meta data
    $meta_query = "SELECT meta_key, meta_value FROM order_meta WHERE order_id = ?";
    $meta_stmt = $conn->prepare($meta_query);
    $meta_stmt->bind_param("i", $order['order_id']);
    $meta_stmt->execute();
    $meta_result = $meta_stmt->get_result();
    
    $order_meta = [];
    while ($meta = $meta_result->fetch_assoc()) {
        $order_meta[$meta['meta_key']] = $meta['meta_value'];
    }
    
    // Get order items
    $items_query = "SELECT oi.*, p.perfume_name, p.image 
                   FROM order_items oi
                   JOIN perfume p ON oi.perfume_id = p.perfume_id
                   WHERE oi.order_id = ?";
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("i", $order['order_id']);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $order_items = [];
    while ($item = $items_result->fetch_assoc()) {
        $order_items[] = $item;
    }
    
    $orders[] = [
        'order' => $order,
        'meta' => $order_meta,
        'items' => $order_items
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Ras Fragrance</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4e7e7;
            color: #5a2a2a;
        }
        .order-history-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fff;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-canceled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .order-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        .item-details {
            flex-grow: 1;
        }
        .item-price {
            font-weight: bold;
            min-width: 100px;
            text-align: right;
        }
        .order-summary {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #777;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="order-history-container">
        <h1>Your Order History</h1>
        
        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <h3>You haven't placed any orders yet</h3>
                <p>Start shopping to see your orders here</p>
                <a href="products.php" class="btn btn-primary">Shop Now</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order_data): 
                $order = $order_data['order'];
                $meta = $order_data['meta'];
                $items = $order_data['items'];
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h4>Order #<?= $order['order_id'] ?></h4>
                            <small class="text-muted">Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></small>
                        </div>
                        <div>
                            <span class="order-status status-<?= strtolower($order['status']) ?>">
                                <?= $order['status'] ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <h5>Items</h5>
                        <?php foreach ($items as $item): ?>
                            <div class="order-item">
                                <img src="<?= htmlspecialchars($item['image'] ?: 'images/default_image.jpeg') ?>" 
                                     alt="<?= htmlspecialchars($item['perfume_name']) ?>" 
                                     class="item-image">
                                <div class="item-details">
                                    <h6><?= htmlspecialchars($item['perfume_name']) ?></h6>
                                    <div>Quantity: <?= $item['quantity'] ?></div>
                                </div>
                                <div class="item-price">
                                    RM <?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="order-summary">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Shipping Information</h5>
                                    <p>
                                        <strong><?= htmlspecialchars($order['customer_name']) ?></strong><br>
                                        <?= htmlspecialchars($meta['address'] ?? '') ?><br>
                                        Phone: <?= htmlspecialchars($meta['phone'] ?? '') ?><br>
                                        Email: <?= htmlspecialchars($meta['email'] ?? '') ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Order Summary</h5>
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal:</span>
                                        <span>RM <?= number_format($meta['subtotal'] ?? $order['total'], 2) ?></span>
                                    </div>
                                    <?php if (isset($meta['points_discount']) && $meta['points_discount'] > 0): ?>
                                        <div class="d-flex justify-content-between">
                                            <span>Points Discount:</span>
                                            <span>-RM <?= number_format($meta['points_discount'], 2) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between mt-2">
                                        <strong>Total:</strong>
                                        <strong>RM <?= number_format($order['total'], 2) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Payment Method:</span>
                                        <span><?= ucfirst(str_replace('_', ' ', $meta['payment_method'] ?? 'Unknown')) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>