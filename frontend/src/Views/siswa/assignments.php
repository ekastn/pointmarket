<?php 
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-tasks',
  'title' => 'Tugas Saya',
]);
?>

<!-- Assignments List -->
<div class="row pm-section">
        <?php if (!empty($assignments)): ?>
                <?php foreach ($assignments as $a): ?>
            <div class="col-md-6 col-lg-4 mb-4">
              <div class="card h-100">
                <div class="card-body d-flex flex-column">
                                    <?php 
                                        $assignIdBadge = (int)($a['assignment_id'] ?? $a['id'] ?? 0);
                                        $studentStatus = $studentStatusMap[$assignIdBadge] ?? null; // do not show internal assignment status like 'published'
                                    ?>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <?php if (!empty($studentStatus)): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($studentStatus); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Tersedia</span>
                                        <?php endif; ?>
                                        <small class="text-muted">Poin: <?php echo htmlspecialchars($a['assignment_reward_points'] ?? $a['reward_points'] ?? 0); ?></small>
                                    </div>
                                    <h5 class="card-title mb-1">
                                        <a class="text-decoration-none" href="/assignments/<?php echo (int)($a['assignment_id'] ?? $a['id'] ?? 0); ?>">
                                            <?php echo htmlspecialchars($a['assignment_title'] ?? $a['title'] ?? ''); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars($a['assignment_description'] ?? $a['description'] ?? ''); ?></p>
                                    <?php $due = $a['assignment_due_date'] ?? ($a['due_date'] ?? null); ?>
                                    <div class="mb-2 small text-muted"><i class="fas fa-calendar me-1"></i>Jatuh tempo: <?php echo !empty($due) ? htmlspecialchars(date('Y-m-d', strtotime($due))) : '-'; ?></div>
                                    <div class="d-flex justify-content-end">
                                        <a class="btn btn-sm btn-primary" href="/assignments/<?php echo (int)($a['assignment_id'] ?? $a['id'] ?? 0); ?>">
                                            <i class="fas fa-eye me-1"></i> Lihat Detail
                                        </a>
                                    </div>
                </div>
              </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <?php $renderer->includePartial('components/partials/empty_state', [
                'icon' => 'fas fa-tasks',
                'title' => 'Tidak ada tugas',
                'subtitle' => 'Tugas baru akan muncul di sini saat guru membuatnya.',
            ]); ?>
        </div>
    <?php endif; ?>
</div>

<!-- No action buttons here. Actions live in the detail page. -->
