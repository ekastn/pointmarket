<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($categories)) : ?>
            <tr>
                <td colspan="4" class="text-center">No product categories found.</td>
            </tr>
        <?php else : ?>
            <?php $i = $start; ?>
            <?php foreach ($categories as $category) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($category['name']); ?></td>
                    <td><?= htmlspecialchars($category['description'] ?? '-'); ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm btn-edit-category"
                            data-bs-toggle="modal" data-bs-target="#modalEditCategory"
                            data-category-id="<?= $category['id']; ?>"
                            data-category-name="<?= htmlspecialchars($category['name']); ?>"
                            data-category-description="<?= htmlspecialchars($category['description'] ?? ''); ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm btn-delete-category" data-category-id="<?= $category['id']; ?>">
                            <i class="fas fa-trash"></i> Delete
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
