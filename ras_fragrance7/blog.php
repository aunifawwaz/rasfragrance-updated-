<?php
// Include database connection
include 'db_connection.php';

// Fetch the latest blog from the database
$query = "SELECT * FROM blogs ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($query);

// Default values when no blog exists
$blog = [
    'title' => 'No Blog Found',
    'content' => 'Currently, there are no blog posts available.',
    'image' => 'images/placeholder.png'
];

// Override defaults if blog exists
if ($result && $result->num_rows > 0) {
    $blog = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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
            position: relative;
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

        /* Blog Section Styling */
        .blog-section {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 80%;
            background-color: #f4f4f4;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .blog-image {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            background-color: #8C3F49;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
            overflow: hidden;
            margin: 1rem;
        }

        .blog-image img {
            max-width: 100%;
            height: auto;
        }

        .blog-content {
            flex: 2;
            min-width: 300px;
            margin: 1rem;
        }

        .blog-content h2 {
            font-size: 1.8rem;
            color: #8C3F49;
            margin-bottom: 1rem;
        }

        .blog-content p {
            font-size: 1rem;
            line-height: 1.6;
            color: #5a2a2a;
        }

        .blog-next {
            text-align: right;
            margin-top: 2rem;
        }

        .blog-next a {
            text-decoration: none;
            color: #8C3F49;
            font-weight: bold;
            font-size: 1rem;
        }

        .blog-next a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <!-- Blog Section - Content managed via admin panel -->
        <section class="blog-section">
            <div class="blog-image">
                <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="Blog Image">
            </div>
            
            <div class="blog-content">
                <h2><?php echo htmlspecialchars($blog['title']); ?></h2>
                <p><?php echo htmlspecialchars($blog['content']); ?></p>
            </div>
        </section>

        <!-- Next Button -->
        <div class="blog-next">
            <a href="next_blog.php">NEXT</a>
        </div>
    </main>
    
    <?php include('footer.php'); ?>
</body>
</html>