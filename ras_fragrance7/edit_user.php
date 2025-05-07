<?php
session_start();

// Ensure session role exists and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit(); // Stop script execution
}

include 'db_connection.php';

$message = "";

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user details for editing
    $sql = "SELECT user_id, username, email, role FROM `User` WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $message = "User not found.";
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 0;

        if (!empty($username) && !empty($email)) {
            // Update user info
            $update_sql = "UPDATE `User` SET username = ?, email = ?, role = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssii", $username, $email, $role, $user_id);

            if ($stmt->execute()) {
                $message = "User updated successfully.";
            } else {
                $message = "Error updating user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Please fill in all the fields.";
        }
    }
} else {
    header('Location: manage_users.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Edit User</h1>
    </header>
    <style>
        /* Back Button Styled the Same as the Submit Button */
.back-link {
    display: inline-block;
    padding: 12px 20px;
    background-color: #8C3F49;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 16px;
    text-align: center;
    margin-top: 20px;  /* Adds spacing between the back link and the form */
    margin-bottom: 20px; /* Adds spacing between the button and the form */
    transition: background-color 0.3s ease;
}

.back-link:hover {
    background-color: #a64d4d;  /* Darker red on hover */
}

/* Button Styling for the Update User Button */
button {
    padding: 12px 20px;
    background-color: #8C3F49;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #a64d4d;  /* Darker red on hover */
}
S
    </style>

    <main>
        <?php if (!empty($message)): ?>
            <p style="color: <?= strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
            <label>Role:</label>
            <select name="role">
                <option value="0" <?= $user['role'] == 0 ? 'selected' : '' ?>>User</option>
                <option value="1" <?= $user['role'] == 1 ? 'selected' : '' ?>>Admin</option>
            </select><br>
            <button type="submit">Update User</button>
        </form>

        <a href="manage_users.php" class="back-link">Back to Manage Users</a>
    </main>

    <footer>
        <p>&copy; 2025 Ras Fragrance. All rights reserved.</p>
    </footer>
</body>
</html>
