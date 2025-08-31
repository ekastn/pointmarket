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
    <?php $renderer->includePartial('components/partials/page_title', [
        'icon' => 'fas fa-book-open',
        'title' => htmlspecialchars($title ?: 'Kelas'),
    ]); ?>

    <div class="row pm-section">
        <div class="col-12 col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari judul atau slug" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </form>
        </div>
    </div>

    <div class="col-md-9 col-lg-10 ">
        <?php
        $myCourses = array_filter($courses, function($course) {
            return $course["is_enrolled"];
        });
        ?>

        <?php if (!empty($myCourses)): ?>
        <div class="pm-section">
            <h4 class="mb-3">Kursus Saya</h4>
            <div class="row">
                <?php foreach ($myCourses as $course): ?>
                    <?php $renderer->includePartial('components/partials/course_card', ['course' => $course]); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Available Courses -->
        <div class="pm-section">
            <h4 class="mb-3">Kursus Tersedia</h4>
            <div class="row">
                <?php foreach ($courses as $course): ?>
                    <?php if (!$course["is_enrolled"]): ?>
                        <?php $renderer->includePartial('components/partials/course_card', ['course' => $course]); ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <nav class="d-flex justify-content-between align-items-center pm-section" aria-label="Page navigation">
        <div class="mb-3">
            Menampilkan <?= $start; ?>–<?= $end; ?> dari <?= $total_data; ?> data
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
