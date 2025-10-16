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
                    // progress can be numeric or JSON; normalize to percent if possible
                    $progressVal = 0;
                    if (isset($mission['progress'])) {
                        if (is_numeric($mission['progress'])) {
                            $progressVal = (int)$mission['progress'];
                        } elseif (is_string($mission['progress'])) {
                            $progressVal = is_numeric($mission['progress']) ? (int)$mission['progress'] : 0;
                        } elseif (is_array($mission['progress'])) {
                            // try common keys
                            if (isset($mission['progress']['percent']) && is_numeric($mission['progress']['percent'])) {
                                $progressVal = (int)$mission['progress']['percent'];
                            } elseif (isset($mission['progress']['value']) && is_numeric($mission['progress']['value'])) {
                                $progressVal = (int)$mission['progress']['value'];
                            }
                        }
                    }
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($title) ?></h6>
                        </div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($desc) ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($status)) ?></p>
                            <?php if (!empty($startedAt)): ?>
                                <p><strong>Mulai:</strong> <?= htmlspecialchars(date('d M Y H:i', strtotime($startedAt))) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($completedAt)): ?>
                                <p><strong>Selesai:</strong> <?= htmlspecialchars(date('d M Y H:i', strtotime($completedAt))) ?></p>
                            <?php endif; ?>
                            <p><strong>Progress:</strong> <?= (int)$progressVal ?>%</p>

                            <div class="d-flex gap-2">
                                <a href="/missions/<?= (int)($mission['mission_id'] ?? 0) ?>" class="btn btn-outline-primary btn-sm">Detail</a>
                                <?php if (($status ?? '') !== 'completed'): ?>
                                <button class="btn btn-success btn-sm start-mission-btn" data-mission-id="<?= (int)($mission['mission_id'] ?? 0) ?>" data-user-mission-id="<?= (int)($mission['id'] ?? 0) ?>">Mulai/Lanjutkan</button>
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
