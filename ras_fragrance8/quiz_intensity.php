<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['primary_scent'])) {
    header("Location: quiz.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    if (isset($_POST['intensity'])) { 
        $_SESSION['intensity'] = $_POST['intensity']; 
        header("Location: quiz_result.php"); 
        exit(); 
    } 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - Ras Fragrance</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<style>
        /* Updated styles for header layout */
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
            position: relative; /* To position icons and nav on top */
        }

        header .top-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .logo img {
            width: 100px; /* Reduce the size */
            height: auto;
            cursor: pointer; /* Prevents any inline alignment issues */
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
        .quiz-section {
        text-align: center;
        margin: 20px 0;
    }

    .quiz-section h2 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .quiz-question {
        margin: 15px 0;
    }

    .quiz-option {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin: 10px 0;
    }

    .quiz-option button {
        padding: 10px 20px;
        background-color: #8C3F49;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .quiz-option button:hover {
        background-color: #722f3a;
    }


    </style>
    <header>
    <div class="top-section">
            <div class="logo">
    <!-- Clickable Logo -->
    <a href="home.php" title="Home">
        <img src="images/logo.jpeg" alt="Logo Icon" title="Logo" class="logo">
    </a></div>

    <!-- Clickable Icons -->
    <div class="icons">
        <a href="search.php" title="Search">
            <img src="images/search.png" alt="Search Icon" title="Search">
        </a>
        <a href="profile.php" title="Profile">
            <img src="images/profile.png" alt="Profile Icon" title="Profile">
        </a>
        <a href="cart.php" title="Cart">
            <img src="images/cart.png" alt="Cart Icon" title="Cart">
        </a>
    </div>
</div>

        <h1>Ras Fragrance</h1>
        <h2>The Essence of Luxury</h2>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="quiz.php">Quiz</a></li>
                <li><a href="shop.php">Shop</a></li>
                <!--li><a href="blog.php">Blog</a></li-->
            
                <!--li><a href="login.php">Login</a></li-->
            </ul>
        </nav>
    </header>
    <main> 
        <section class="quiz-section"> 
            <h2>Know Your Fragrance</h2> 
            <form method="POST" action="quiz_intensity.php"> 
                <div class="quiz-question"> 
                    <?php if ($_SESSION['primary_scent'] == 'floral'): ?> 
                        <label>How do you prefer the floral scent?</label> 
                        <div class="quiz-option"> 
                            <button type="submit" name="intensity" value="light">Light</button> 
                            <button type="submit" name="intensity" value="intense">Intense</button> 
                        </div>
                    <?php elseif ($_SESSION['primary_scent'] == 'woody'): ?> 
                        <label>How do you prefer the woody scent?</label> 
                        <div class="quiz-option"> 
                            <button type="submit" name="intensity" value="fresh">Fresh</button> 
                            <button type="submit" name="intensity" value="smoky">Smoky</button> 
                        </div> 
                    <?php elseif ($_SESSION['primary_scent'] == 'citrus'): ?> 
                        <label>How do you prefer the citrus scent?</label> 
                        <div class="quiz-option"> 
                            <button type="submit" name="intensity" value="sweet">Sweet</button>
                            <button type="submit" name="intensity" value="tangy">Tangy</button> 
                        </div> 
                    <?php endif; ?> 
                </div> 
            </form> 
        </section> 
        <a href="quiz.php" class="cta-button">Back</a>
    </main>
    <footer>
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>
</body>
</html>
