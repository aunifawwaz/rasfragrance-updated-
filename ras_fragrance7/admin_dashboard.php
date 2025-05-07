<?php
session_start();

// Ensure session role exists and is admin
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Include your database connection
include 'db_connection.php';

// Fetch statistics
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM User")->fetch_row()[0],
    'products' => $conn->query("SELECT COUNT(*) FROM Perfume")->fetch_row()[0],
    'orders' => $conn->query("SELECT COUNT(*) FROM Orders")->fetch_row()[0],
    //'revenue' => $conn->query("SELECT SUM(total_amount) FROM Orders WHERE status = 'completed'")->fetch_row()[0] ?? 0
];

// Fetch recent orders
/*$recent_orders = $conn->query("SELECT o.order_id, u.username, o.order_date, o.total_amount 
                              FROM Orders o JOIN User u ON o.user_id = u.user_id 
                              ORDER BY o.order_date DESC LIMIT 5");

// Fetch popular products
$popular_products = $conn->query("SELECT p.name, COUNT(oi.item_id) as sales 
                                 FROM Order_Items oi JOIN Perfume p ON oi.perfume_id = p.perfume_id 
                                 GROUP BY p.perfume_id ORDER BY sales DESC LIMIT 5");*/

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RAS Fragrance</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8C3F49;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            min-height: 100vh;
            background: var(--dark);
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 0.75rem 1rem;
            margin-bottom: 0.2rem;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .stat-card .count {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .stat-card .label {
            color: var(--secondary);
            font-size: 1rem;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_menu.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-calendar"></i> <?= date('F j, Y') ?>
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-users"></i>
                                <div class="count"><?= $stats['users'] ?></div>
                                <div class="label">Total Users</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-wine-bottle"></i>
                                <div class="count"><?= $stats['products'] ?></div>
                                <div class="label">Total Products</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-shopping-cart"></i>
                                <div class="count"><?= $stats['orders'] ?></div>
                                <div class="label">Total Orders</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-dollar-sign"></i>
                                <div class="count">RM<?= number_format($stats['revenue'], 2) ?></div>
                                <div class="label">Total Revenue</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Orders</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($order = $recent_orders->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?= $order['order_id'] ?></td>
                                                <td><?= htmlspecialchars($order['username']) ?></td>
                                                <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                                                <td>RM<?= number_format($order['total_amount'], 2) ?></td>
                                                <td>
                                                    <a href="order_details.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-primary">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Popular Products -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Popular Products</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php while($product = $popular_products->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($product['name']) ?>
                                        <span class="badge bg-primary rounded-pill"><?= $product['sales'] ?></span>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>