<?php
session_start();
include 'db_connection.php';

// Get user info if logged in
$user_info = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT username, email, phone_no, address FROM User WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_info = $result->fetch_assoc();
}

// Function to sync cart with database
function syncCartWithDatabase($user_id, $cart_items, $conn) {
    // Clear existing cart items for this user
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");
    
    // Insert current cart items
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, perfume_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $item['perfume_id'], $item['quantity']);
        $stmt->execute();
    }
}

// Function to load cart from database
function loadCartFromDatabase($user_id, $conn) {
    $cart = [];
    $result = $conn->query("SELECT p.perfume_id, p.perfume_name, p.price, c.quantity 
                           FROM cart c
                           JOIN perfume p ON c.perfume_id = p.perfume_id
                           WHERE c.user_id = $user_id");
    
    while ($row = $result->fetch_assoc()) {
        $cart[] = $row;
    }
    return $cart;
}

// Initialize or load cart
if (isset($_SESSION['user_id'])) {
    // Load cart from database if user is logged in
    $_SESSION['cart'] = loadCartFromDatabase($_SESSION['user_id'], $conn);
} elseif (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['perfume_id'], $_POST['quantity'])) {
    $perfume_id = intval($_POST['perfume_id']);
    $quantity = intval($_POST['quantity']);

    // Fetch perfume details
    $perfume_query = "SELECT perfume_id, perfume_name, price FROM perfume WHERE perfume_id = ?";
    $stmt = $conn->prepare($perfume_query);
    $stmt->bind_param("i", $perfume_id);
    $stmt->execute();
    $perfume_result = $stmt->get_result();
    $perfume = $perfume_result->fetch_assoc();

    if ($perfume) {
        // Add to cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['perfume_id'] == $perfume_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'perfume_id' => $perfume['perfume_id'],
                'perfume_name' => $perfume['perfume_name'],
                'price' => $perfume['price'],
                'quantity' => $quantity
            ];
        }
        
        // Sync with database if logged in
        if (isset($_SESSION['user_id'])) {
            syncCartWithDatabase($_SESSION['user_id'], $_SESSION['cart'], $conn);
        }
        
        $_SESSION['message'] = "Perfume added to cart successfully!";
    } else {
        $_SESSION['message'] = "Perfume not found.";
    }
    
    // Update cart count
    updateCartCount();
    header("Location: cart.php");
    exit();
}

// Handle remove item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $remove_id = intval($_POST['remove_id']);
    foreach ($_SESSION['cart'] as $key => &$item) {
        if ($item['perfume_id'] == $remove_id) {
            if ($item['quantity'] > 1) {
                $item['quantity']--;
            } else {
                unset($_SESSION['cart'][$key]);
            }
            break;
        }
    }
    
    // Sync with database if logged in
    if (isset($_SESSION['user_id'])) {
        syncCartWithDatabase($_SESSION['user_id'], $_SESSION['cart'], $conn);
    }
    
    $_SESSION['message'] = "Item updated in the cart.";
    updateCartCount();
    header("Location: cart.php");
    exit();
}

// Function to update cart count in session
function updateCartCount() {
    $_SESSION['cart_count'] = 0;
    if (!empty($_SESSION['cart'])) {
        $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
    }
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // Get user info from form or session
    $customer_name = isset($_POST['name']) ? trim($_POST['name']) : $user_info['username'];
    $email = isset($_POST['email']) ? trim($_POST['email']) : $user_info['email'];
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : $user_info['phone_no'];
    $address = isset($_POST['address']) ? trim($_POST['address']) : $user_info['address'];
    $payment_method = $_POST['payment_method'];
    $loyalty_points_used = isset($_POST['use_loyalty_points']) ? (int)$_POST['loyalty_points'] : 0;
    
    // Basic validation
    $errors = [];
    if (empty($customer_name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($_SESSION['cart'])) $errors[] = "Your cart is empty";
    
    if (empty($errors)) {
        // Calculate total
        $subtotal = array_reduce($_SESSION['cart'], function($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
        
        // Apply loyalty points discount (example: 1 point = RM0.10)
        $points_discount = min($loyalty_points_used * 0.10, $subtotal);
        $total = $subtotal - $points_discount;
        
        // Create order in database
        try {
            $conn->begin_transaction();
            
            // Insert order (using your existing table structure)
            $stmt = $conn->prepare("INSERT INTO orders (customer_name, total, status) VALUES (?, ?, 'Pending')");
            $stmt->bind_param("sd", $customer_name, $total);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Store additional order details in order_meta table
            $stmt = $conn->prepare("INSERT INTO order_meta (order_id, meta_key, meta_value) VALUES (?, ?, ?)");
            $meta_data = [
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'payment_method' => $payment_method,
                'loyalty_points_used' => $loyalty_points_used,
                'subtotal' => $subtotal,
                'points_discount' => $points_discount,
                'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL
            ];
            
            foreach ($meta_data as $key => $value) {
                $stmt->bind_param("iss", $order_id, $key, $value);
                $stmt->execute();
            }
            
            // Store order items in order_items table
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, perfume_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $item['perfume_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }
            
            // Update user's loyalty points if logged in
            if (isset($_SESSION['user_id']) && $loyalty_points_used > 0) {
                $stmt = $conn->prepare("UPDATE users SET loyalty_points = loyalty_points - ? WHERE user_id = ?");
                $stmt->bind_param("ii", $loyalty_points_used, $_SESSION['user_id']);
                $stmt->execute();
            }
            
            $conn->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            $_SESSION['cart_count'] = 0;
            if (isset($_SESSION['user_id'])) {
                $conn->query("DELETE FROM cart WHERE user_id = " . $_SESSION['user_id']);
            }
            
            // Redirect to thank you page
            $_SESSION['order_complete'] = true;
            header("Location: order_complete.php?order_id=$order_id");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Error processing order: " . $e->getMessage();
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_data'] = [
                'name' => $customer_name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'payment_method' => $payment_method,
                'loyalty_points_used' => $loyalty_points_used
            ];
            header("Location: cart.php?checkout=1");
            exit();
        }
    } else {
        $_SESSION['checkout_errors'] = $errors;
        $_SESSION['checkout_data'] = [
            'name' => $customer_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'payment_method' => $payment_method,
            'loyalty_points_used' => $loyalty_points_used
        ];
        header("Location: cart.php?checkout=1");
        exit();
    }
}

// Initialize cart count
updateCartCount();

// Check if we should show checkout form
$show_checkout = isset($_GET['checkout']) && !empty($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .cart-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .cart-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .product-info {
            display: flex;
            align-items: center;
            flex-grow: 1;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .product-details {
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .bestseller-tag {
            background: #FFD700;
            color: #000;
            font-size: 12px;
            padding: 2px 5px;
            border-radius: 3px;
            display: inline-block;
            margin-left: 10px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            margin: 0 20px;
        }

        .quantity-btn {
            background: #f0f0f0;
            border: none;
            width: 30px;
            height: 30px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .quantity-value {
            margin: 0 10px;
            font-size: 16px;
            min-width: 20px;
            text-align: center;
        }

        .total-price {
            font-weight: bold;
            font-size: 18px;
            min-width: 100px;
            text-align: right;
        }

        .subtotal-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: right;
        }

        .checkout-btn {
            background: #8C3F49;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .checkout-btn:hover {
            background-color: #a64d4d;
        }

        .continue-shopping {
            display: inline-block;
            margin-top: 15px;
            color: #8C3F49;
            text-decoration: none;
            transition: color 0.3s;
        }

        .continue-shopping:hover {
            color: #a64d4d;
            text-decoration: underline;
        }

        .payment-options {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .payment-option {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            flex: 1;
            margin: 0 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .payment-option:hover {
            background-color: #e0e0e0;
        }

        .loyalty-points {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }
                /* Checkout form styles */
                .checkout-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        
        .payment-method {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .payment-option {
            flex: 1;
            min-width: 120px;
        }
        
        .payment-option input[type="radio"] {
            display: none;
        }
        
        .payment-option label {
            display: block;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-option input[type="radio"]:checked + label {
            background: #8C3F49;
            color: white;
        }
        
        .order-summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="cart-container">
        <?php if ($show_checkout): ?>
            <div class="cart-header">Checkout</div>
            
            <?php if (!empty($_SESSION['checkout_errors'])): ?>
                <div class="alert alert-danger">
                    <?php foreach ($_SESSION['checkout_errors'] as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['checkout_errors']); ?>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-7">
                    <div class="checkout-form">
                        <h4>Shipping Information</h4>
                        <form method="post">
                            
                            <div class="form-group">
                                <label for="username">Name</label>
                                <textarea id="name" name="name" class="form-control" rows="3" required><?= 
                                    htmlspecialchars($user_info['username'] ?? $_SESSION['checkout_data']['name'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Email</label>
                                <textarea id="email" name="email" class="form-control" rows="3" required><?= 
                                    htmlspecialchars($user_info['email'] ?? $_SESSION['checkout_data']['email'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <textarea id="phone" name="phone" class="form-control" rows="3" required><?= 
                                    htmlspecialchars($user_info['phone'] ?? $_SESSION['checkout_data']['phone'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="address">Shipping Address</label>
                                <textarea id="address" name="address" class="form-control" rows="3" required><?= 
                                    htmlspecialchars($user_info['address'] ?? $_SESSION['checkout_data']['address'] ?? '') ?></textarea>
                                <small class="text-muted">Need to update? <a href="edit_profile.php">Edit your profile</a></small>
                            </div>
                            
                            <h4 class="mt-4">Payment Method</h4>
                            <div class="payment-method">
                                <div class="payment-option">
                                    <input type="radio" id="credit-card" name="payment_method" value="credit_card" 
                                           <?= ($_SESSION['checkout_data']['payment_method'] ?? '') == 'credit_card' ? 'checked' : '' ?>>
                                    <label for="credit-card">Credit Card</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="paypal" name="payment_method" value="paypal" 
                                           <?= ($_SESSION['checkout_data']['payment_method'] ?? '') == 'paypal' ? 'checked' : '' ?>>
                                    <label for="paypal">PayPal</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="gpay" name="payment_method" value="gpay" 
                                           <?= ($_SESSION['checkout_data']['payment_method'] ?? '') == 'gpay' ? 'checked' : '' ?>>
                                    <label for="gpay">G Pay</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="cod" name="payment_method" value="cod" 
                                           <?= ($_SESSION['checkout_data']['payment_method'] ?? '') == 'cod' ? 'checked' : '' ?>>
                                    <label for="cod">Cash on Delivery</label>
                                </div>
                            </div>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" id="use_loyalty_points" name="use_loyalty_points" class="form-check-input"
                                               <?= isset($_SESSION['checkout_data']['loyalty_points_used']) && $_SESSION['checkout_data']['loyalty_points_used'] > 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="use_loyalty_points">
                                            Use Loyalty Points (Available: 250)
                                        </label>
                                    </div>
                                    <input type="number" id="loyalty_points" name="loyalty_points" class="form-control mt-2" 
                                           min="0" max="250" value="<?= $_SESSION['checkout_data']['loyalty_points_used'] ?? 0 ?>">
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" name="confirm_order" class="checkout-btn">Confirm Order</button>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-5">
                    <div class="order-summary">
                        <h4>Order Summary</h4>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item">
    <div class="product-info">
        <?php
        // Get image for perfume
        $perfume_image_query = "SELECT image FROM perfume WHERE perfume_id = ?";
        $stmt = $conn->prepare($perfume_image_query);
        $stmt->bind_param("i", $item['perfume_id']);
        $stmt->execute();
        $image_result = $stmt->get_result();
        $image = $image_result->fetch_assoc()['image'] ?? 'images/default_image.jpeg';
        ?>
        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['perfume_name']) ?>" class="product-image">
        <div class="product-details">
            <div class="product-title"><?= htmlspecialchars($item['perfume_name']) ?></div>
            <div>Qty: <?= $item['quantity'] ?></div>
        </div>
    </div>
    <div class="total-price">
        RM <?= number_format($item['price'] * $item['quantity'], 2) ?>
    </div>
</div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="subtotal-section">
                            <div>Subtotal: RM <?= number_format(array_reduce($_SESSION['cart'], function($carry, $item) {
                                return $carry + ($item['price'] * $item['quantity']);
                            }, 0), 2) ?></div>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div id="points-discount">
                                    Points Discount: -RM 0.00
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-2"><strong>Total: RM <?= number_format(array_reduce($_SESSION['cart'], function($carry, $item) {
                                return $carry + ($item['price'] * $item['quantity']);
                            }, 0), 2) ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <a href="cart.php" class="continue-shopping">← Back to Cart</a>
            
        <?php else: ?>
            <!-- [Keep your existing cart display code...] -->
            <div class="cart-header">Your cart</div>

            <?php if (!empty($_SESSION['message'])): ?>
                <script>
                    alert("<?= htmlspecialchars($_SESSION['message']); ?>");
                </script>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['cart'])): ?>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <?php
                    // Get image for perfume
                    $perfume_image_query = "SELECT image FROM perfume WHERE perfume_id = ?";
                    $stmt = $conn->prepare($perfume_image_query);
                    $stmt->bind_param("i", $item['perfume_id']);
                    $stmt->execute();
                    $image_result = $stmt->get_result();
                    $image = $image_result->fetch_assoc()['image'] ?? 'images/default_image.jpeg';
                    ?>
                    
                    <div class="cart-item">
                        <div class="product-info">
                            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['perfume_name']) ?>" class="product-image">
                            <div class="product-details">
                                <div class="product-title">
                                    <?= htmlspecialchars($item['perfume_name']); ?>
                                    <span class="bestseller-tag">BESTSELLER</span>
                                </div>
                                <div>Easy Like Sunday Morning</div>
                            </div>
                        </div>
                        
                        <div class="quantity-control">
                            <form method="post" style="display:flex; align-items:center;">
                                <input type="hidden" name="remove_id" value="<?= $item['perfume_id']; ?>">
                                <button type="submit" class="quantity-btn">-</button>
                                <span class="quantity-value"><?= $item['quantity']; ?></span>
                                <button type="button" class="quantity-btn plus-btn" data-id="<?= $item['perfume_id']; ?>">+</button>
                            </form>
                        </div>
                        
                        <div class="total-price">
                            RM <?= number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="subtotal-section">
                    <div>Subtotal RM <?= number_format(array_reduce($_SESSION['cart'], function($carry, $item) {
                        return $carry + ($item['price'] * $item['quantity']);
                    }, 0), 2); ?></div>
                    <small>Tax included and shipping calculated at checkout</small>
                </div>

                <a href="cart.php?checkout=1" class="checkout-btn">Proceed to Checkout</a>
                
                <div class="payment-options">
                    <div class="payment-option">G Pay</div>
                    <div class="payment-option">Loyalty Points</div>
                </div>
                
                <div class="loyalty-points">250</div>
                
                <a href="products.php" class="continue-shopping">Continue shopping</a>
            <?php else: ?>
                <p>Your cart is empty.</p>
                <a href="products.php" class="continue-shopping">Continue shopping</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php';?>

    <!-- Bootstrap JS and Dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Add functionality for all plus buttons
        document.querySelectorAll('.plus-btn').forEach(button => {
            button.addEventListener('click', function() {
                const perfumeId = this.getAttribute('data-id');
                const form = document.createElement('form');
                form.method = 'post';
                form.style.display = 'none';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'perfume_id';
                inputId.value = perfumeId;
                
                const inputQty = document.createElement('input');
                inputQty.type = 'hidden';
                inputQty.name = 'quantity';
                inputQty.value = 1;
                
                form.appendChild(inputId);
                form.appendChild(inputQty);
                document.body.appendChild(form);
                form.submit();
            });
        });
        
        // Loyalty points calculation
        $(document).ready(function() {
            $('#use_loyalty_points, #loyalty_points').change(function() {
                updateLoyaltyPoints();
            });
            
            function updateLoyaltyPoints() {
                if ($('#use_loyalty_points').is(':checked')) {
                    const points = parseInt($('#loyalty_points').val()) || 0;
                    const maxPoints = 250;
                    const pointsToUse = Math.min(points, maxPoints);
                    const discount = (pointsToUse * 0.10).toFixed(2);
                    const subtotal = <?= array_reduce($_SESSION['cart'] ?? [], function($carry, $item) {
                        return $carry + ($item['price'] * $item['quantity']);
                    }, 0) ?>;
                    const total = Math.max(0, subtotal - discount).toFixed(2);
                    
                    $('#points-discount').html(`Points Discount: -RM ${discount}`);
                    $('.order-summary .subtotal-section strong').html(`Total: RM ${total}`);
                } else {
                    $('#points-discount').html('Points Discount: -RM 0.00');
                    $('.order-summary .subtotal-section strong').html(`Total: RM <?= 
                        number_format(array_reduce($_SESSION['cart'] ?? [], function($carry, $item) {
                            return $carry + ($item['price'] * $item['quantity']);
                        }, 0), 2) ?>`);
                }
            }
        });
    </script>
</body>
</html>
