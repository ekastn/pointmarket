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

<!-- Statistics Overview -->
<div class="row pm-section">
    <div class="col-12">
        <h4><i class="fas fa-chart-bar me-2"></i>Statistik Kamu</h4>
    </div>
    <?php if (!empty($stats)): ?>
        <?php foreach ($stats as $stat): ?>
        <div class="col-md-6 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-<?php echo $stat['type'] === 'MSLQ' ? 'brain' : ($stat['type'] === 'AMS' ? 'heart' : 'graduation-cap'); ?> me-2"></i>
                        <?php echo strtoupper($stat['type']); ?>
                    </h5>
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1"><strong>Selesai:</strong></p>
                            <h4 class="text-primary"><?php echo htmlspecialchars($stat['total_completed'] ?? 0); ?></h4>
                        </div>
                        <div class="col-6">
                            <?php if ($stat['type'] === 'vark'): ?>
                                <p class="mb-1"><strong>Gaya Belajar:</strong></p>
                                <?php if ($stat['total_completed'] > 0 && $varkResult): ?>
                                    <h6 class="text-success"><?php echo htmlspecialchars($varkResult['dominant_style']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($varkResult['learning_preference']); ?></small>
                                <?php else: ?>
                                    <h6 class="text-muted">Belum dinilai</h6>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="mb-1"><strong>Rata-rata Skor:</strong></p>
                                <?php if ($stat['average_score'] !== null): ?>
                                    <?php 
                                    $avg_score = $stat['average_score'];
                                    $scoreClass = $avg_score >= 5.5 ? 'score-high' : ($avg_score >= 4 ? 'score-medium' : 'score-low');
                                    ?>
                                    <h4 class="<?php echo $scoreClass; ?>"><?php echo htmlspecialchars(number_format($avg_score, 2)); ?></h4>
                                <?php else: ?>
                                    <h4 class="text-muted">-</h4>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($stat['last_completed']): ?>
                        <small class="text-muted">
                            Terakhir selesai: <?php echo htmlspecialchars(date('d M Y', strtotime($stat['last_completed']))); ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <?php $renderer->includePartial('components/partials/empty_state', [
                'icon' => 'fas fa-chart-bar',
                'title' => 'Belum ada statistik kuesioner',
                'subtitle' => 'Isi kuesioner dulu untuk lihat progress di sini.',
            ]); ?>
        </div>
    <?php endif; ?>
</div>

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
