<?php
session_start();
$quizzes = $data['quizzes'] ?? [];
$user = $data['user'] ?? null;
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-question-circle',
  'title' => 'Kuis Saya',
]);
?>

<div class="row pm-section">
    <?php if (!empty($quizzes)): ?>
        <?php foreach ($quizzes as $quiz): ?>
            <div class="col-md-6 col-lg-4 mb-4">
              <div class="card h-100">
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-<?php echo ($quiz['status'] ?? 'draft')==='published'?'success':'secondary'; ?>"><?php echo htmlspecialchars($quiz['status'] ?? ''); ?></span>
                    <small class="text-muted">Poin: <?php echo htmlspecialchars($quiz['reward_points'] ?? 0); ?></small>
                  </div>
                  <h5 class="card-title mb-1"><?php echo htmlspecialchars($quiz['title'] ?? ''); ?></h5>
                  <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars($quiz['description'] ?? ''); ?></p>
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Durasi: <?php echo isset($quiz['duration_minutes'])? htmlspecialchars($quiz['duration_minutes']).' menit':'N/A'; ?></small>
                    <a href="/quiz/<?php echo (int)($quiz['id'] ?? 0); ?>" class="btn btn-sm btn-primary">Lihat</a>
                  </div>
                </div>
              </div>
            </div>
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
