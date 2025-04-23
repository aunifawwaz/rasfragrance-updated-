<?php
// Start the session
session_start();

// Include database connection
include 'db_connection.php';

// Get the category ID from the URL
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

// Redirect to the shop page if no category is provided
if (!$category_id) {
    header("Location: shop.php");
    exit();
}

// Fetch category name
$category_query = "SELECT name FROM categories WHERE id = ?";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category_result = $stmt->get_result();
$category = $category_result->fetch_assoc();

// If the category doesn't exist, redirect to the shop page
if (!$category) {
    header("Location: shop.php");
    exit();
}

// Fetch perfumes under this category
$perfumes_query = "SELECT * FROM perfume WHERE category_id = ?";
$stmt = $conn->prepare($perfumes_query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$perfumes_result = $stmt->get_result();
$perfumes = $perfumes_result->fetch_all(MYSQLI_ASSOC);

// Handle Add to Cart Action
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

        // Update cart count in session
        $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));

        // Return updated cart count for AJAX
        echo $_SESSION['cart_count'];
        exit();
    } else {
        echo "Error: Perfume not found.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['name']); ?> Perfumes</title>
    <link rel="stylesheet" href="styles.css">
    <style> 
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
            position: relative; /* Position context for the cart icon */
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
            z-index: 10; /* Ensure the icon is above the image */
        }

        .add-to-cart img {
            width: 20px;
            height: 20px;
        }

        .add-to-cart:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        
        .add-to-cart img {
            width: 20px;
            height: 20px;
        }

        .add-to-cart:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

       

        h1, h2 {
            margin: 5px 0;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        nav {
            margin: 10px 0;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 15px;
            padding: 0;
            margin: 0;
        }

        nav ul li {
            display: inline;
        }

        nav ul li a {
            text-decoration: none;
            color: black;
            font-weight: 500;
        }

       
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <main>
        <h2>Explore Our <?= htmlspecialchars($category['name']); ?> Perfumes</h2>

        <section class="perfume-container">
            <?php if (!empty($perfumes)): ?>
                <?php foreach ($perfumes as $perfume): ?>
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
                                <img src="images/cart.png">
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No perfumes available in this category.</p>
            <?php endif; ?>
        </section>

        <a href="shop.php" class="cta-button">Back to Shop</a>
    </main>

    <script>
        function addToCart(perfumeId) {
            var form = document.getElementById('cart-form-' + perfumeId);
            var formData = new FormData(form);

            fetch('products.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('cart-count').textContent = data;
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

    <?php include('footer.php'); ?>
</body>
</html>
