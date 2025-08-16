<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Title</th>
            <th>Slug</th>
            <th>Description</th>
            <th>Owner ID</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($courses)) : ?>
            <tr>
                <td colspan="8" class="text-center">No courses found.</td>
            </tr>
        <?php else : ?>
            <?php $i = $start; ?>
            <?php foreach ($courses as $course) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($course['title']); ?></td>
                    <td><?= htmlspecialchars($course['slug']); ?></td>
                    <td><?= htmlspecialchars($course['description'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($course['owner_id']); ?></td>
                    <td><?= date('d-m-Y H:i', strtotime($course['created_at'])); ?></td>
                    <td><?= date('d-m-Y H:i', strtotime($course['updated_at'])); ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm btn-edit-course"
                            data-bs-toggle="modal" data-bs-target="#modalEditCourse"
                            data-course-id="<?= $course['id']; ?>"
                            data-course-title="<?= htmlspecialchars($course['title']); ?>"
                            data-course-slug="<?= htmlspecialchars($course['slug']); ?>"
                            data-course-description="<?= htmlspecialchars($course['description'] ?? ''); ?>"
                            data-course-metadata="<?= htmlspecialchars(json_encode($course['metadata'])); ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm btn-delete-course" data-course-id="<?= $course['id']; ?>">
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
