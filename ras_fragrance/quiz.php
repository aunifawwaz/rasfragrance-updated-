<?php
include 'db_connection.php';
$current_question = $pdo->query("SELECT * FROM perfume_quiz WHERE is_active = 1 ORDER BY sort_order LIMIT 1")->fetch();
?>

<div class="container mt-5">
  <div class="card">
    <div class="card-body">
      <h3 class="card-title">Perfume Personality Quiz</h3>
      
      <form id="quiz-form">
        <input type="hidden" id="question-id" value="<?= $current_question['id'] ?>">
        
        <div class="mb-4">
          <h4><?= htmlspecialchars($current_question['question']) ?></h4>
        </div>

        <div class="answer-buttons">
          <?php foreach (json_decode($current_question['answers'], true) as $key => $ans): ?>
            <button 
              type="button" 
              class="btn btn-outline-primary mb-2 answer-btn" 
              data-perfume="<?= htmlspecialchars($ans['perfume'] ?? '') ?>"
            >
              <?= htmlspecialchars($ans['text']) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </form>

      <div id="result" class="mt-4" style="display:none;">
        <h4>Your perfect match: <span id="result-perfume"></span></h4>
        <p class="text-muted">Discover this fragrance in our collection!</p>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.answer-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const perfume = this.dataset.perfume;
    
    if (perfume) {
      // Show result if perfume is specified
      document.getElementById('result-perfume').textContent = perfume;
      document.getElementById('result').style.display = 'block';
    } else {
      // Load next question (you would implement AJAX here)
      alert('Would load next question in full implementation');
    }
  });
});
</script>