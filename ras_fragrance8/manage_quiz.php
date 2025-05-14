<?php
session_start();
require_once 'db_connection.php';

// Force no caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// API endpoint to fetch questions
if (isset($_GET['get_question'])) {
    $question = $conn->query("SELECT * FROM perfume_quiz WHERE id = ".(int)$_GET['id'])->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($question);
    exit();
}

// Set current page for menu highlighting
$current_page = 'manage_quiz.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_question'])) {
        $id = $_POST['id'] ?? 0;
        $question_text = trim($_POST['question']);
        $sort_order = (int)$_POST['sort_order'];
        $question_type = $_POST['question_type'] ?? 'branch';
        $next_question_id = !empty($_POST['next_question_id']) ? (int)$_POST['next_question_id'] : null;
        
        // Process answers with branching support
        $answers = [];
        foreach ($_POST['answer_keys'] as $index => $key) {
            $answers[$key] = [
                'text' => trim($_POST['answer_texts'][$index]),
                'perfume' => trim($_POST['answer_perfumes'][$index] ?? '')
            ];
            
            // Add branching logic if enabled
            if (!empty($_POST['branch_answer_keys'])) {
                $branchIndex = array_search($key, $_POST['branch_answer_keys']);
                if ($branchIndex !== false) {
                    $answers[$key]['next_question_id'] = (int)$_POST['branch_next_questions'][$branchIndex];
                }
            }
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
    <title>Perfume Quiz Manager - RAS Fragrance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8C3F49;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            min-height: 100vh;
            background: var(--dark);
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 0.75rem 1rem;
            margin-bottom: 0.2rem;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .page-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .quiz-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
        }
        
        .answer-btn {
            transition: all 0.2s;
            margin-bottom: 0.5rem;
            border-radius: 0.25rem;
            text-align: left;
        }
        
        .answer-btn:hover {
            transform: translateX(5px);
            background-color: #f8f9fa;
        }
        
        .tree-path {
            font-family: monospace;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .modal-xl {
            max-width: 90%;
        }
        
        .branch-path {
            margin-left: 20px;
            border-left: 2px solid #ddd;
            padding-left: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Menu -->
            <?php include 'admin_menu.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 page-header">
                    <h1 class="h2"><?= isset($_GET['quiz']) ? 'Perfume Quiz' : 'Quiz Manager' ?></h1>
                    <?php if(!isset($_GET['quiz'])): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <a href="?quiz=1" class="btn btn-success">
                                    <i class="fas fa-play"></i> Test Quiz
                                </a>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal">
                                    <i class="fas fa-plus"></i> Add Question
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if (isset($_GET['quiz'])): ?>
                    <!-- Quiz Mode -->
                    <div class="quiz-container" id="quiz-container">
                        <!-- Quiz will be loaded here by JavaScript -->
                    </div>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const questions = <?= json_encode($questions) ?>;
                            let currentQuestion = questions.find(q => q.question_type === 'root');
                            
                            if (!currentQuestion) {
                                document.getElementById('quiz-container').innerHTML = `
                                    <div class="alert alert-danger">
                                        No root question found. Please set a question as root type.
                                    </div>`;
                                return;
                            }
                            
                            function loadQuestion(question) {
                                try {
                                    const answers = JSON.parse(question.answers);
                                    let answersHTML = '';
                                    
                                    for (const [key, answer] of Object.entries(answers)) {
                                        answersHTML += `
                                            <button onclick="handleAnswer('${key}')" 
                                                    class="list-group-item list-group-item-action answer-btn">
                                                <strong>${key}.</strong> ${answer.text}
                                            </button>`;
                                    }
                                    
                                    document.getElementById('quiz-container').innerHTML = `
                                        <h3 class="mb-4">${question.question}</h3>
                                        <div class="list-group">
                                            ${answersHTML}
                                        </div>`;
                                } catch (e) {
                                    console.error('Error parsing answers:', e);
                                    showError('Error loading question format');
                                }
                            }
                            
                            window.handleAnswer = function(answerKey) {
                                try {
                                    const answers = JSON.parse(currentQuestion.answers);
                                    const selectedAnswer = answers[answerKey];
                                    
                                    if (!selectedAnswer) {
                                        throw new Error(`Answer ${answerKey} not found`);
                                    }
                                    
                                    // Check for direct perfume result
                                    if (selectedAnswer.perfume) {
                                        showResult(selectedAnswer.perfume);
                                        return;
                                    }
                                    
                                    // Check for branch-specific next question
                                    if (selectedAnswer.next_question_id) {
                                        const nextQuestion = questions.find(q => q.id == selectedAnswer.next_question_id);
                                        if (nextQuestion) {
                                            currentQuestion = nextQuestion;
                                            loadQuestion(currentQuestion);
                                            return;
                                        }
                                    }
                                    
                                    // Fall back to default next question
                                    if (currentQuestion.next_question_id) {
                                        const nextQuestion = questions.find(q => q.id == currentQuestion.next_question_id);
                                        if (nextQuestion) {
                                            currentQuestion = nextQuestion;
                                            loadQuestion(currentQuestion);
                                            return;
                                        }
                                    }
                                    
                                    showCompletion();
                                } catch (e) {
                                    console.error('Error handling answer:', e);
                                    showError('Error processing your answer');
                                }
                            };
                            
                            window.restartQuiz = function() {
                                currentQuestion = questions.find(q => q.question_type === 'root');
                                if (currentQuestion) {
                                    loadQuestion(currentQuestion);
                                }
                            };
                            
                            function showResult(perfume) {
                                document.getElementById('quiz-container').innerHTML = `
                                    <div class="text-center">
                                        <h2 class="text-success">${perfume}</h2>
                                        <p class="lead">Your perfect fragrance match!</p>
                                        <button onclick="restartQuiz()" class="btn btn-primary">
                                            Start Over
                                        </button>
                                    </div>`;
                            }
                            
                            function showCompletion() {
                                document.getElementById('quiz-container').innerHTML = `
                                    <div class="alert alert-info">
                                        Quiz completed! No further questions available.
                                    </div>
                                    <button onclick="restartQuiz()" class="btn btn-primary">
                                        Start Over
                                    </button>`;
                            }
                            
                            function showError(message) {
                                document.getElementById('quiz-container').innerHTML = `
                                    <div class="alert alert-danger">
                                        ${message}. Please try again.
                                    </div>
                                    <button onclick="restartQuiz()" class="btn btn-primary">
                                        Restart Quiz
                                    </button>`;
                            }
                            
                            // Start the quiz
                            loadQuestion(currentQuestion);
                        });
                    </script>
                <?php else: ?>
                    <!-- Admin Mode -->
                    <!-- Path Visualization -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Decision Tree Paths</h5>
                        </div>
                        <div class="card-body">
                            <div class="tree-path">
                                <?php
                                function renderQuestionPath($questionId, $questions, $indent = 0) {
                                    $question = $questions[array_search($questionId, array_column($questions, 'id'))] ?? null;
                                    if (!$question) return;
                                    
                                    $answers = json_decode($question['answers'], true);
                                    ?>
                                    <div class="mb-3" style="margin-left: <?= $indent * 20 ?>px;">
                                        <strong>Q<?= $question['id'] ?>:</strong> <?= $question['question'] ?>
                                        <?php foreach ($answers as $key => $a): ?>
                                            <div class="ms-4">
                                                <?= $key ?>: <?= $a['text'] ?>
                                                <?php if(!empty($a['perfume'])): ?>
                                                    → <span class="text-success"><?= $a['perfume'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php 
                                            if (!empty($a['next_question_id'])) {
                                                renderQuestionPath($a['next_question_id'], $questions, $indent + 1);
                                            }
                                            ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php
                                }
                                
                                // Find all root questions
                                $roots = array_filter($questions, fn($q) => $q['question_type'] == 'root');
                                foreach ($roots as $root) {
                                    renderQuestionPath($root['id'], $questions);
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Question List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">All Questions</h5>
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
                                                            <?php if(!empty($a['next_question_id'])): ?>
                                                                → Q<?= $a['next_question_id'] ?>
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
                                                   data-order="<?= $q['sort_order'] ?>">
                                                   <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this question?')">
                                                    <input type="hidden" name="delete_id" value="<?= $q['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
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
                            <div class="col-md-4">
                                <label class="form-label">Question Type</label>
                                <select name="question_type" id="editQuestionType" class="form-select" required>
                                    <option value="root">Root Question</option>
                                    <option value="branch">Branch Question</option>
                                    <option value="terminal">Terminal Question</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Next Question (default)</label>
                                <select name="next_question_id" id="editNextQuestion" class="form-select">
                                    <option value="">-- End of Path --</option>
                                    <?php foreach ($questions as $q): ?>
                                        <option value="<?= $q['id'] ?>">
                                            Q<?= $q['id'] ?>: <?= substr($q['question'], 0, 30) ?>...
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="editSortOrder" class="form-control" required>
                            </div>
                        </div>
                        
                        <h5>Answers</h5>
                        <div id="answers-container">
                            <!-- Answers will be loaded here -->
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <button type="button" id="add-answer" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i> Add Answer
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enableBranching">
                                    <label class="form-check-label" for="enableBranching">
                                        Enable Branching Logic
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Branching logic container (hidden by default) -->
                        <div id="branching-container" style="display: none;">
                            <h5>Branching Rules</h5>
                            <div id="branch-rules-container">
                                <!-- Branch rules will be added here -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="save_question" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questionModal = new bootstrap.Modal(document.getElementById('questionModal'));
            const enableBranching = document.getElementById('enableBranching');
            const branchingContainer = document.getElementById('branching-container');
            
            // Toggle branching container visibility
            enableBranching.addEventListener('change', function() {
                branchingContainer.style.display = this.checked ? 'block' : 'none';
                if (this.checked && document.getElementById('branch-rules-container').children.length === 0) {
                    // Add branch rules for existing answers if enabling branching
                    const answerInputs = document.querySelectorAll('input[name="answer_keys[]"]');
                    answerInputs.forEach(input => {
                        addBranchRule(input.value);
                    });
                }
            });
            
            // Add answer button
            document.getElementById('add-answer').addEventListener('click', function() {
                const key = String.fromCharCode(65 + document.querySelectorAll('input[name="answer_keys[]"]').length);
                addAnswerRow(key, '', '');
                if (enableBranching.checked) {
                    addBranchRule(key);
                }
            });
            
            // Edit question button handler
            document.querySelectorAll('.edit-question-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('editQuestionId').value = this.dataset.id;
                    document.getElementById('editQuestionText').value = this.dataset.question;
                    document.getElementById('editQuestionType').value = this.dataset.type;
                    document.getElementById('editNextQuestion').value = this.dataset.next || '';
                    document.getElementById('editSortOrder').value = this.dataset.order;
                    
                    const answers = JSON.parse(this.dataset.answers);
                    const hasBranching = Object.values(answers).some(a => a.next_question_id);
                    
                    enableBranching.checked = hasBranching;
                    branchingContainer.style.display = hasBranching ? 'block' : 'none';
                    
                    // Clear containers
                    document.getElementById('answers-container').innerHTML = '';
                    document.getElementById('branch-rules-container').innerHTML = '';
                    
                    // Add answer rows and branch rules
                    for (const [key, answer] of Object.entries(answers)) {
                        addAnswerRow(key, answer.text, answer.perfume);
                        if (hasBranching) {
                            addBranchRule(key, answer.next_question_id);
                        }
                    }
                    
                    questionModal.show();
                });
            });
            
            // New question button
            document.querySelector('[data-bs-target="#questionModal"]')?.addEventListener('click', function() {
                // Reset form
                document.getElementById('editQuestionId').value = '0';
                document.getElementById('editQuestionText').value = '';
                document.getElementById('editQuestionType').value = 'branch';
                document.getElementById('editNextQuestion').value = '';
                document.getElementById('editSortOrder').value = <?= count($questions) + 1 ?>;
                enableBranching.checked = false;
                branchingContainer.style.display = 'none';
                
                // Clear containers
                document.getElementById('answers-container').innerHTML = '';
                document.getElementById('branch-rules-container').innerHTML = '';
                
                // Add default answer
                addAnswerRow('A', '', '');
                
                // Show modal
                questionModal.show();
            });
            
            // Helper function to add answer row
            function addAnswerRow(key = '', text = '', perfume = '') {
                const container = document.getElementById('answers-container');
                const rowId = Date.now();
                
                container.insertAdjacentHTML('beforeend', `
                    <div class="answer-group mb-3 border p-3" data-row-id="${rowId}">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="answer_keys[]" class="form-control" 
                                    value="${key}" required>
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="answer_texts[]" class="form-control" 
                                    value="${text}" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="answer_perfumes[]" class="form-control" 
                                    value="${perfume}" placeholder="Perfume or next question">
                            </div>
                        </div>
                    </div>`);
            }
            
            // Helper function to add branch rule
            function addBranchRule(answerKey = '', nextQuestionId = '') {
                const container = document.getElementById('branch-rules-container');
                
                container.insertAdjacentHTML('beforeend', `
                    <div class="branch-rule mb-3 border p-3">
                        <div class="row">
                            <div class="col-md-4">
                                <label>If answer is:</label>
                                <input type="text" name="branch_answer_keys[]" class="form-control" 
                                    value="${answerKey}" readonly>
                            </div>
                            <div class="col-md-8">
                                <label>Then go to:</label>
                                <select name="branch_next_questions[]" class="form-select">
                                    <option value="">-- End of Path --</option>
                                    <?php foreach ($questions as $q): ?>
                                        <option value="<?= $q['id'] ?>" ${nextQuestionId == <?= $q['id'] ?> ? 'selected' : ''}>
                                            Q<?= $q['id'] ?>: <?= substr($q['question'], 0, 30) ?>...
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>`);
            }
        });
    </script>
</body>
</html>