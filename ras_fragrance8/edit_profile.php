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
    <title>Update Profile - Ras Fragrance</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Maroon Theme Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4e7e7; /* Light background */
            color: #5a2a2a; /* Dark text */
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #8C3F49; /* Maroon header */
            padding: 10px 20px;
            color: white;
            border-bottom: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo img {
            width: 100px;
            height: auto;
            cursor: pointer;
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
            color: white;
            font-weight: 500;
        }

        .profile-section {
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
            background: #f9f9f9;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-section h2 {
            text-align: center;
            color: #8C3F49; /* Maroon color */
        }

        .profile-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .profile-item {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
        }

        .profile-item span {
            font-weight: bold;
        }

        .cta-buttons {
            text-align: center;
            margin-top: 20px;
        }

        .cta-buttons a,
        .cta-buttons button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #8C3F49; /* Maroon color */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 5px;
            transition: background-color 0.3s ease;
        }

        .cta-buttons a:hover,
        .cta-buttons button:hover {
            background-color: #a64d4d; /* Lighter Maroon on hover */
        }

        footer {
            text-align: center;
            padding: 15px;
            background-color: #8C3F49; /* Maroon footer */
            color: white;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <!-- Profile Section -->
    <main class="container mt-5">
        <section class="profile-section p-4">
            <h2>Update Profile</h2>
            <form action="process_editprofile.php" method="POST">
                <div class="mb-3 row">
                    <label for="username" class="col-sm-3 col-form-label">Username:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="email" class="col-sm-3 col-form-label">Email:</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="phone_no" class="col-sm-3 col-form-label">Phone Number:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="phone_no" name="phone_no" value="<?= htmlspecialchars($user['phone_no']) ?>" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="address" class="col-sm-3 col-form-label">Address:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>
                    </div>
                </div>

                <div class="cta-buttons">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="profile.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </main>

    <!-- Footer Section -->
    <footer>
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS and Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
