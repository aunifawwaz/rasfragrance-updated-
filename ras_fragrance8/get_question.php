<?php
// get_question.php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM perfume_quiz WHERE id = $id");
    
    if ($result && $result->num_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Question not found']);
    }
} else {
    echo json_encode(['error' => 'No question ID provided']);
}
?>