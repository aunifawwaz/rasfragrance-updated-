
<?php
session_start();
require_once 'db_connection.php';

// API endpoint to fetch questions
if (isset($_GET['get_question'])) {
    $question = $conn->query("SELECT * FROM perfume_quiz WHERE id = ".(int)$_GET['id'])->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($question);
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_question'])) {
        $id = $_POST['id'] ?? 0;
        $question_text = trim($_POST['question']);
        $sort_order = (int)$_POST['sort_order'];
        $question_type = $_POST['question_type'] ?? 'branch'; // Default to branch if not set
        $next_question_id = !empty($_POST['next_question_id']) ? (int)$_POST['next_question_id'] : null;
        
        // Process simplified answers
        $answers = [];
        foreach ($_POST['answer_keys'] as $index => $key) {
            $answers[$key] = [
                'text' => trim($_POST['answer_texts'][$index]),
                'perfume' => trim($_POST['answer_perfumes'][$index] ?? '')
            ];
        }
        
        $answers_json = json_encode($answers);
        
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE perfume_quiz SET 
                question = ?, 
                answers = ?, 
                sort_order = ?,
                question_type = ?,
                next_question_id = ?
                WHERE id = ?");
            $stmt->bind_param("ssisii", $question_text, $answers_json, $sort_order, $question_type, $next_question_id, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO perfume_quiz 
                (question, answers, sort_order, question_type, next_question_id) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisi", $question_text, $answers_json, $sort_order, $question_type, $next_question_id);
        }
        
        $stmt->execute();
        $_SESSION['message'] = "Question saved successfully!";
        header("Location: manage_quiz.php");
        exit();
    }
    
    if (isset($_POST['delete_id'])) {
        $stmt = $conn->prepare("DELETE FROM perfume_quiz WHERE id = ?");
        $stmt->bind_param("i", $_POST['delete_id']);
        $stmt->execute();
        $_SESSION['message'] = "Question deleted successfully!";
        header("Location: manage_quiz.php");
        exit();
    }
}

// Fetch all questions
$questions = [];
$result = $conn->query("SELECT * FROM perfume_quiz ORDER BY sort_order");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}

// Prepare for edit
$editing_question = [
    'id' => 0, 
    'question' => '', 
    'answers' => '{"A":{"text":"","perfume":""}}', 
    'sort_order' => count($questions) + 1,
    'next_question_id' => null,
    'question_type' => 'branch'
];
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    foreach ($questions as $q) {
        if ($q['id'] == $edit_id) {
            $editing_question = $q;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfume Quiz Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .tree-path {
            font-family: monospace;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .quiz-container {
            background: #e9ecef;
            padding: 20px;
            border-radius: 5px;
        }
        .answer-btn {
            text-align: left;
            transition: all 0.2s;
        }
        .answer-btn:hover {
            transform: translateX(5px);
        }
        .modal-xl {
            max-width: 90%;
        }
    </style>
</head>
<body>
    
    <div class="container py-4">
        <!-- Admin Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?= isset($_GET['quiz']) ? 'Perfume Quiz' : 'Quiz Manager' ?></h1>
            <?php if(!isset($_GET['quiz'])): ?>
                <div>
                    <a href="?quiz=1" class="btn btn-success me-2">Test Quiz</a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal">Add Question</button>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Quiz Mode -->
        <?php if (isset($_GET['quiz'])): ?>
            <div class="quiz-container" id="quiz-container">
                <!-- Quiz will be loaded here by JavaScript -->
            </div>
            
            <script>
                let currentQuestionId = <?= json_encode(array_values(array_filter($questions, fn($q) => $q['question_type'] == 'root'))[0]['id'] ?? 1) ?>;
                
                function loadQuestion(id) {
                    fetch(`?get_question=1&id=${id}`)
                        .then(r => r.json())
                        .then(q => {
                            document.getElementById('quiz-container').innerHTML = `
                                <h3 class="mb-4">${q.question}</h3>
                                <div class="list-group">
                                    ${Object.entries(JSON.parse(q.answers)).map(([key, a]) => `
                                        <button onclick="handleAnswer(${q.id}, '${key}')" 
                                                class="list-group-item list-group-item-action answer-btn">
                                            <strong>${key}.</strong> ${a.text}
                                        </button>
                                    `).join('')}
                                </div>
                            `;
                        });
                }
                
                function handleAnswer(qId, answerKey) {
                    const question = <?= json_encode($questions) ?>.find(q => q.id == qId);
                    const answer = JSON.parse(question.answers)[answerKey];
                    
                    if (answer.perfume) {
                        document.getElementById('quiz-container').innerHTML = `
                            <div class="text-center">
                                <h2 class="text-success">${answer.perfume}</h2>
                                <p class="lead">Your perfect fragrance match!</p>
                                <button onclick="loadQuestion(${currentQuestionId})" 
                                        class="btn btn-primary">
                                    Start Over
                                </button>
                            </div>
                        `;
                    } else if (question.next_question_id) {
                        loadQuestion(question.next_question_id);
                    }
                }
                
                // Initialize quiz
                loadQuestion(currentQuestionId);
            </script>

        <!-- Admin Mode -->
        <?php else: ?>
            <!-- Path Visualization -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Current Linear Path</h5>
                </div>
                <div class="card-body">
                    <div class="tree-path">
                        <?php
                        $current = array_values(array_filter($questions, fn($q) => $q['question_type'] == 'root'))[0] ?? null;
                        while ($current): 
                            $answers = json_decode($current['answers'], true);
                        ?>
                            <div class="mb-3">
                                <strong>Q<?= $current['id'] ?>:</strong> <?= $current['question'] ?>
                                <?php foreach ($answers as $key => $a): ?>
                                    <div class="ms-4">
                                        <?= $key ?>: <?= $a['text'] ?>
                                        <?php if(!empty($a['perfume'])): ?>
                                            → <span class="text-success"><?= $a['perfume'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php 
                            $current = $current['next_question_id'] 
                                ? array_values(array_filter($questions, fn($q) => $q['id'] == $current['next_question_id']))[0] 
                                : null;
                        endwhile; 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Question List -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>All Questions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($questions as $q): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5>Q<?= $q['id'] ?>: <?= htmlspecialchars($q['question']) ?></h5>
                                        <div class="mb-2">
                                            <?php 
                                            $answers = json_decode($q['answers'], true);
                                            foreach ($answers as $key => $a): ?>
                                                <span class="badge bg-light text-dark me-2">
                                                    <?= $key ?>: <?= htmlspecialchars($a['text']) ?>
                                                    <?php if(!empty($a['perfume'])): ?>
                                                        → <?= htmlspecialchars($a['perfume']) ?>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <small class="text-muted">
                                            Type: <?= strtoupper($q['question_type']) ?> | 
                                            Next: <?= $q['next_question_id'] ? 'Q'.$q['next_question_id'] : 'END' ?> | 
                                            Order: <?= $q['sort_order'] ?>
                                        </small>
                                    </div>
                                    <div class="btn-group">
                                        <a href="#" class="btn btn-sm btn-outline-primary edit-question-btn" 
                                           data-id="<?= $q['id'] ?>"
                                           data-question="<?= htmlspecialchars($q['question']) ?>"
                                           data-answers='<?= $q['answers'] ?>'
                                           data-type="<?= $q['question_type'] ?>"
                                           data-next="<?= $q['next_question_id'] ?>"
                                           data-order="<?= $q['sort_order'] ?>">Edit</a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this question?')">
                                            <input type="hidden" name="delete_id" value="<?= $q['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Question Modal -->
    <div class="modal fade" id="questionModal" tabindex="-1" aria-labelledby="questionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionModalLabel">Add/Edit Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editQuestionId" value="0">
                        
                        <div class="mb-3">
                            <label class="form-label">Question Text</label>
                            <input type="text" name="question" id="editQuestionText" class="form-control" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Question Type</label>
                                <select name="question_type" id="editQuestionType" class="form-select" required>
                                    <option value="root">Root Question</option>
                                    <option value="branch">Branch Question</option>
                                    <option value="terminal">Terminal Question</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Next Question</label>
                                <select name="next_question_id" id="editNextQuestion" class="form-select">
                                    <option value="">-- End of Path --</option>
                                    <?php foreach ($questions as $q): ?>
                                        <option value="<?= $q['id'] ?>">
                                            Q<?= $q['id'] ?>: <?= substr($q['question'], 0, 30) ?>...
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="editSortOrder" class="form-control" required>
                        </div>
                        
                        <h5>Answers</h5>
                        <div id="answers-container">
                            <!-- Answers will be loaded here -->
                        </div>
                        
                        <button type="button" id="add-answer" class="btn btn-secondary mb-3">+ Add Answer</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_question" class="btn btn-primary">Save Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize modal
        const questionModal = new bootstrap.Modal(document.getElementById('questionModal'));
        
        // Add new answer field
        document.getElementById('add-answer').addEventListener('click', function() {
            const container = document.getElementById('answers-container');
            const newAnswer = `
                <div class="answer-group mb-3 border p-3">
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" name="answer_keys[]" class="form-control" placeholder="Key" required>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="answer_texts[]" class="form-control" placeholder="Answer text" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="answer_perfumes[]" class="form-control" placeholder="Perfume">
                        </div>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', newAnswer);
        });

        // Edit question button handler
        document.querySelectorAll('.edit-question-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('editQuestionId').value = this.dataset.id;
                document.getElementById('editQuestionText').value = this.dataset.question;
                document.getElementById('editQuestionType').value = this.dataset.type;
                document.getElementById('editNextQuestion').value = this.dataset.next || '';
                document.getElementById('editSortOrder').value = this.dataset.order;
                
                // Load answers
                const container = document.getElementById('answers-container');
                container.innerHTML = '';
                const answers = JSON.parse(this.dataset.answers);
                
                for (const [key, answer] of Object.entries(answers)) {
                    const answerHtml = `
                        <div class="answer-group mb-3 border p-3">
                            <div class="row">
                                <div class="col-md-2">
                                    <input type="text" name="answer_keys[]" class="form-control" 
                                        value="${key}" required>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="answer_texts[]" class="form-control" 
                                        value="${answer.text}" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="answer_perfumes[]" class="form-control" 
                                        value="${answer.perfume || ''}">
                                </div>
                            </div>
                        </div>`;
                    container.insertAdjacentHTML('beforeend', answerHtml);
                }
                
                questionModal.show();
            });
        });

        // New question button handler
        document.querySelector('[data-bs-target="#questionModal"]').addEventListener('click', function() {
            document.getElementById('editQuestionId').value = '0';
            document.getElementById('editQuestionText').value = '';
            document.getElementById('editQuestionType').value = 'branch';
            document.getElementById('editNextQuestion').value = '';
            document.getElementById('editSortOrder').value = <?= count($questions) + 1 ?>;
            
            // Reset answers with one empty field
            const container = document.getElementById('answers-container');
            container.innerHTML = `
                <div class="answer-group mb-3 border p-3">
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" name="answer_keys[]" class="form-control" value="A" required>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="answer_texts[]" class="form-control" placeholder="Answer text" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="answer_perfumes[]" class="form-control" placeholder="Perfume">
                        </div>
                    </div>
                </div>`;
        });
    </script>
</body>
</html>