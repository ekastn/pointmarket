<?php
// Data for the view will be passed from the DashboardController
// $userProfile, $studentStats, $questionnaireStats, $latestVARKResult, $messages, $aiMetrics, $assignmentStats, $weeklyProgress, $recentActivities

// Ensure variables are defined to prevent PHP notices if not passed
$studentStats = $studentStats ?? [];
$learningStyle = $studentStats['learning_style'] ?? null;

$statsItems = [
    ['title' => 'Poin', 'value' => $studentStats['total_points'], 'icon' => 'fas fa-coins'],
    ['title' => 'Tugas Selesai', 'value' => $studentStats['completed_assignments'], 'icon' => 'fas fa-tasks'],
    ['title' => 'Skor MSLQ', 'value' => $studentStats['mslq_score'], 'icon' => 'fas fa-brain'],
    ['title' => 'Skor AMS', 'value' => $studentStats['ams_score'], 'icon' => 'fas fa-heart'],
];

require_once __DIR__.'/../../Helpers/VARKHelpers.php';
require_once __DIR__.'/../../Helpers/DateHelpers.php';

?>


<?php 
$right = '<div class="btn-toolbar"><div class="btn-group">'
       . '<button type="button" class="btn btn-sm btn-outline-secondary">'
       . '<i class="fas fa-download me-1"></i>Ekspor'
       . '</button></div></div>';
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-tachometer-alt',
  'title' => 'Dashboard',
  'right' => $right,
]);
?>

<!-- AI Features POC -->
<?php $renderer->includePartial('components/partials/ai_features_poc'); ?>

<!-- Student Stats -->
<div class="row pm-section">
    <?php foreach ($statsItems as $item) { ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <?php $renderer->includePartial('components/partials/card_stats', $item); ?>
        </div>
    <?php } ?>
</div>

<!-- Quick Actions -->
<div class="row pm-section">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="/assignments" class="btn btn-primary w-100">
                            <i class="fas fa-tasks me-2"></i>
                            Lihat Tugas
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/quiz" class="btn btn-success w-100">
                            <i class="fas fa-question-circle me-2"></i>
                            Ikuti Kuis
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/questionnaires" class="btn btn-info w-100">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Kuesioner
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/materials" class="btn btn-warning w-100">
                            <i class="fas fa-book me-2"></i>
                            Materi Belajar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- VARK Learning Style Card -->
<div class="row pm-section">
    <div class="col-12">
        <div class="card border-left-primary">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-brain me-2"></i>
                    Profil Gaya Belajar Kamu
                </h6>
            </div>
            <div class="card-body">
                <?php if ($learningStyle && $learningStyle && $learningStyle['label'] !== null && $learningStyle['label'] !== "") { ?>
                    <?php
                    $dominantStyle = $learningStyle['label'];
                    $learningPreference = $learningStyle['type'];
                    $learningTips = \App\Helpers\getVARKLearningTips($dominantStyle);
                    $preferenceScores = $learningStyle['scores'];
                    ?>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h5 class="text-primary mb-2">
                                        <i class="<?php echo htmlspecialchars($learningTips['icon']); ?> me-2"></i>
                                        <?php echo htmlspecialchars($dominantStyle); ?>
                                    </h5>
                                    <p class="text-muted mb-3">
                                        <?php echo htmlspecialchars($learningTips['description']); ?>
                                    </p>
                                    
                                    <div class="vark-scores">
                                        <small class="text-muted">Skor VARK:</small>
                                        <div class="row mt-2">
                                            <div class="col">
                                                <span class="vark-badge visual">Visual: <?php echo htmlspecialchars($preferenceScores['visual'] ?? 'N/A'); ?></span>
                                                <span class="vark-badge auditory ms-2">Auditory: <?php echo htmlspecialchars($preferenceScores['auditory'] ?? 'N/A'); ?></span>
                                                <span class="vark-badge reading">Reading: <?php echo htmlspecialchars($preferenceScores['reading'] ?? 'N/A'); ?></span>
                                                <span class="vark-badge kinesthetic ms-2">Kinesthetic: <?php echo htmlspecialchars($preferenceScores['kinesthetic'] ?? 'N/A'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <h6 class="text-success">Tips Belajar buat Kamu:</h6>
                                    <ul class="small">
                                        <?php foreach (array_slice($learningTips['study_tips'], 0, 3) as $tip) { ?>
                                            <li><?php echo htmlspecialchars($tip); ?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="<?php echo htmlspecialchars($learningTips['icon']); ?> fa-4x text-primary opacity-50 mb-3"></i>
                            <br>
                            <a href="/vark-assessment" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-sync-alt me-1"></i>
                                Ulangi Assessment
                            </a>
                        </div>
                    </div>
                <?php } else { ?>
                    <?php $renderer->includePartial('components/partials/empty_state', [
                        'icon' => 'fas fa-brain',
                        'title' => 'Gaya Belajar Belum Dinilai',
                        'subtitle' => 'Ikuti VARK assessment untuk tahu preferensi belajar kamu dan dapat rekomendasi yang lebih personal.',
                        'cta_path' => '/vark-assessment',
                        'cta_label' => 'Mulai VARK Assessment',
                    ]); ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Evaluations -->
<div class="row pm-section">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    Evaluasi Mingguan
                </h5>
                <a href="/weekly-evaluations" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>
                    Lihat Semua
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    // Organize evaluations by questionnaire title for easy access
                    $currentWeekEvaluations = [];
                    foreach ($weekly_evaluations as $eval) {
                        $currentWeekEvaluations[$eval['questionnaire_type']] = $eval;
                    }

                    $evaluationTypes = [
                        'MSLQ' => ['icon' => 'fas fa-brain', 'color' => 'success'],
                        'AMS' => ['icon' => 'fas fa-heart', 'color' => 'warning'],
                    ];

                    foreach ($evaluationTypes as $type => $meta) : 
                        $evaluation = $currentWeekEvaluations[$type] ?? null;
                        $title = $evaluation['questionnaire_title'] ?? 'not_assigned'; // Default status if not found
                        $status = $evaluation['status'] ?? 'not_assigned'; // Default status if not found
                        $score = $evaluation['score'] ?? null;
                        $completedAt = $evaluation['completed_at'] ?? null;
                        $description = $evaluation['questionnaire_description'] ?? null;
                    ?>
                        <div class="col-md-6 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-<?= $meta['color'] ?> text-white">
                                    <h6 class="mb-0">
                                        <i class="<?= $meta['icon'] ?> me-2"></i>
                                        <?= htmlspecialchars($title) ?>
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <?php if ($status === 'pending') : ?>
                                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Pending</h6>
                                        <?php if ($description) : ?><p class="small text-muted"><?= htmlspecialchars($description) ?></p><?php endif; ?>
                                        <p class="small text-muted">Due: <?= htmlspecialchars(date('D, M d', strtotime($evaluation['due_date']))) ?></p>
                                        <a href="/questionnaires/<?= htmlspecialchars($evaluation['questionnaire_id']) ?>?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-play me-1"></i>Start Evaluation
                                        </a>
                                    <?php elseif ($status === 'completed') : ?>
                                        <div class="score-badge badge bg-<?= $meta['color'] ?> mb-3">
                                            <?php echo htmlspecialchars(number_format($score, 1)); ?>/7.0
                                        </div>
                                        <h6 class="text-<?= $meta['color'] ?>">Completed</h6>
                                        <?php if ($description) : ?><p class="small text-muted"><?= htmlspecialchars($description) ?></p><?php endif; ?>
                                        <p class="small text-muted">On: <?= htmlspecialchars(date('D, M d', strtotime($completedAt))) ?></p>
                                        <a href="/questionnaires/results?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-sm btn-outline-<?= $meta['color'] ?>">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                    <?php elseif ($status === 'overdue') : ?>
                                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                        <h6 class="text-danger">Overdue</h6>
                                        <?php if ($description) : ?><p class="small text-muted"><?= htmlspecialchars($description) ?></p><?php endif; ?>
                                        <p class="small text-muted">Due: <?= htmlspecialchars(date('D, M d', strtotime($evaluation['due_date']))) ?></p>
                                        <?php if ($evaluation['completed_at']) : // Check if it was completed but marked overdue later ?>
                                            <a href="/questionnaires/results?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-eye me-1"></i>View Details (Overdue)
                                            </a>
                                        <?php else : ?>
                                            <a href="/questionnaires/<?= htmlspecialchars($evaluation['questionnaire_id']) ?>?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-sm btn-danger">
                                                <i class="fas fa-play me-1"></i>Start Evaluation (Overdue)
                                            </a>
                                        <?php endif; ?>
                                    <?php else : // status === 'not_assigned' or other unexpected status ?>
                                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Not Available This Week</h6>
                                        <?php if ($description) : ?><p class="small text-muted"><?= htmlspecialchars($description) ?></p><?php endif; ?>
                                        <p class="small text-muted">Check back later or view all evaluations.</p>
                                        <a href="/weekly-evaluations" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye me-1"></i>View All
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($recommendations) || !empty($missingAssessments)) : ?>
<div class="row pm-section">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Rekomendasi
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recommendations)) : ?>
                    <?php
                    // Normalize structure: backend returns an object with 'actions'
                    $hasStructured = is_array($recommendations) && isset($recommendations['actions']) && is_array($recommendations['actions']);
                    $trainingPending = !empty($recommendations['training_pending']);
                    $emptyReason = $recommendations['empty_reason'] ?? '';
                    $totalItems = $recommendations['total_items'] ?? 0;
                    ?>
                    <?php if ($hasStructured): ?>
                        <?php $actions = $recommendations['actions']; ?>
                        <?php if ($trainingPending || ($emptyReason && $totalItems == 0)): ?>
                            <div class="p-3 text-center border rounded mb-3 bg-light">
                                <?php if ($trainingPending): ?>
                                    <div class="mb-2">
                                        <strong>Sedang mempersiapkan rekomendasi...</strong>
                                    </div>
                                    <p class="small text-muted mb-2">Coba refresh dalam beberapa detik.</p>
                                <?php endif; ?>
                                <a href="" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate-right me-1"></i>Refresh</a>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($actions) && !$trainingPending && $totalItems == 0 && !$emptyReason): ?>
                            <div class="text-muted small">Belum ada rekomendasi tersedia.</div>
                        <?php else: ?>
                            <?php if (!$trainingPending && $totalItems > 0): ?>
                            <div class="row" id="pmRecoSection">
                                <?php
                                $qValues = array_map(function($a){return isset($a['q_value']) ? (float)$a['q_value'] : 0;}, $actions);
                                $maxQ = max($qValues ?: [0]);
                                // Color palette similar to weekly evaluations (rotating)
                                $actionColorPalette = ['primary','success','warning','info','secondary','danger'];
                                ?>
                                <?php foreach ($actions as $aIndex => $action): ?>
                                    <?php
                                    $q = isset($action['q_value']) ? (float)$action['q_value'] : null;
                                    $itemCount = isset($action['items']) && is_array($action['items']) ? count($action['items']) : 0;
                                    $items = $action['items'] ?? [];
                                    $preview = array_slice($items, 0, 3);
                                    $color = $actionColorPalette[$aIndex % count($actionColorPalette)];
                                    // Colors needing dark text for readability
                                    $needsDark = in_array($color, ['warning','info','light']);
                                    $textClass = $needsDark ? 'text-dark' : 'text-white';
                                    ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card h-100 shadow-sm border-0" data-reco-action="<?php echo $aIndex; ?>">
                                            <div class="card-header py-2 d-flex justify-content-between align-items-center bg-<?php echo htmlspecialchars($color); ?> <?php echo $textClass; ?>">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-bolt"></i>
                                                    <strong class="small mb-0"><?php echo htmlspecialchars($action['action_name'] ?? ('Aksi '.($aIndex+1))); ?></strong>
                                                </div>
                                                <span class="badge <?php echo $needsDark ? 'bg-light text-dark' : 'bg-white text-'.$color; ?> border"><i class="fas fa-layer-group me-1"></i><?php echo $itemCount; ?></span>
                                            </div>
                                            <div class="card-body p-2">
                                                <?php if ($q !== null): ?>
                                                    <div class="small text-muted mb-1">Q: <?php echo htmlspecialchars(number_format($q,3)); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($items)): ?>
                                                    <ul class="list-unstyled mb-2 small" data-preview-list="<?php echo $aIndex; ?>">
                                                        <?php foreach ($preview as $it): ?>
                                                            <li class="mb-1 d-flex align-items-start">
                                                                <i class="fas fa-chevron-right text-muted me-2" style="font-size:.6rem; margin-top:.25rem;"></i>
                                                                <span class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($it['title'] ?? 'Item'); ?></span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                        <?php if ($itemCount > count($preview)): ?>
                                                            <li class="text-muted fst-italic">+<?php echo $itemCount - count($preview); ?> lainnya</li>
                                                        <?php endif; ?>
                                                    </ul>
                                                    <button type="button" class="btn btn-sm btn-outline-primary w-100" data-toggle-items="<?php echo $aIndex; ?>">Detail</button>
                                                    <div class="mt-2 d-none" data-full-list="<?php echo $aIndex; ?>">
                                                        <ul class="list-group list-group-flush small">
                                                            <?php $itemCounter=1; foreach ($items as $it): ?>
                                                                <?php
                                                                $diffRaw = isset($it['difficulty_level']) ? trim((string)$it['difficulty_level']) : '';
                                                                ?>
                                                                <li class="list-group-item px-2 py-2">
                                                                    <div class="d-flex">
                                                                        <div class="me-2 text-primary fw-bold" style="width:1.5rem;">#<?php echo $itemCounter++; ?></div>
                                                                        <div class="flex-grow-1">
                                                                            <div class="fw-semibold small mb-1"><?php echo htmlspecialchars($it['title'] ?? 'Item'); ?></div>
                                                                            <?php if (!empty($it['description'])): ?>
                                                                                <div class="text-muted small mb-1"><?php echo htmlspecialchars($it['description']); ?></div>
                                                                            <?php endif; ?>
                                                                            <div class="small text-muted d-flex flex-wrap gap-2">
                                                                                <?php if (!empty($it['category'])): ?><span><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($it['category']); ?></span><?php endif; ?>
                                                                                <?php if ($diffRaw !== ''): ?><span><i class="fas fa-signal me-1"></i><?php echo htmlspecialchars($diffRaw); ?></span><?php endif; ?>
                                                                                <?php if (!empty($it['estimated_duration'])): ?><span><i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($it['estimated_duration']); ?></span><?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-muted small">Tidak ada item.</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="small text-muted mt-2 d-flex flex-wrap gap-3 align-items-center">
                                <span>Sumber: <strong><?php echo htmlspecialchars($recommendations['source'] ?? 'unknown'); ?></strong></span>
                                <span>Aksi: <strong><?php echo htmlspecialchars($recommendations['total_actions'] ?? 0); ?></strong></span>
                                <span>Item: <strong><?php echo htmlspecialchars($recommendations['total_items'] ?? 0); ?></strong></span>
                                <?php if ($trainingPending): ?><span class="badge bg-warning text-dark">Training Pending</span><?php endif; ?>
                                <?php if ($emptyReason === 'trained_but_empty'): ?><span class="badge bg-secondary">Data Minim</span><?php endif; ?>
                                <?php if ($emptyReason === 'untrained' && !$trainingPending): ?><span class="badge bg-info text-dark">Untrained</span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach (array_slice((array)$recommendations, 0, 6) as $idx => $rec): ?>
                                <?php if (!is_array($rec)) continue; ?>
                                <li class="list-group-item">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3 mt-1 text-primary fw-bold"><?php echo (is_int($idx) ? $idx + 1 : 1); ?>.</div>
                                        <div class="flex-grow-1">
                                            <strong><?php echo htmlspecialchars($rec['title'] ?? $rec['name'] ?? 'Item'); ?></strong>
                                            <?php if (!empty($rec['description'])): ?>
                                                <div class="small text-muted mt-1"><?php echo htmlspecialchars($rec['description']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php elseif (!empty($missingAssessments)) : ?>
                    <h6 class="mb-3">
                        Lengkapi assessment (VARK, MSLQ, AMS) supaya kami bisa menampilkan rekomendasi yang dipersonalisasi untuk kamu.
                    </h6>
                    <ul class="small mb-3">
                        <?php foreach ($missingAssessments as $miss): ?>
                            <li><strong><?php echo htmlspecialchars($miss); ?></strong> belum ada / belum lengkap.</li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="/questionnaires" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-clipboard-list me-1"></i>Lengkapi Sekarang
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $renderer->includePartial('components/partials/ai_simulations_section'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
        // (Removed q-bar animation logic)

            // Legacy (pm-reco-expand-toggle) expand (if still present)
        document.querySelectorAll('.pm-reco-expand-toggle[data-toggle-action]').forEach(function(t){
            t.addEventListener('click', function(){
                const idx = this.getAttribute('data-toggle-action');
                const host = document.querySelector('.pm-reco-action[data-action-index="'+idx+'"]');
                const list = document.querySelector('.pm-reco-items[data-items-list="'+idx+'"]');
                if (!host || !list) return;
                const expanded = host.classList.toggle('expanded');
                list.style.display = expanded ? 'flex' : 'none';
                this.innerHTML = expanded ? '<i class="fas fa-chevron-up"></i> Tutup' : '<i class="fas fa-chevron-down"></i> Detail';
                if (expanded) {
                    list.style.animation = 'pmFadeUp .35s ease';
                }
            });
        });

            // New card grid Detail buttons (data-toggle-items)
            document.querySelectorAll('button[data-toggle-items]').forEach(function(btn){
                btn.addEventListener('click', function(){
                    const idx = this.getAttribute('data-toggle-items');
                    const card = document.querySelector('[data-reco-action="'+idx+'"]');
                    if (!card) return;
                    const preview = card.querySelector('[data-preview-list="'+idx+'"]');
                    const full = card.querySelector('[data-full-list="'+idx+'"]');
                    if (!full) return;
                    const expanded = !full.classList.contains('d-none');
                    if (expanded) {
                        // collapse
                        full.classList.add('d-none');
                        if (preview) preview.classList.remove('d-none');
                        this.innerHTML = 'Detail';
                    } else {
                        full.classList.remove('d-none');
                        if (preview) preview.classList.add('d-none');
                        this.innerHTML = 'Tutup';
                    }
                });
            });

        // Auto-collapse if section height grows too large after expansions
        const clamp = document.getElementById('pmRecoSection');
        if (clamp) {
            const enforceClamp = () => {
                if (clamp.scrollHeight > 1200) { // safety threshold
                    // collapse all except first
                    const actions = clamp.querySelectorAll('.pm-reco-action');
                    actions.forEach((a,i)=>{ if(i>0) { a.classList.remove('expanded'); const l=a.querySelector('.pm-reco-items'); if(l) l.style.display='none'; const toggle=a.querySelector('.pm-reco-expand-toggle'); if(toggle) toggle.innerHTML='<i class="fas fa-chevron-down"></i> Detail'; }});
                }
            };
            setTimeout(enforceClamp, 600);
        }
});
</script>
