<?php
session_start();
require_once 'db_connection.php';

// Initialize variables
$questions = [];
$editing_question = ['id' => 0, 'question' => '', 'answers' => '{"A":{"text":"","perfume":""}}'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle question saving
    if (isset($_POST['save_question'])) {
        $id = $_POST['id'] ?? 0;
        $question_text = trim($_POST['question']);
        
        // Process answers
        $answers = [];
        foreach ($_POST['answer_keys'] as $index => $key) {
            $text = trim($_POST['answer_texts'][$index]);
            $perfume = trim($_POST['answer_perfumes'][$index] ?? '');
            if (!empty($key) {
                $answers[$key] = ['text' => $text, 'perfume' => $perfume];
            }
        }
        
        if (!empty($question_text) {
            $answers_json = json_encode($answers);
            
            if ($id > 0) {
                // Update existing question
                $stmt = $conn->prepare("UPDATE perfume_quiz SET question = ?, answers = ? WHERE id = ?");
                $stmt->bind_param("ssi", $question_text, $answers_json, $id);
            } else {
                // Insert new question
                $stmt = $conn->prepare("INSERT INTO perfume_quiz (question, answers) VALUES (?, ?)");
                $stmt->bind_param("ss", $question_text, $answers_json);
            }
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Question saved successfully!";
            } else {
                $_SESSION['error'] = "Error saving question: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Question text cannot be empty!";
        }
    }
    
    // Handle other actions (delete, toggle status, update order)
    if (isset($_POST['delete_id'])) {
        $stmt = $conn->prepare("DELETE FROM perfume_quiz WHERE id = ?");
        $stmt->bind_param("i", $_POST['delete_id']);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Question deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting question: " . $conn->error;
        }
    }
    
    if (isset($_POST['toggle_status'])) {
        $stmt = $conn->prepare("UPDATE perfume_quiz SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $_POST['toggle_status']);
        $stmt->execute();
    }
    
    if (isset($_POST['update_order'])) {
        foreach ($_POST['order'] as $id => $order) {
            $stmt = $conn->prepare("UPDATE perfume_quiz SET sort_order = ? WHERE id = ?");
            $stmt->bind_param("ii", $order, $id);
            $stmt->execute();
        }
        $_SESSION['message'] = "Sort order updated!";
    }
    
    // Stay on the same page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Check if editing a specific question
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $result = $conn->query("SELECT * FROM perfume_quiz WHERE id = $id");
        if ($result->num_rows > 0) {
            $editing_question = $result->fetch_assoc();
        }
    }
}

// Fetch all questions
$result = $conn->query("SELECT * FROM perfume_quiz ORDER BY sort_order");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quiz Questions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sortable-ghost { opacity: 0.5; background: #cce5ff; }
        .question-item { cursor: move; }
        .answer-group { position: relative; }
        .remove-answer { position: absolute; right: 10px; top: 10px; cursor: pointer; color: #dc3545; }
        .edit-form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Quiz Questions</h1>
            <a href="?edit=0" class="btn btn-primary">Add New Question</a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Edit Form (shown when editing/adding) -->
        <?php if (isset($_GET['edit'])): ?>
            <div class="edit-form">
                <form method="post">
                    <input type="hidden" name="id" value="<?= $editing_question['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <input type="text" name="question" value="<?= htmlspecialchars($editing_question['question']) ?>" class="form-control" required>
                    </div>

                    <div id="answers-container">
                        <?php foreach (json_decode($editing_question['answers'], true) as $key => $ans): ?>
                            <div class="answer-group mb-3 border p-3">
                                <span class="remove-answer" onclick="this.parentElement.remove()">✕</span>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Answer Key (A/B/C/D)</label>
                                        <input type="text" name="answer_keys[]" value="<?= $key ?>" class="form-control" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Answer Text</label>
                                        <input type="text" name="answer_texts[]" value="<?= htmlspecialchars($ans['text']) ?>" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Linked Perfume</label>
                                        <input type="text" name="answer_perfumes[]" value="<?= htmlspecialchars($ans['perfume'] ?? '') ?>" class="form-control">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" id="add-answer" class="btn btn-secondary mb-3">+ Add Answer</button>
                    <button type="submit" name="save_question" class="btn btn-primary">Save Question</button>
                    <a href="manage_quiz.php" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <!-- Questions List -->
        <form method="post" id="sort-form">
            <div class="list-group" id="questions-list">
                <?php foreach ($questions as $question): ?>
                    <div class="list-group-item question-item" data-id="<?= $question['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="handle me-3">☰</span>
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($question['question']) ?></h5>
                                    <small class="text-muted">
                                        Answers: <?= implode(', ', array_keys(json_decode($question['answers'], true))) ?>
                                    </small>
                                </div>
                            </div>
                            <div class="btn-group">
                                <a href="?edit=<?= $question['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="toggle_status" value="<?= $question['id'] ?>">
                                    <button type="submit" class="btn btn-sm <?= $question['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                        <?= $question['is_active'] ? 'Active' : 'Inactive' ?>
                                    </button>
                                </form>
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this question?')">
                                    <input type="hidden" name="delete_id" value="<?= $question['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                        <input type="hidden" name="order[<?= $question['id'] ?>]" value="<?= $question['sort_order'] ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-3">
                <button type="submit" name="update_order" class="btn btn-primary">Save Order</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <script>
        // Make questions sortable
        new Sortable(document.getElementById('questions-list'), {
            handle: '.handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                document.querySelectorAll('#questions-list .question-item').forEach((item, index) => {
                    item.querySelector('input[name^="order"]').value = index + 1;
                });
            }
        });

        // Add answer field
        document.getElementById('add-answer')?.addEventListener('click', function() {
            const container = document.getElementById('answers-container');
            const newAnswer = `
                <div class="answer-group mb-3 border p-3">
                    <span class="remove-answer" onclick="this.parentElement.remove()">✕</span>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="answer_keys[]" placeholder="A" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="answer_texts[]" placeholder="Answer text" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="answer_perfumes[]" placeholder="Perfume name" class="form-control">
                        </div>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', newAnswer);
        });
    </script>
</body>
</html>