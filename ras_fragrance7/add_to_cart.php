<?php
session_start();
include 'db_connection.php';

$response = ['success' => false, 'count' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['perfume_id'], $_POST['quantity'])) {
    $perfume_id = intval($_POST['perfume_id']);
    $quantity = intval($_POST['quantity']);

    // Fetch perfume details
    $stmt = $conn->prepare("SELECT perfume_id, perfume_name, price FROM perfume WHERE perfume_id = ?");
    $stmt->bind_param("i", $perfume_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $perfume = $result->fetch_assoc();

    if ($perfume) {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add to cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['perfume_id'] == $perfume_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = [
                'perfume_id' => $perfume['perfume_id'],
                'perfume_name' => $perfume['perfume_name'],
                'price' => $perfume['price'],
                'quantity' => $quantity
            ];
        }

        // Sync with database if user is logged in
        if (isset($_SESSION['user_id'])) {
            // Clear existing items
            $conn->query("DELETE FROM cart WHERE user_id = " . $_SESSION['user_id']);
            
            // Insert current items
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $conn->prepare("INSERT INTO cart (user_id, perfume_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $_SESSION['user_id'], $item['perfume_id'], $item['quantity']);
                $stmt->execute();
            }
        }

        $response['success'] = true;
        $response['count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>