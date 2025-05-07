<?php
session_start();

// Ensure session role exists and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit(); // Stop script execution
}

include 'db_connection.php';

// Check if delete request is made
if (isset($_POST['delete_perfume']) && isset($_POST['perfume_id'])) {
    $perfume_id = $_POST['perfume_id'];

    // Prepare and execute delete query
    $delete_sql = "DELETE FROM Perfume WHERE perfume_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $perfume_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error deleting perfume"]);
    }

    $stmt->close();
}

$conn->close();
?>
