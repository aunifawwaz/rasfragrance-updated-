<?php
session_start();
include 'db_connection.php';

// Get the first question (root question)
$result = $conn->query("SELECT * FROM perfume_quiz WHERE question_type = 'root' AND is_active = 1 ORDER BY sort_order LIMIT 1");
$current_question = $result->fetch_assoc();

if (!$current_question) {
    die("No active quiz questions found. Please set up the quiz in admin.");
}

// Function to get perfume details by name with proper error handling
function getPerfumeDetailsByName($conn, $perfume_name) {
    if (empty($perfume_name)) {
        return null;
    }
    
    try {
        $stmt = $conn->prepare("SELECT perfume_id, perfume_name, price, image FROM perfume WHERE perfume_name = ?");
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        
        $stmt->bind_param("s", $perfume_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    } catch (Exception $e) {
        error_log("Error getting perfume details: " . $e->getMessage());
        return null;
    }
}

// Process AJAX request for getting questions
if (isset($_GET['get_question']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM perfume_quiz WHERE id = $id");
    $question = $result ? $result->fetch_assoc() : null;
    
    if ($question) {
        // Pre-load perfume details for answers
        $answers = json_decode($question['answers'], true) ?: [];
        foreach ($answers as &$answer) {
            if (!empty($answer['perfume'])) {
                $perfumeDetails = getPerfumeDetailsByName($conn, $answer['perfume']);
                if ($perfumeDetails) {
                    $answer['perfume_id'] = $perfumeDetails['perfume_id'];
                    $answer['perfume_image'] = $perfumeDetails['image'];
                    $answer['perfume_price'] = $perfumeDetails['price'];
                }
            }
        }
        $question['answers'] = json_encode($answers);
    }
    
    header('Content-Type: application/json');
    echo json_encode($question ?: ['error' => 'Question not found']);
    exit();
}

if (isset($_SESSION['role']) && $_SESSION['role'] == 1) {
    header('Location: admin_dashboard.php');
    exit();
}

// Pre-load perfume details for all answers in the first question with proper checks
$answers = json_decode($current_question['answers'], true) ?: [];
foreach ($answers as &$answer) {
    if (!empty($answer['perfume'])) {
        $perfumeDetails = getPerfumeDetailsByName($conn, $answer['perfume']);
        if ($perfumeDetails) {
            $answer['perfume_id'] = $perfumeDetails['perfume_id'];
            $answer['perfume_image'] = $perfumeDetails['image'];
            $answer['perfume_price'] = $perfumeDetails['price'];
        }
    }
}
$current_question['answers'] = json_encode($answers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Your Scent To You - RAS Fragrance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .quiz-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 15px;
        }
        
        .quiz-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            overflow: hidden;
        }
        
        .quiz-header {
            background-color: #8C3F49;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .quiz-header h1 {
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .quiz-header h2 {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 20px;
        }
        
        .quiz-body {
            padding: 30px;
            background-color: white;
        }
        
        .quiz-title {
            color: #8C3F49;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .question-text {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #333;
            font-weight: 500;
        }
        
        .answer-btn {
            width: 100%;
            text-align: left;
            padding: 15px 20px;
            margin-bottom: 12px;
            border-radius: 8px;
            border: 2px solid #8C3F49;
            color: #8C3F49;
            background: white;
            transition: all 0.3s;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .answer-btn:hover, .answer-btn:focus {
            transform: translateX(5px);
            background-color: #8C3F49;
            color: white;
        }
        
        #result {
            display: none;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
            border-top: 5px solid #8C3F49;
        }
        
        #result-perfume {
            color: #8C3F49;
            font-weight: bold;
            font-size: 1.8rem;
            margin: 15px 0;
        }
        
        .start-btn {
            background-color: #8C3F49;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1.2rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .start-btn:hover {
            background-color: #6a2f3a;
            transform: translateY(-2px);
        }
        
        .divider {
            border-top: 1px solid #ddd;
            margin: 30px 0;
        }
        
        /* Make sure header doesn't affect quiz layout */
        header + .container {
            margin-top: 0 !important;
        }
    </style>
</head>
<body>
    <?php include 'header.php' ?>

    <div class="quiz-container">
        <div class="quiz-card">
            <div class="quiz-header">
                <h1>Match Your Scent To You</h1>
                <h2>SCENT FINDER QUIZ</h2>
                <p>Finding your signature scent can be a challenge, but we're here to make it easier. Just share your preferences, and we'll match you with fragrances that fit your style and personality.</p>
                <button id="start-quiz-btn" class="start-btn">Begin My Experience</button>
            </div>
            
            <div id="quiz-content" style="display:none;">
                <div class="quiz-body">
                    <h3 class="quiz-title">Perfume Personality Quiz</h3>
                    
                    <form id="quiz-form">
                        <input type="hidden" id="question-id" value="<?= htmlspecialchars($current_question['id'] ?? '') ?>">
                        <div class="mb-4">
                            <h4 class="question-text"><?= htmlspecialchars($current_question['question'] ?? '') ?></h4>
                        </div>
                        <div class="answer-buttons">
                            <?php 
                            $answers = json_decode($current_question['answers'] ?? '[]', true);
                            foreach ($answers as $key => $ans): 
                                $perfume_id = $ans['perfume_id'] ?? '';
                                $perfume_image = $ans['perfume_image'] ?? '';
                                $perfume_price = $ans['perfume_price'] ?? '';
                            ?>
                                <button type="button" class="btn answer-btn"
                                    data-perfume="<?= htmlspecialchars($ans['perfume'] ?? '') ?>"
                                    data-perfume-id="<?= $perfume_id ?>"
                                    data-perfume-image="<?= htmlspecialchars($perfume_image) ?>"
                                    data-perfume-price="<?= htmlspecialchars($perfume_price) ?>"
                                    data-next-question="<?= $ans['next_question_id'] ?? ($current_question['next_question_id'] ?? '') ?>">
                                    <?= htmlspecialchars($ans['text'] ?? '') ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </form>

                    <div id="result" class="mt-4">
                        <h4>Your perfect match:</h4>
                        <div class="perfume-result">
                            <img id="result-image" src="" alt="Perfume Image" class="img-fluid rounded mb-3" style="max-height: 200px; width: auto;" onerror="this.onerror=null;this.src='images/default_image.jpeg';">
                            <div class="perfume-info">
                                <div id="result-perfume" class="perfume-name h4"></div>
                                <div id="result-price" class="perfume-price h5 mb-3"></div>
                                <p class="text-muted">Discover this fragrance in our collection!</p>
                            </div>
                        </div>
                        <a href="#" id="result-link" class="btn start-btn">
                            View This Fragrance
                        </a>
                        <div class="divider"></div>
                        <button id="restart-btn" class="btn start-btn" style="background-color: #6c757d;">Take Quiz Again</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Show quiz content when start button is clicked
        $('#start-quiz-btn').click(function() {
            $(this).hide();
            $('.quiz-header p').hide();
            $('#quiz-content').fadeIn();
            $('html, body').animate({
                scrollTop: $('#quiz-content').offset().top
            }, 500);
        });

        // Handle answer selection
        $(document).on('click', '.answer-btn', function(e) {
            const perfume = $(this).data('perfume');
            const perfume_id = $(this).data('perfume-id');
            const perfume_image = $(this).data('perfume-image');
            const perfume_price = $(this).data('perfume-price');
            const nextQuestionId = $(this).data('next-question');
            
            if (perfume) {
                // Show result if perfume is specified
                showResult(perfume, perfume_id, perfume_image, perfume_price);
            } else if (nextQuestionId) {
                // Load next question via AJAX
                $.ajax({
                    url: 'quiz.php',
                    method: 'GET',
                    data: { 
                        get_question: 1,
                        id: nextQuestionId 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            alert(response.error);
                        } else {
                            // Check if this question has any perfume answers
                            const answers = JSON.parse(response.answers);
                            const hasPerfume = Object.values(answers).some(a => a.perfume);
                            
                            if (!hasPerfume && !response.next_question_id) {
                                // This is the last question with no perfume specified
                                showCompletion();
                            } else {
                                updateQuizInterface(response);
                            }
                        }
                    },
                    error: function() {
                        alert('Error loading next question. Please try again.');
                    }
                });
            } else {
                // This is the last question - check if we have a perfume recommendation
                const answers = JSON.parse('<?= addslashes($current_question['answers']) ?>');
                const perfumeAnswer = Object.values(answers).find(a => a.perfume);
                
                if (perfumeAnswer) {
                    showResult(perfumeAnswer.perfume, perfumeAnswer.perfume_id || '', perfumeAnswer.perfume_image || '', perfumeAnswer.perfume_price || '');
                } else {
                    showCompletion();
                }
            }
        });

        // Function to show results
        function showResult(perfume, perfume_id, perfume_image, perfume_price) {
            $('#result-perfume').text(perfume);
            
            // Set image - construct proper path
            let imageSrc = 'images/default_image.jpeg'; // Default fallback
            if (perfume_image) {
                // Remove any duplicate 'images/' prefix
                perfume_image = perfume_image.replace(/^images\//, '');
                // Construct new path
                imageSrc = 'images/' + perfume_image;
            }
            $('#result-image').attr('src', imageSrc);
            
            // Set price if available
            if (perfume_price) {
                $('#result-price').text('RM ' + parseFloat(perfume_price).toFixed(2));
            } else {
                $('#result-price').text('');
            }
            
            // Set link
            if (perfume_id) {
                $('#result-link').attr('href', 'perfume_details.php?perfume_id=' + perfume_id);
            } else {
                $('#result-link').attr('href', 'shop.php?search=' + encodeURIComponent(perfume));
            }
            
            $('#result').show();
            $('#quiz-form').hide();
            $('html, body').animate({
                scrollTop: $('#result').offset().top
            }, 500);
        }

        // Function to show completion when no result
        function showCompletion() {
            $('#result-perfume').html('<span class="text-danger">No specific fragrance match found</span>');
            $('#result-image').attr('src', 'images/default_image.jpeg');
            $('#result-price').text('');
            $('#result-link').text('Browse All Fragrances').attr('href', 'shop.php');
            $('#result').show();
            $('#quiz-form').hide();
            $('html, body').animate({
                scrollTop: $('#result').offset().top
            }, 500);
        }

        // Restart quiz
        $('#restart-btn').click(function() {
            location.reload();
        });

        function updateQuizInterface(question) {
            let answersHtml = '';
            const answers = JSON.parse(question.answers);
            
            for (const [key, answer] of Object.entries(answers)) {
                answersHtml += `
                    <button type="button" class="btn answer-btn"
                        data-perfume="${answer.perfume || ''}"
                        data-perfume-id="${answer.perfume_id || ''}"
                        data-perfume-image="${answer.perfume_image || ''}"
                        data-perfume-price="${answer.perfume_price || ''}"
                        data-next-question="${answer.next_question_id || question.next_question_id || ''}">
                        ${answer.text}
                    </button>`;
            }
            
            $('#quiz-form').html(`
                <input type="hidden" id="question-id" value="${question.id}">
                <div class="mb-4">
                    <h4 class="question-text">${question.question}</h4>
                </div>
                <div class="answer-buttons">
                    ${answersHtml}
                </div>
            `);
        }
    });
    </script>
</body>
</html>
