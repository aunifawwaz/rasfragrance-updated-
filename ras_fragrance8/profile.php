<?php
// Start session
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user profile details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM User WHERE user_id = ?";
$stmt = $conn->prepare($user_query);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();

    if (!$user) {
        die("User not found.");
    }
} else {
    die("Query preparation failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Ras Fragrance</title>
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

        .profile-section {
            margin: 40px auto;
            max-width: 800px;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-details {
            margin-top: 20px;
        }

        .profile-item {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .profile-item span {
            font-weight: bold;
        }

        .cta-buttons a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #8C3F49;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
            transition: background-color 0.3s;
        }

        .cta-buttons a:hover {
            background-color: #a64d4d;
        }

        .add-address-btn {
            padding: 10px 15px;
            background-color: #8C3F49;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .add-address-btn:hover {
            background-color: #a64d4d;
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .profile-header h2 {
            margin: 0;
        }

        .profile-header .edit-button {
            color: #8C3F49;
            cursor: pointer;
        }

        .addresses-section {
            margin-top: 20px;
        }

        .addresses-section p {
            font-size: 16px;
            color: #555;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container">
    <section class="profile-section">
        <div class="profile-header">
            <h2>Profile</h2>
            <span class="edit-button">&#9998; Edit</span> <!-- Edit icon for profile -->
        </div>

        <div class="profile-details">
            <div class="profile-item">
                <span>Name:</span> <?= htmlspecialchars($user['username']); ?>
            </div>
            <div class="profile-item">
                <span>Email:</span> <?= htmlspecialchars($user['email']); ?>
            </div>
        </div>

        <div class="addresses-section">
            <h3>Addresses</h3>
            <p>No addresses added</p>
            <a href="add_address.php" class="add-address-btn">+ Add Address</a>
        </div>

        <div class="cta-buttons text-center">
            <a href="edit_profile.php">Edit Profile</a>
            <a href="order_history.php">View Order History</a>
            <?php if ($user['role'] == 0): // Only show for regular users ?>
                <a href="favorites.php">View Favorite Perfumes</a>
            <?php endif; ?>
        </div>

        <!-- Logout Button -->
        <div class="text-center mt-4">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS and Dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>