<?php
    $mission = $mission ?? [];
    $userMission = $userMission ?? null;

    $title = $mission['title'] ?? '';
    if (is_array($title)) { $title = json_encode($title); }
    $desc = $mission['description'] ?? null;
    if (is_array($desc)) { $desc = json_encode($desc); }
    $reward = $mission['reward_points'] ?? null;
    $meta = $mission['metadata'] ?? null;
    $status = $userMission['status'] ?? null;
    $startedAt = $userMission['started_at'] ?? null;
    $completedAt = $userMission['completed_at'] ?? null;
?>

<div class="container-fluid">
    <?php $renderer->includePartial('components/partials/page_title', [
        'icon' => 'fas fa-trophy',
        'title' => 'Detail Misi',
    ]); ?>

    <div class="row pm-section">
        <div class="col-12">
            <div class="card pm-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-flag-checkered me-2"></i><?= htmlspecialchars($title ?: 'Misi') ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">Deskripsi</div>
                        <div><?= htmlspecialchars($desc ?? 'Belum ada deskripsi.') ?></div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Poin Hadiah</div>
                            <div class="h6 mb-0"><?= $reward !== null ? (int)$reward : '-' ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Status</div>
                            <div class="h6 mb-0">
                                <?= $status ? htmlspecialchars(ucfirst($status)) : 'Belum dimulai' ?>
                                <?php if ($status === 'completed'): ?>
                                    <span class="badge bg-success ms-2">Selesai</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Waktu</div>
                            <div class="mb-0">
                                <?php if (!empty($startedAt)): ?>
                                    <div>Mulai: <?= htmlspecialchars(date('d M Y H:i', strtotime($startedAt))) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($completedAt)): ?>
                                    <div>Selesai: <?= htmlspecialchars(date('d M Y H:i', strtotime($completedAt))) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($meta)): ?>
                    <div class="mb-3">
                        <div class="text-muted small">Metadata</div>
                        <?php
                            // Normalize metadata to an associative array
                            $metaArr = $meta;
                            if (is_string($meta)) {
                                $decoded = json_decode($meta, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $metaArr = $decoded;
                                }
                            }

                            // Helper to stringify nested values
                            $metaValueToString = function ($value) {
                                if (is_scalar($value) || $value === null) {
                                    return (string)($value ?? '');
                                }
                                if (is_array($value)) {
                                    // If associative, render key:value pairs; else join values
                                    $isAssoc = array_keys($value) !== range(0, count($value) - 1);
                                    if ($isAssoc) {
                                        $parts = [];
                                        foreach ($value as $k => $v) {
                                            $parts[] = ucfirst(str_replace('_', ' ', (string)$k)) . ': ' . (is_scalar($v) ? (string)$v : '[...]');
                                        }
                                        return implode(', ', $parts);
                                    }
                                    // flat list
                                    $vals = array_map(function ($v) { return is_scalar($v) ? (string)$v : '[...]'; }, $value);
                                    return implode(', ', $vals);
                                }
                                return '';
                            };
                        ?>
                        <?php if (is_array($metaArr)): ?>
                        <dl class="row mb-0">
                            <?php foreach ($metaArr as $key => $val): ?>
                            <dt class="col-sm-4 col-md-3"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)$key))) ?></dt>
                            <dd class="col-sm-8 col-md-9"><?= htmlspecialchars($metaValueToString($val)) ?></dd>
                            <?php endforeach; ?>
                        </dl>
                        <?php else: ?>
                        <div class="mb-0" style="background: #f8fafc; padding: .75rem; border-radius: .5rem; border: 1px solid #e9ecef;">
                            <?= htmlspecialchars((string)$meta) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <a href="/my-missions" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
                        <?php if (($status ?? '') !== 'completed'): ?>
                        <button class="btn btn-primary btn-sm start-mission-btn" data-mission-id="<?= (int)($mission['id'] ?? 0) ?>" data-user-mission-id="<?= (int)($userMission['id'] ?? 0) ?>">
                            <i class="fas fa-play me-1"></i>Mulai/Lanjutkan
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
