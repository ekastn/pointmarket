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
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($title ?: 'Kursus') ?></h1>

    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari judul atau slug" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
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
        <div class="mb-5">
            <h4 class="mb-3">Kursus Saya</h4>
            <div class="row">
                <?php foreach ($myCourses as $course): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card card-hover h-100">
                            <img src="/public/images/product_placeholder.png" class="card-img-top" alt="<?= htmlspecialchars($course["title"]) ?>">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title"><?= $course["title"] ?></h5>
                                </div>
                                <p class="card-text text-muted small"><?= $course["description"] ?? "" ?></p>
                                <div class="mt-auto">
                                    <a href="/courses/<?= $course["slug"] ?>" class="btn btn-primary w-100">Lihat Kursus</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Available Courses -->
        <div>
            <h4 class="mb-3">Kursus Tersedia</h4>
            <div class="row">
                <?php foreach ($courses as $course): ?>
                    <?php if (!$course["is_enrolled"]): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card card-hover h-100">
                                <img src="/public/images/product_placeholder.png" class="card-img-top" alt="<?= htmlspecialchars($course["title"]) ?>">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title"><?= $course["title"] ?></h5>
                                    </div>
                                    <p class="card-text text-muted small"><?= $course["description"] ?? "" ?></p>
                                    <div class="mt-auto">
                                        <a href="/courses/<?= $course["slug"] ?>" class="btn btn-primary w-100">Lihat Kursus</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <nav class="d-flex justify-content-between align-items-center" aria-label="Page navigation">
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
