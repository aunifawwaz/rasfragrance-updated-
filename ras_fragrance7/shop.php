<?php
session_start();
include 'db_connection.php';

// Handle add to cart request
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
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if perfume already in cart
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

        // Sync with database if user is logged in
        if (isset($_SESSION['user_id'])) {
            // Clear existing cart items for this user
            $conn->query("DELETE FROM cart WHERE user_id = " . $_SESSION['user_id']);
            
            // Insert current cart items
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $conn->prepare("INSERT INTO cart (user_id, perfume_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $_SESSION['user_id'], $item['perfume_id'], $item['quantity']);
                $stmt->execute();
            }
        }

        // Update cart count
        $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
        $_SESSION['cart_count'] = $cart_count;
        
        // Return the new cart count for AJAX response
        echo $cart_count;
        exit();
    }
}

// Fetch categories from the database
$query = "SELECT id,name FROM categories";
$result = $conn->query($query);

// Check if categories are available
$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = ['id' => $row['id'], 'name' => $row['name']];
    }
} else {
    $categories[] = "No categories available";
}

// Fetch perfumes sorted by popularity
$popular_query = "SELECT * FROM perfume ORDER BY popularity_score DESC LIMIT 8";
$popular_result = $conn->query($popular_query);
$popular_perfumes = $popular_result->fetch_all(MYSQLI_ASSOC);

// Fetch expert's choice perfumes
$experts_choice_query = "SELECT * FROM perfume WHERE expert_choice = 1 LIMIT 8";
$experts_choice_result = $conn->query($experts_choice_query);
$experts_choice_perfumes = $experts_choice_result->fetch_all(MYSQLI_ASSOC);

// Fetch beginner's choice perfumes
$beginners_choice_query = "SELECT * FROM perfume WHERE beginners_choice = 1 LIMIT 8";
$beginners_choice_result = $conn->query($beginners_choice_query);
$beginners_choice_perfumes = $beginners_choice_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Ras Fragrance</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Categories Section Styling */
        .categories-section {
            text-align: center;
            padding: 2rem;
            background-color: #f4f4f4;
            border-radius: 10px;
            margin: 2rem auto;
            max-width: 80%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .categories-section h2 {
            font-size: 2rem;
            color: #8C3F49;
            margin-bottom: 1.5rem;
        }

        .categories-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }

        .category-card {
            background-color: #ffffff;
            color: #8C3F49;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            padding: 1rem;
            border: 1px solid #8C3F49;
            border-radius: 10px;
            width: 150px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .category-card:hover {
            transform: scale(1.05);
        }

        /* Page Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .perfume-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            padding: 2rem;
        }

        .perfume-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 200px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            position: relative;
        }

        .perfume-card:hover {
            transform: scale(1.05);
        }

        .perfume-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }

        .perfume-card h3 {
            font-size: 18px;
            margin: 0.5rem 0;
            color: #333;
        }

        .perfume-card p {
            font-size: 14px;
            color: #555;
            margin: 0.5rem;
        }

        .perfume-card a {
            display: block;
            text-decoration: none;
            color: white;
            background-color: #8C3F49;
            padding: 0.5rem;
            margin: 1rem;
            border-radius: 4px;
        }

        .perfume-card a:hover {
            background-color: #a64d4d;
        }

        /* Add to Cart Button */
        .add-to-cart {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 5px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            cursor: pointer;
            z-index: 10;
            border: none;
        }

        .add-to-cart img {
            width: 20px;
            height: 20px;
        }

        .add-to-cart:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        h2 {
            color: black;
            text-align: center;
            margin-top: 20px;
        }

        /* Success message */
        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            display: none;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <!-- Success message -->
    <div id="successMessage" class="success-message">
        Item added to cart successfully!
    </div>

    <main>
        <!-- Popular Perfumes Section -->
        <section class="favorites-section">
            <h2>Popular Perfumes</h2>
            <div class="perfume-container">
                <?php foreach ($popular_perfumes as $perfume): ?>
                    <div class="perfume-card">
                        <img src="<?= htmlspecialchars($perfume['image'] ?: 'images/default_image.jpeg') ?>" alt="Perfume Image">
                        <h3><?= htmlspecialchars($perfume['perfume_name']); ?></h3>
                        <p><strong>Price:</strong> RM <?= number_format($perfume['price'], 2); ?></p>
                        <a href="perfume_details.php?perfume_id=<?= $perfume['perfume_id']; ?>">View Details</a>

                        <!-- Add to Cart Button -->
                        <form id="cart-form-<?= $perfume['perfume_id']; ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="perfume_id" value="<?= $perfume['perfume_id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="button" class="add-to-cart" onclick="addToCart(<?= $perfume['perfume_id']; ?>)">
                                <img src="images/cart.png" alt="Cart Icon">
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Expert's Choice Section -->
        <section class="favorites-section">
            <h2>Expert's Choice</h2>
            <div class="perfume-container">
                <?php foreach ($experts_choice_perfumes as $perfume): ?>
                    <div class="perfume-card">
                        <img src="<?= htmlspecialchars($perfume['image'] ?: 'images/default_image.jpeg') ?>" alt="Perfume Image">
                        <h3><?= htmlspecialchars($perfume['perfume_name']); ?></h3>
                        <p><strong>Price:</strong> RM <?= number_format($perfume['price'], 2); ?></p>
                        <a href="perfume_details.php?perfume_id=<?= $perfume['perfume_id']; ?>">View Details</a>

                        <!-- Add to Cart Button -->
                        <form id="cart-form-<?= $perfume['perfume_id']; ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="perfume_id" value="<?= $perfume['perfume_id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="button" class="add-to-cart" onclick="addToCart(<?= $perfume['perfume_id']; ?>)">
                                <img src="images/cart.png" alt="Cart Icon">
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Beginner's Choice Section -->
        <section class="favorites-section">
            <h2>Beginner's Choice</h2>
            <div class="perfume-container">
                <?php foreach ($beginners_choice_perfumes as $perfume): ?>
                    <div class="perfume-card">
                        <img src="<?= htmlspecialchars($perfume['image'] ?: 'images/default_image.jpeg') ?>" alt="Perfume Image">
                        <h3><?= htmlspecialchars($perfume['perfume_name']); ?></h3>
                        <p><strong>Price:</strong> RM <?= number_format($perfume['price'], 2); ?></p>
                        <a href="perfume_details.php?perfume_id=<?= $perfume['perfume_id']; ?>">View Details</a>

                        <!-- Add to Cart Button -->
                        <form id="cart-form-<?= $perfume['perfume_id']; ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="perfume_id" value="<?= $perfume['perfume_id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="button" class="add-to-cart" onclick="addToCart(<?= $perfume['perfume_id']; ?>)">
                                <img src="images/cart.png" alt="Cart Icon">
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <script>
        function addToCart(perfumeId) {
            var form = document.getElementById('cart-form-' + perfumeId);
            var formData = new FormData(form);

            fetch('shop.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Update the cart count in header
                var cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data;
                } else {
                    // If cart count doesn't exist, create it
                    var cartIcon = document.querySelector('.fa-shopping-bag').parentNode;
                    var newCount = document.createElement('span');
                    newCount.className = 'cart-count';
                    newCount.textContent = data;
                    cartIcon.appendChild(newCount);
                }

                // Show success message
                var successMessage = document.getElementById('successMessage');
                successMessage.style.display = 'block';
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 3000);
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

    <?php include('footer.php'); ?>
</body>
</html>