<?php
// Start session
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM User WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Check if user is admin (role 1)
if ($user['role'] == 1) {
    header("Location: profile.php");
    exit;
}

// Fetch favorite perfumes
$favorites_query = "SELECT p.* FROM Perfumes p 
                   JOIN favorites f ON p.id = f.perfume_id 
                   WHERE f.user_id = ?";
$favorites_stmt = $conn->prepare($favorites_query);
$favorites_stmt->bind_param("i", $user_id);
$favorites_stmt->execute();
$favorites_result = $favorites_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorite Perfumes - Ras Fragrance</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4e7e7;
        }
        header {
            background-color: #8C3F49;
            padding: 20px;
            color: white;
        }
        footer {
            text-align: center;
            padding: 1rem;
            background-color: #8C3F49;
            color: white;
        }
        .favorites-section {
            margin: 40px auto;
            max-width: 800px;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .perfume-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .perfume-info {
            flex-grow: 1;
        }
        .perfume-name {
            font-weight: bold;
            font-size: 18px;
        }
        .perfume-brand {
            color: #666;
        }
        .no-favorites {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container">
    <section class="favorites-section">
        <h2>Your Favorite Perfumes</h2>
        
        <?php if ($favorites_result->num_rows > 0): ?>
            <?php while ($perfume = $favorites_result->fetch_assoc()): ?>
                <div class="perfume-card">
                    <div class="perfume-info">
                        <div class="perfume-name"><?= htmlspecialchars($perfume['name']); ?></div>
                        <div class="perfume-brand"><?= htmlspecialchars($perfume['brand']); ?></div>
                    </div>
                    <button class="btn btn-danger" onclick="removeFavorite(<?= $perfume['id']; ?>)">Remove</button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-favorites">
                <p>You haven't added any perfumes to your favorites yet.</p>
                <a href="products.php" class="btn btn-primary">Browse Perfumes</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS and Dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    function removeFavorite(perfume_id) {
        // Send AJAX request to remove perfume from favorites
        $.post('manage_favorites.php', { action: 'remove', perfume_id: perfume_id }, function(response) {
            location.reload();  // Reload the page to reflect the changes
        });
    }
</script>

</body>
</html>