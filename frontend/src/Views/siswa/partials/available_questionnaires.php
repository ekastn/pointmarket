<?php
/**
 * @var array $questionnaires
 */
?>
<!-- Kuesioner Tersedia -->
<div class="row pm-section">
  <div class="col-12">
    <div class="card pm-card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Kuesioner Tersedia</h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">Practice questionnaire untuk memahami format & konten. Untuk evaluasi mingguan resmi, gunakan <a href="/weekly-evaluations">Evaluasi Mingguan</a>.</p>
        <div class="row g-3">
          <?php if (!empty($questionnaires)):
            foreach ($questionnaires as $questionnaire):
          ?>
            <div class="col-md-6">
              <div class="card questionnaire-card <?php echo htmlspecialchars($questionnaire['type']); ?> h-100">
                <div class="card-body">
                  <h5 class="card-title">
                    <i class="fas fa-<?php echo $questionnaire['type'] === 'MSLQ' ? 'brain' : ($questionnaire['type'] === 'AMS' ? 'heart' : 'graduation-cap'); ?> me-2"></i>
                    <?php echo htmlspecialchars($questionnaire['name']); ?>
                  </h5>
                  <p class="card-text"><?php echo htmlspecialchars($questionnaire['description']); ?></p>
                  <div class="mb-3">
                    <small class="text-muted">
                      <i class="fas fa-question-circle me-1"></i>
                      <?php echo htmlspecialchars($questionnaire['total_questions']); ?> Pertanyaan
                    </small>
                    <br>
                    <small class="text-muted">
                      <i class="fas fa-clock me-1"></i>
                      Estimasi waktu: <?php echo htmlspecialchars(ceil($questionnaire['total_questions'] * 0.5)); ?> menit
                    </small>
                  </div>
                  <div class="d-flex gap-2">
                    <a href="/questionnaires/<?php echo htmlspecialchars($questionnaire['id']); ?>" class="btn btn-primary btn-sm">
                      <i class="fas fa-play me-1"></i> Practice
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="col-12">
              <?php $renderer->includePartial('components/partials/empty_state', [
                'icon' => 'fas fa-list',
                'title' => 'Belum ada kuesioner saat ini',
                'subtitle' => 'Cek lagi nanti atau hubungi support.',
              ]); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
