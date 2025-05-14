<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$perfume_id = $_POST['perfume_id'] ?? 0;

if ($action === 'add') {
    // Check if already favorited
    $check = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND perfume_id = ?");
    $check->bind_param("ii", $user_id, $perfume_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, perfume_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $perfume_id);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Added to favorites']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'Already in favorites']);
    }
} elseif ($action === 'remove') {
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND perfume_id = ?");
    $stmt->bind_param("ii", $user_id, $perfume_id);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'Removed from favorites']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>