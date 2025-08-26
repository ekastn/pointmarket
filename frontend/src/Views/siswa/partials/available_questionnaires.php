<?php
/**
 * @var array $questionnaires
 */
?>
<!-- Kuesioner Tersedia -->
<div class="row pm-section">
    <div class="col-12">
        <h4><i class="fas fa-list me-2"></i>Kuesioner Tersedia</h4>
        <p class="text-muted">Practice questionnaire untuk memahami format & konten. Untuk evaluasi mingguan resmi, gunakan <a href="/weekly-evaluations">Evaluasi Mingguan</a>.</p>
    </div>
    <?php if (!empty($questionnaires)):
        foreach ($questionnaires as $questionnaire):
    ?>
        <div class="col-md-6 mb-3">
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
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <?php $renderer->includePartial('components/partials/empty_state', [
                'icon' => 'fas fa-list',
                'title' => 'Belum ada kuesioner saat ini',
                'subtitle' => 'Cek lagi nanti atau hubungi support.',
            ]); ?>
        </div>
    <?php endif; ?>
</div>
