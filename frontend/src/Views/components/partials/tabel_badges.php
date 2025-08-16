<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Title</th>
            <th>Description</th>
            <th>Criteria</th>
            <th>Repeatable</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($badges)) : ?>
            <tr>
                <td colspan="7" class="text-center">No badges found.</td>
            </tr>
        <?php else : ?>
            <?php $i = $start; ?>
            <?php foreach ($badges as $badge) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($badge['title']); ?></td>
                    <td><?= htmlspecialchars($badge['description'] ?? '-'); ?></td>
                    <td><pre><?= htmlspecialchars(json_encode($badge['criteria'], JSON_PRETTY_PRINT)); ?></pre></td>
                    <td><?= $badge['repeatable'] ? 'Yes' : 'No'; ?></td>
                    <td><?= date('d-m-Y H:i', strtotime($badge['created_at'])); ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm btn-edit-badge"
                            data-bs-toggle="modal" data-bs-target="#modalEditBadge"
                            data-badge-id="<?= $badge['id']; ?>"
                            data-badge-title="<?= htmlspecialchars($badge['title']); ?>"
                            data-badge-description="<?= htmlspecialchars($badge['description'] ?? ''); ?>"
                            data-badge-criteria="<?= htmlspecialchars(json_encode($badge['criteria'])); ?>"
                            data-badge-repeatable="<?= $badge['repeatable'] ? '1' : '0'; ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm btn-delete-badge" data-badge-id="<?= $badge['id']; ?>">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        <button type="button" class="btn btn-success btn-sm btn-award-badge"
                            data-bs-toggle="modal" data-bs-target="#modalAwardBadge"
                            data-badge-id="<?= $badge['id']; ?>">
                            <i class="fas fa-trophy"></i> Award
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Pagination -->
<nav class="d-flex justify-content-between align-items-center" aria-label="Page navigation">
    <div class="mb-3">
        Showing <?= $start; ?> to <?= $end; ?> of <?= $total_data; ?> entries
    </div>
    <?php if ($total_pages > 1) : ?>
        <ul class="pagination mb-0">
            <!-- Previous Button -->
            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?= build_query_string(array_merge($base_params, ['page' => $page - 1])); ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($base_params, ['page' => $i])); ?>">
                        <?= $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Next Button -->
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?= build_query_string(array_merge($base_params, ['page' => $page + 1])); ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    <?php endif; ?>
</nav>
