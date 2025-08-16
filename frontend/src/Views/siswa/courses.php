<?php
// Helper function to build query strings
function build_query_string(array $params): string
{
    return http_build_query(array_filter($params));
}

$base_params = [
    'search' => $search ?? '',
];
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <?php if (isset($_SESSION['messages'])): ?>
        <div class="alert alert-<?= key($_SESSION['messages']) ?> alert-dismissible fade show" role="alert">
            <?= reset($_SESSION['messages']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['messages']); ?>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by title or slug" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">My Enrolled Courses</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Owner ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)) : ?>
                            <tr>
                                <td colspan="6" class="text-center">No courses found.</td>
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
                                    <td>
                                        <button class="btn btn-danger btn-sm btn-unenroll" data-course-id="<?= $course['id']; ?>"><i class="fas fa-minus-circle"></i> Unenroll</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
</div>
