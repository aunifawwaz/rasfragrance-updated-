<?php
session_start(); // Ensure session is started at the beginning
include 'db_connection.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch input values
    $username = isset($_POST['username']) ? $conn->real_escape_string($_POST['username']) : '';
    $password = isset($_POST['password']) ? $conn->real_escape_string($_POST['password']) : '';

    if (!empty($username) && !empty($password)) {
        // Query the database for the username
        $query = "SELECT * FROM `User` WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Compare plaintext passwords (NOTE: In production, use password_verify() with hashed passwords)
            if ($password === $user['password']) {
                // Set all necessary session variables
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Initialize cart count if not set
                if (!isset($_SESSION['cart_count'])) {
                    $_SESSION['cart_count'] = 0;
                }

                // Redirect to home.php
                header('Location: home.php');
                exit;
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "Invalid username.";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all the fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RAS FRAGRANCE</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4e7e7;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .form-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 1;
        }
        
        .login-header {
            background-color: #8C3F49;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        
        .btn-login {
            background-color: #8C3F49;
            color: white;
            border: none;
            padding: 10px 20px;
            width: 100%;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .btn-login:hover {
            background-color: #a64d4d;
            color: white;
        }
        
        .register-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #8C3F49;
        }
        
        .register-link:hover {
            text-decoration: none;
            color: #a64d4d;
        }
        
        footer {
            text-align: center;
            padding: 20px;
            background-color: #8C3F49;
            color: white;
            margin-top: auto;
        }
        
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
   

    <div class="login-header">
        <h1>Login to Your Account</h1>
    </div>

    <main class="container">
        <div class="form-container">
            <!-- Display Error Message -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo strpos($message, 'Incorrect') !== false ? 'danger' : 'warning'; ?>" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-login">LOGIN</button>
            </form>

            <a href="register.php" class="register-link">Don't have an account? Register here</a>
        </div>
    </main>

    <footer>
    <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS and Dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>