<?php
session_start();
$quizzes = $data['quizzes'] ?? [];
$user = $data['user'] ?? null;
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-question-circle',
  'title' => 'Kuis Saya',
]);
?>

<!-- Quizzes List -->
<div class="row pm-section">
    <div class="col-12">
        <h4><i class="fas fa-list me-2"></i>Daftar Kuis 
            <small class="text-muted">(<?php echo count($quizzes); ?> kuis)</small>
        </h4>
    </div>
    
    <?php if (!empty($quizzes)): ?>
        <?php foreach ($quizzes as $quiz): ?>
            <?php $renderer->includePartial('components/partials/quiz_card', ['quiz' => $quiz]); ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <?php $renderer->includePartial('components/partials/empty_state', [
                'icon' => 'fas fa-question-circle',
                'title' => 'Tidak ada kuis',
                'subtitle' => 'Kuis baru akan muncul di sini saat guru membuatnya.',
            ]); ?>
        </div>
    <?php endif; ?>
</div>
