<?php
session_start();
include 'db_connection.php';

// Pagination settings (MOVE THIS TO THE TOP)
$per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Fetch all perfumes with their LOWEST price (excluding removed and discontinued)
$all_perfumes_query = "SELECT 
                        p.*,
                        COALESCE(
                            (SELECT MIN(ps.price) FROM perfume_sizes ps WHERE ps.perfume_id = p.perfume_id),
                            p.price
                        ) AS display_price,
                        (SELECT COUNT(*) FROM perfume WHERE deleted = 0 AND is_discontinued = 0) AS total_count
                     FROM perfume p 
                     WHERE p.deleted = 0 
                     AND p.is_discontinued = 0
                     ORDER BY p.perfume_name ASC
                     LIMIT $per_page OFFSET $offset";
                     
$all_perfumes_result = $conn->query($all_perfumes_query);
$all_perfumes = $all_perfumes_result->fetch_all(MYSQLI_ASSOC);

// Get total count
$total_count = 0;
if (!empty($all_perfumes) && isset($all_perfumes[0]['total_count'])) {
    $total_count = $all_perfumes[0]['total_count'];
} else {
    $count_result = $conn->query("SELECT COUNT(*) AS total_count FROM perfume WHERE deleted = 0 AND is_discontinued = 0");
    $total_count = $count_result->fetch_assoc()['total_count'];
}
$total_pages = ceil($total_count / $per_page);

// Handle add to favorites request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_favorites'], $_POST['perfume_id'])) {
    if (isset($_SESSION['user_id'])) {
        $perfume_id = intval($_POST['perfume_id']);
        $user_id = $_SESSION['user_id'];
        
        // Check if already in favorites
        $check_stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND perfume_id = ?");
        $check_stmt->bind_param("ii", $user_id, $perfume_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows == 0) {
            // Add to favorites
            $stmt = $conn->prepare("INSERT INTO favorites (user_id, perfume_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $perfume_id);
            if ($stmt->execute()) {
                echo "added";
            } else {
                echo "error";
            }
        } else {
            // Remove from favorites
            $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND perfume_id = ?");
            $stmt->bind_param("ii", $user_id, $perfume_id);
            if ($stmt->execute()) {
                echo "removed";
            } else {
                echo "error";
            }
        }
    } else {
        echo "login_required";
    }
    exit();
}

// Fetch user's favorites if logged in
$user_favorites = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $favorites_query = "SELECT perfume_id FROM favorites WHERE user_id = ?";
    $favorites_stmt = $conn->prepare($favorites_query);
    $favorites_stmt->bind_param("i", $user_id);
    $favorites_stmt->execute();
    $favorites_result = $favorites_stmt->get_result();
    
    while ($row = $favorites_result->fetch_assoc()) {
        $user_favorites[] = $row['perfume_id'];
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

$popular_query = "SELECT 
                    p.*,
                    COALESCE(
                        (SELECT MIN(ps.price) FROM perfume_sizes ps WHERE ps.perfume_id = p.perfume_id),
                        p.price
                    ) AS display_price
                  FROM perfume p 
                  ORDER BY p.perfume_name ASC 
                  LIMIT 8";

$popular_result = $conn->query($popular_query);
$popular_perfumes = $popular_result->fetch_all(MYSQLI_ASSOC);

// Fetch expert's choice perfumes with their LOWEST price
$experts_choice_query = "SELECT 
                            p.*,
                            COALESCE(
                                (SELECT MIN(ps.price) FROM perfume_sizes ps WHERE ps.perfume_id = p.perfume_id),
                                p.price
                            ) AS display_price
                         FROM perfume p 
                         WHERE expert_choice = 1 
                         LIMIT 8";
$experts_choice_result = $conn->query($experts_choice_query);
$experts_choice_perfumes = $experts_choice_result->fetch_all(MYSQLI_ASSOC);

// Fetch beginner's choice perfumes with their LOWEST price
$beginners_choice_query = "SELECT 
                              p.*,
                              COALESCE(
                                  (SELECT MIN(ps.price) FROM perfume_sizes ps WHERE ps.perfume_id = p.perfume_id),
                                  p.price
                              ) AS display_price
                           FROM perfume p 
                           WHERE beginners_choice = 1 
                           LIMIT 8";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
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
            cursor: pointer;
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

        .perfume-card .price-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 1rem;
        }

        .perfume-card .price {
            font-size: 14px;
            color: #555;
        }

        /* Favorite Button */
        .favorite-btn {
            background: none;
            border: none;
            color: #ccc;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
            font-size: 1.2rem;
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }

        .favorite-btn:hover {
            color: #ff6b6b;
        }

        .favorite-btn.active {
            color: #ff6b6b;
        }

        h2 {
            color: maroon;
            text-align: center;
            margin-top: 20px;
        }

        .favorites-section h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .favorites-section .perfume-container .perfume-card h3 {
            color: #2c3e50;
        }

        .favorites-section p {
            color: #888;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <main>
        <!-- All Perfumes Section -->
<section class="favorites-section">
    <h2>All Perfumes</h2>
    <div class="perfume-container">
        <?php foreach ($all_perfumes as $perfume): ?>
            <div class="perfume-card" onclick="window.location.href='perfume_details.php?perfume_id=<?= $perfume['perfume_id'] ?>'">
                <button class="favorite-btn <?= in_array($perfume['perfume_id'], $user_favorites) ? 'active' : '' ?>" 
                        onclick="event.stopPropagation(); toggleFavorite(<?= $perfume['perfume_id'] ?>, this)">
                    <i class="bi <?= in_array($perfume['perfume_id'], $user_favorites) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                </button>
                <img src="<?= htmlspecialchars($perfume['image'] ?: 'images/default_image.jpeg') ?>" alt="Perfume Image">
                <h3><?= htmlspecialchars($perfume['perfume_name']); ?></h3>
                <div class="price-container">
                    <span class="price"><strong>Price:</strong> RM <?= number_format($perfume['display_price'], 2); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
        <!-- Popular Perfumes Section -->
        <section class="favorites-section">
            <h2>Popular Perfumes</h2>
            <div class="perfume-container">
                <?php foreach ($popular_perfumes as $perfume): ?>
                    <div class="perfume-card" onclick="window.location.href='perfume_details.php?perfume_id=<?= $perfume['perfume_id'] ?>'">
                        <button class="favorite-btn <?= in_array($perfume['perfume_id'], $user_favorites) ? 'active' : '' ?>" 
                                onclick="event.stopPropagation(); toggleFavorite(<?= $perfume['perfume_id'] ?>, this)">
                            <i class="bi <?= in_array($perfume['perfume_id'], $user_favorites) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        </button>
                        <img src="<?= htmlspecialchars($perfume['image'] ?: 'images/default_image.jpeg') ?>" alt="Perfume Image">
                        <h3><?= htmlspecialchars($perfume['perfume_name']); ?></h3>
                        <div class="price-container">
                            <span class="price"><strong>Price:</strong> RM <?= number_format($perfume['display_price'], 2); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Expert's Choice Section -->
        <section class="favorites-section">
            <h2>Expert's Choice</h2>
            <div class="perfume-container">
                <?php foreach ($experts_choice_perfumes as $perfume): ?>
                    <div class="perfume-card" onclick="window.location.href='perfume_details.php?perfume_id=<?= $perfume['perfume_id'] ?>'">
                        <button class="favorite-btn <?= in_array($perfume['perfume_id'], $user_favorites) ? 'active' : '' ?>" 
                                onclick="event.stopPropagation(); toggleFavorite(<?= $perfume['perfume_id'] ?>, this)">
                            <i class="bi <?= in_array($perfume['perfume_id'], $user_favorites) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        </button>
                        <img src="<?= htmlspecialchars($perfume['image'] ?: 'images/default_image.jpeg') ?>" alt="Perfume Image">
                        <h3><?= htmlspecialchars($perfume['perfume_name']); ?></h3>
                        <div class="price-container">
                            <span class="price"><strong>Price:</strong> RM <?= number_format($perfume['display_price'], 2); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Beginner's Choice Section -->
        <section class="favorites-section">
            <h2>Beginner's Choice</h2>
            <div class="perfume-container">
                <?php foreach ($beginners_choice_perfumes as $perfume): ?>
                    <div class="perfume-card" onclick="window.location.href='perfume_details.php?perfume_id=<?= $perfume['perfume_id'] ?>'">
                        <button class="favorite-btn <?= in_array($perfume['perfume_id'], $user_favorites) ? 'active' : '' ?>" 
                                onclick="event.stopPropagation(); toggleFavorite(<?= $perfume['perfume_id'] ?>, this)">
                            <i class="bi <?= in_array($perfume['perfume_id'], $user_favorites) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        </button>
                        <img src="<?= htmlspecialchars($perfume['image'] ?: 'images/default_image.jpeg') ?>" alt="Perfume Image">
                        <h3><?= htmlspecialchars($perfume['perfume_name']); ?></h3>
                        <div class="price-container">
                            <span class="price"><strong>Price:</strong> RM <?= number_format($perfume['display_price'], 2); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Pagination -->
<div class="pagination-container" style="text-align: center; margin: 20px 0;">
    <?php if ($total_pages > 1): ?>
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn btn-outline-primary">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $i ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-primary">Next</a>
        <?php endif; ?>
    <?php endif; ?>
</div>
    </main>

    <script>
        function toggleFavorite(perfumeId, button) {
            fetch('shop.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'add_to_favorites=1&perfume_id=' + perfumeId
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'login_required') {
                    alert('Please login to add items to favorites');
                    window.location.href = 'login.php';
                } else if (data === 'added') {
                    button.classList.add('active');
                    button.innerHTML = '<i class="bi bi-heart-fill"></i>';
                } else if (data === 'removed') {
                    button.classList.remove('active');
                    button.innerHTML = '<i class="bi bi-heart"></i>';
                } else if (data === 'error') {
                    alert('Error occurred. Please try again.');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

    <?php include('footer.php'); ?>
</body>
</html>