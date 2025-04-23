<?php
// Include database connection
include 'db_connection.php';

// Get the perfume ID and category ID from the URL
$perfume_id = isset($_GET['perfume_id']) ? intval($_GET['perfume_id']) : null;
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;  // Capture category_id from the URL

// Redirect to shop page if no perfume ID is provided
if (!$perfume_id) {
    header("Location: shop.php");
    exit();
}

// Fetch perfume details
$perfume_query = "SELECT * FROM perfume WHERE perfume_id = ?";
$stmt = $conn->prepare($perfume_query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $perfume_id);
$stmt->execute();
$perfume_result = $stmt->get_result();
$perfume = $perfume_result->fetch_assoc();

// Redirect if the perfume doesn't exist
if (!$perfume) {
    header("Location: shop.php");
    exit();
}

// Initialize cart count for header
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($perfume['perfume_name']); ?> Details</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4e7e7;
            color: #5a2a2a;
            margin: 0;
            padding: 0;
        }

        .perfume-header {
            background-color: #8C3F49;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        .perfume-details img {
            width: 100%;
            max-width: 400px;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .product-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .price-tag {
            font-size: 24px;
            color: #8C3F49;
            font-weight: bold;
            margin: 15px 0;
        }

        .quantity-control {
            width: 120px;
            margin-bottom: 20px;
        }

        .add-to-cart-btn {
            background-color: #8C3F49;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        .add-to-cart-btn:hover {
            background-color: #a64d4d;
        }

        .back-button {
            display: inline-block;
            color: #8C3F49;
            text-decoration: none;
            margin-top: 20px;
            font-weight: bold;
        }

        .back-button:hover {
            text-decoration: underline;
        }

        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }

        .bestseller-badge {
            background-color: #FFD700;
            color: #000;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="perfume-header">

    </div>

    <main class="container my-5">
        <div class="row">
            <!-- Perfume Image -->
            <div class="col-lg-6 mb-4">
                <div class="perfume-details text-center">
                    <img src="<?= htmlspecialchars($perfume['image'] ?: 'images/default_image.jpeg') ?>" 
                         alt="<?= htmlspecialchars($perfume['perfume_name']); ?>" 
                         class="img-fluid">
                </div>
            </div>

            <!-- Perfume Info -->
            <div class="col-lg-6">
                <div class="product-card p-4">
                    <h2><?= htmlspecialchars($perfume['perfume_name']); ?>
                        <?php if (rand(0, 1)): ?> <!-- Randomly show bestseller badge for demo -->
                            <span class="bestseller-badge">BESTSELLER</span>
                        <?php endif; ?>
                    </h2>
                    
                    <div class="price-tag">RM <?= number_format($perfume['price'], 2); ?></div>
                    
                    <p><?= htmlspecialchars($perfume['description']); ?></p>
                    
                    <hr>
                    
                    <!-- Add to Cart Form -->
                    <form id="addToCartForm" method="post">
                        <input type="hidden" name="perfume_id" value="<?= $perfume['perfume_id']; ?>">
                        
                        <div class="form-group">
                            <label for="quantity"><strong>Quantity:</strong></label>
                            <input type="number" id="quantity" name="quantity" 
                                   value="1" min="1" max="10" 
                                   class="form-control quantity-control">
                        </div>
                        
                        <button type="submit" class="add-to-cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                    
                    <a href="products.php?category_id=<?= $category_id; ?>" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Success Message Alert -->
    <div id="alertMessage" class="alert alert-success alert-message" role="alert">
        Item added to cart successfully!
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS and Dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function() {
        // AJAX form submission
        $('#addToCartForm').submit(function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            
            $.ajax({
                type: 'POST',
                url: 'add_to_cart.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $('#alertMessage').fadeIn().delay(2000).fadeOut();
                        
                        // Update cart count in header
                        if (response.count > 0) {
                            $('.cart-count').text(response.count);
                            if ($('.cart-count').length === 0) {
                                $('.fa-shopping-bag').after('<span class="cart-count">' + response.count + '</span>');
                            }
                        }
                    }
                },
                error: function() {
                    alert('Error adding item to cart. Please try again.');
                }
            });
        });
    });
    </script>
</body>
</html>