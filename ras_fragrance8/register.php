<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connection.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? $conn->real_escape_string($_POST['username']) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Do not escape for password hashing
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (!empty($username) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            // Check if username or email already exists
            $check_sql = "SELECT * FROM `User` WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = "Username or email already exists.";
            } else {
                // Insert the user into the database
                $insert_sql = "INSERT INTO `User` (username, email, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("sss", $username, $email, $password);

                if ($stmt->execute()) {
                    $message = "Registration successful!";
                    // Redirect to login page after successful registration
                    header('Location: login.php');
                    exit; // Make sure no further code is executed
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $message = "Passwords do not match.";
        }
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
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4e7e7;
            font-family: Arial, sans-serif;
        }
        
        .form-container {
            max-width: 500px;
            margin: 80px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .register-header {
            background-color: #8C3F49;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 10px;
        }

        .register-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }

        .btn-register {
            background-color: #8C3F49;
            color: white;
            border: none;
            padding: 12px 20px;
            width: 100%;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn-register:hover {
            background-color: #a64d4d;
        }

        .register-link:hover {
            text-decoration: none;
            color: #a64d4d;
        }

        .register-link{
            color: #a64d4d;
        }
        

        footer {
            text-align: center;
            padding: 20px;
            background-color: #8C3F49;
            color: white;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<header class="register-header">
    <h1>Register</h1>
</header>

<main class="container">
    <div class="form-container">
        <!-- Display Error or Success Message -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo strpos($message, 'successful') !== false ? 'success' : 'danger'; ?>" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn-register">Register</button>
        </form>

        <a href="login.php" class="register-link">Already have an account? Log In</a>
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
