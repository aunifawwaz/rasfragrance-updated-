<?php
// Start session
session_start();
include 'db_connection.php';

// Fetch all-time favorite perfumes from the database
$perfumes = [];
$query = "SELECT 
             p.*,
             (
                 SELECT COALESCE(MAX(pv.price), p.price)
                 FROM perfume_variants pv
                 WHERE pv.perfume_id = p.perfume_id
             ) AS display_price
          FROM perfume p
          WHERE p.is_all_time_favorite = 1
          AND p.deleted = 0
          LIMIT 4";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // If variants are stored as JSON in a column
        if (!empty($row['sizes'])) {
            $sizes = json_decode($row['sizes'], true);
            $maxPrice = max(array_column($sizes, 'price'));
            $row['display_price'] = $maxPrice;
        } else {
            $row['display_price'] = $row['price'];
        }
        $perfumes[] = $row;
    }
}
if (!$result) {
    die("Query failed: " . $conn->error);
}

if (isset($_SESSION['role']) && $_SESSION['role'] == 1) {
    header('Location: admin_dashboard.php');
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Ras Fragrance</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #8C3F49;
            padding: 10px 20px;
            border-bottom: 1px solid #ccc;
        }

        header .top-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .logo img {
            width: 100px;
            height: auto;
            cursor: pointer;
        }

        h1, h2 {
            margin: 5px 0;
            text-align: center;
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

        .icons {
            display: flex;
            gap: 15px;
        }

        .icons img {
            width: 24px;
            height: 24px;
            cursor: pointer;
        }

        main {
            padding: 20px;
        }

        /* Slideshow Container */
        .slideshow {
            position: relative;
            width: 100%;
            max-width: 1200px;
            height: 400px;
            margin: 20px auto;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Slides */
        .slides {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.5s ease-in-out;
        }

        .slide {
            min-width: 100%;
            height: 100%;
            position: relative;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Navigation Dots */
        .dots {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .dot {
            width: 12px;
            height: 12px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .dot.active {
            background-color: white;
        }

        .perfume-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px 0;
        }

        .perfume-card {
            background-color: #fff;
            border: 1px solid #8C3F49;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 250px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .perfume-card:hover {
            transform: translateY(-5px);
        }

        .perfume-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .perfume-card h3 {
            font-size: 18px;
            color: #8C3F49;
            margin: 10px 0;
        }

        .perfume-card .price {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
            color: #8C3F49;
        }

        .perfume-card .cta-button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #8C3F49;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .perfume-card .cta-button:hover {
            background-color: #a64d4d;
        }

        .favorites-section {
            text-align: center;
            padding: 20px 0;
        }

        .favorites-section h2 {
            color: #8C3F49;
            margin-bottom: 20px;
            font-size: 28px;
            position: relative;
            display: inline-block;
        }

        .favorites-section h2::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 3px;
            background-color: #8C3F49;
            bottom: -10px;
            left: 25%;
        }
    </style>
</head>
<body>
    <?php include 'header.php' ?>

    <main>
        <!-- Slideshow Section -->
        <div class="slideshow">
            <div class="slides">
                <div class="slide"><img src="images/slide1.jpeg" alt="Slide 1"></div>
                <div class="slide"><img src="images/slide2.jpeg" alt="Slide 2"></div>
                <div class="slide"><img src="images/slide4.jpeg" alt="Slide 3"></div>
            </div>
            <!-- Navigation Dots -->
            <div class="dots">
                <div class="dot" data-index="0"></div>
                <div class="dot" data-index="1"></div>
                <div class="dot" data-index="2"></div>
            </div>
        </div>

        <!-- All Time Favourites Section -->
        <section class="favorites-section">
            <h2>ALL TIME FAVOURITES</h2>
            <div class="perfume-list">
                <?php if (!empty($perfumes)): ?>
                    <?php foreach ($perfumes as $perfume): ?>
                        <div class="perfume-card">
                            <a href="perfume_details.php?id=<?= htmlspecialchars($perfume['perfume_id']) ?>" class="cta-button">
                                <img src="<?= htmlspecialchars($perfume['image'] ?: 'images/default_image.jpeg') ?>" 
                                     alt="<?= htmlspecialchars($perfume['perfume_name'] ?? 'Perfume') ?>">
                            </a>
                            <h3><?= htmlspecialchars($perfume['perfume_name'] ?? 'Unknown Perfume') ?></h3>
                            <div class="price">
                                RM <?= number_format($perfume['display_price'], 2) ?>
                            </div>
                            <a href="perfume_details.php?id=<?= htmlspecialchars($perfume['perfume_id']) ?>" 
                            class="cta-button" 
                            onclick="console.log('Redirecting to perfume ID: <?= $perfume['perfume_id'] ?>')">
                                View Details
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No favorite perfumes available at the moment. Please check back later.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include('footer.php'); ?>

    <script>
        const slides = document.querySelector('.slides');
        const dots = document.querySelectorAll('.dot');

        let currentIndex = 0;
        const totalSlides = dots.length;

        // Function to show slide by index
        function showSlide(index) {
            slides.style.transform = `translateX(-${index * 100}%)`;
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        }

        // Auto-slide every 5 seconds
        setInterval(() => {
            currentIndex = (currentIndex + 1) % totalSlides;
            showSlide(currentIndex);
        }, 5000);

        // Add click events for dots
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                currentIndex = parseInt(dot.getAttribute('data-index'));
                showSlide(currentIndex);
            });
        });

        // Initial display
        showSlide(currentIndex);
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>