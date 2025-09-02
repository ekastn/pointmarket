<?php
// Data for this view will be passed from the QuestionnaireController
$user = $user ?? ['name' => 'Guest'];
$questionnaires = $questionnaires ?? [];
$history = $history ?? [];
$stats = $stats ?? [];
$pendingEvaluations = $pendingEvaluations ?? [];
$varkResult = $varkResult ?? null;
$messages = $messages ?? [];
?>

<?php 
$right = '<div class="btn-group">'
       . '<a href="/weekly-evaluations" class="btn btn-sm btn-outline-primary"><i class="fas fa-calendar-check"></i> Evaluasi Mingguan</a>'
       . '<a href="/ai-explanation" class="btn btn-sm btn-outline-info ms-2"><i class="fas fa-robot"></i> Penjelasan AI</a>'
       . '</div>';
$renderer->includePartial('components/partials/page_title', [
    'icon' => 'fas fa-clipboard-list',
    'title' => 'Kuesioner',
    'right' => $right,
]);
?>

<?php $renderer->includePartial('siswa/partials/available_questionnaires', ['questionnaires' => $questionnaires]); ?>

<!-- Recent History -->
<div class="row pm-section">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Kuesioner Terakhir</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($history)): ?>
                    <div class="history-timeline">
                        <?php foreach ($history as $item): ?>
                        <div class="history-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-<?php echo $item['questionnaire_type'] === 'MSLQ' ? 'brain' : 'heart'; ?> me-2"></i>
                                        <?php echo htmlspecialchars($item['questionnaire_name']); ?>
                                    </h6>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($item['questionnaire_description'] ?? 'Belum ada deskripsi.'); ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo htmlspecialchars(date('d M Y', strtotime($item['completed_at']))); ?>
                                        <?php if (isset($item['week_number']) && isset($item['year'])): ?>
                                            | Minggu <?php echo htmlspecialchars($item['week_number']); ?>/<?php echo htmlspecialchars($item['year']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <?php 
                                    $score = $item['total_score'];
                                    $scoreClass = $score >= 5.5 ? 'score-high' : ($score >= 4 ? 'score-medium' : 'score-low');
                                    ?>
                                    <span class="score-badge <?php echo $scoreClass; ?>">
                                        Skor: <?php echo htmlspecialchars(number_format($score ?? 0, 2)); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php $renderer->includePartial('components/partials/empty_state', [
                        'icon' => 'fas fa-clipboard-list',
                        'title' => 'Belum ada riwayat kuesioner',
                        'subtitle' => 'Selesaikan kuesioner pertama kamu untuk melihat progress.',
                        'cta_path' => '/weekly-evaluations',
                        'cta_label' => 'Mulai Evaluasi Mingguan',
                    ]); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
