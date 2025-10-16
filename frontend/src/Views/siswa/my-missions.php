<div class="container-fluid">
    <?php $renderer->includePartial('components/partials/page_title', [
        'icon' => 'fas fa-trophy',
        'title' => 'Misi Saya',
    ]); ?>

    <?php if (empty($missions)): ?>
        <?php $renderer->includePartial('components/partials/empty_state', [
            'icon' => 'fas fa-trophy',
            'title' => 'Belum ada misi aktif',
            'subtitle' => 'Kamu belum punya misi yang aktif.',
        ]); ?>
    <?php else: ?>
        <div class="row pm-section">
            <?php foreach ($missions as $mission): ?>
                <?php
                    $title = $mission['mission_title'] ?? '';
                    if (is_array($title)) { $title = json_encode($title); }
                    $desc = $mission['mission_description'] ?? 'Belum ada deskripsi.';
                    if (is_array($desc)) { $desc = json_encode($desc); }
                    $status = $mission['status'] ?? '';
                    $startedAt = $mission['started_at'] ?? '';
                    if ($startedAt && !is_string($startedAt)) { $startedAt = ''; }
                    $completedAt = $mission['completed_at'] ?? null;
                    if ($completedAt && !is_string($completedAt)) { $completedAt = null; }
                    $rewardPoints = $mission['mission_reward_points'] ?? null;
                    // progress can be numeric or JSON; normalize to percent if possible
                    $progressVal = 0;
                    if (isset($mission['progress'])) {
                        if (is_numeric($mission['progress'])) {
                            $progressVal = (int)$mission['progress'];
                        } elseif (is_string($mission['progress'])) {
                            $progressVal = is_numeric($mission['progress']) ? (int)$mission['progress'] : 0;
                        } elseif (is_array($mission['progress'])) {
                            if (isset($mission['progress']['percent']) && is_numeric($mission['progress']['percent'])) {
                                $progressVal = (int)$mission['progress']['percent'];
                            } elseif (isset($mission['progress']['value']) && is_numeric($mission['progress']['value'])) {
                                $progressVal = (int)$mission['progress']['value'];
                            }
                        }
                    }
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge <?= ($status === 'completed') ? 'bg-success' : 'bg-secondary' ?>"><?php echo htmlspecialchars($status ? ucfirst($status) : 'Tersedia'); ?></span>
                                <small class="text-muted">Poin: <?php echo (int)($rewardPoints ?? 0); ?></small>
                            </div>
                            <h5 class="card-title mb-1">
                                <a class="text-decoration-none" href="/missions/<?php echo (int)($mission['mission_id'] ?? 0); ?>"><?php echo htmlspecialchars($title); ?></a>
                            </h5>
                            <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars($desc); ?></p>
                            <div class="small text-muted mb-2">
                                <?php if (!empty($startedAt)): ?>
                                    <div><i class="fas fa-play-circle me-1"></i>Mulai: <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($startedAt))); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($completedAt)): ?>
                                    <div><i class="fas fa-flag-checkered me-1"></i>Selesai: <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($completedAt))); ?></div>
                                <?php endif; ?>
                                <div><i class="fas fa-chart-line me-1"></i>Progress: <?php echo (int)$progressVal; ?>%</div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-primary" href="/missions/<?php echo (int)($mission['mission_id'] ?? 0); ?>">
                                    <i class="fas fa-eye me-1"></i> Detail
                                </a>
                                <?php if (($status ?? '') !== 'completed'): ?>
                                <button class="btn btn-sm btn-success start-mission-btn" data-mission-id="<?= (int)($mission['mission_id'] ?? 0) ?>" data-user-mission-id="<?= (int)($mission['id'] ?? 0) ?>">
                                    <i class="fas fa-play me-1"></i> Mulai/Lanjutkan
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Status updates are managed by the system; manual updates are disabled for students. -->
