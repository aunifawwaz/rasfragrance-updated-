<?php
include 'db_connection.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if accessed directly
if (!isset($_SESSION['primary_scent']) || !isset($_SESSION['intensity'])) {
    header("Location: quiz.php");
    exit();
}

// Get session data
$primary_scent = $_SESSION['primary_scent'];
$intensity = $_SESSION['intensity'];


//$primary_scent = filter_input(INPUT_POST, 'primary-scent', FILTER_SANITIZE_STRING);
//$intensity = filter_input(INPUT_POST, 'intensity', FILTER_SANITIZE_STRING);
// Define recommendations based on the flowchart
$recommendations = [
    'floral' => [
        'light' => ['perfume_id' => 12, 'name' => 'Rose Garden', 'image' => 'images/amethyst.jpeg'],
        'intense' => ['perfume_id' => 15, 'name' => 'Jasmine', 'image' => 'images/amber.jpeg'],
    ],
    'woody' => [
        'fresh' => ['perfume_id' => 16, 'name' => 'Cedar Breeze', 'image' => 'images/coral.jpeg'],
        'smoky' => ['perfume_id' => 17, 'name' => 'Sandalwood Smoke', 'image' => 'images/emerald.jpeg'],
    ],
    'citrus' => [
        'sweet' => ['perfume_id' => 18, 'name' => 'Orange Blossom', 'image' => 'images/lolite.jpeg'],
        'tangy' => ['perfume_id' => 13, 'name' => 'Lemon Zest', 'image' => 'images/onyx.jpeg'],
    ],
];

// Determine the recommendation
$recommended_product = $recommendations[$primary_scent][$intensity] ?? null;
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to the external stylesheet -->
    <style>
        /* Inline styles for the maroon theme */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4e7e7;
            color: #5a2a2a;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #8b3d3d;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
        }

        header h1 {
            margin: 0;
        }

        main {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
        }

        .result-container {
            max-width: 800px;
            width: 100%;
            padding: 1.5rem;
            background-color: #fff;
            border: 2px solid #8b3d3d;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .result-container h2 {
            color: #8b3d3d;
            margin-bottom: 1rem;
        }

        .result-container p {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #5a2a2a;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            margin: 2rem 0;
        }

        .product {
            text-align: center;
            width: 200px; /* Ensure consistent size */
        }

        .product img {
            width: 100%; /* Scale to the container width */
            height: auto;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: transform 0.3s ease-in-out;
        }

        .product img:hover {
            transform: scale(1.05); /* Slight zoom effect on hover */
        }

        .product p {
            margin: 0.5rem 0 0;
            font-weight: bold;
            color: #8b3d3d;
        }

        .cta-button {
            display: inline-block;
            background-color: #8b3d3d;
            color: #fff;
            padding: 0.7rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-top: 1rem;
        }

        .cta-button:hover {
            background-color: #a64d4d;
        }

        .button-group {
        display: flex;
        justify-content: center; /* Center the buttons horizontally */
        gap: 1rem; /* Add spacing between the buttons */
        margin-top: 2rem; /* Add some space above the button group */
    }

    .cta-button {
        display: inline-block;
        background-color: #8b3d3d;
        color: #fff;
        padding: 0.7rem 1.5rem;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s ease;
        text-align: center;
    }

    .cta-button:hover {
        background-color: #a64d4d;
    }
    </style>
</head>
<body>
    <header>
        <h1>Your Quiz Results</h1>
    </header>
    <main>
    <div class="result-container">
        <h2>Congratulations!</h2>
        <p><strong>Primary Scent:</strong> <?= htmlspecialchars($primary_scent) ?></p>
        <p><strong>Intensity:</strong> <?= htmlspecialchars($intensity)?></p>


        <?php if ($recommended_product): ?>
            <p><strong>Recommended Perfume:</strong> <?= htmlspecialchars($recommended_product['name']) ?></p>
             <!-- Product Grid -->
             <div class="product-grid">
                <div class="product">
                <a href="perfume_details.php?perfume_id=<?= htmlspecialchars($recommended_product['perfume_id']) ?>">
                        <img src="<?= htmlspecialchars($recommended_product['image']) ?>" alt="<?= htmlspecialchars($recommended_product['name']) ?>">
                        <p><?= htmlspecialchars($recommended_product['name']) ?></p>
                    </a>
                </div>
            </div>
        <?php endif; ?>

            <!-- Button Group -->
            <div class="button-group">
                <a href="shop.php" class="cta-button">Shop</a>
                <a href="quiz.php" class="cta-button">Retake Quiz</a>
                <a href="home.php" class="cta-button">Home</a>
            </div>
        </div>
    </main>
    <footer>
        <p style="text-align: center; background-color: #8b3d3d; color: #fff; padding: 1rem 0; margin: 0;">
            &copy; 2025 Ras Fragrance. All rights reserved.
        </p>
    </footer>
</body>
</html>
