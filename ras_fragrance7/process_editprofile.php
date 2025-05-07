<?php
// Start session
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Validate and sanitize input data
$user_id = $_SESSION['user_id'];
$username = htmlspecialchars(trim($_POST['username']));
$email = htmlspecialchars(trim($_POST['email']));
$phone_no = htmlspecialchars(trim($_POST['phone_no']));
$address = htmlspecialchars(trim($_POST['address']));

// Check for empty fields (optional but recommended)
if (empty($username) || empty($email) || empty($phone_no) || empty($address)) {
    die("All fields are required.");
}

// Prepare the update query
$update_query = "UPDATE User SET username = ?, email = ?, phone_no = ?, address = ? WHERE user_id = ?";
$stmt = $conn->prepare($update_query);

if ($stmt) {
    $stmt->bind_param("ssssi", $username, $email, $phone_no, $address, $user_id);
    if ($stmt->execute()) {
        // Redirect or show a success message
        header("Location: profile.php?update=success");
        exit;
    } else {
        die("Error updating profile: " . $stmt->error);
    }
} else {
    die("Query preparation failed: " . $conn->error);
}
?>
