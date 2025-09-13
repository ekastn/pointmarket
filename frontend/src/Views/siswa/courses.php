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
    <?php 
        ob_start();
    ?>
        <form method="GET" class="d-flex gap-2 align-items-center">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width: 260px" placeholder="Cari judul atau slug" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-secondary btn-sm" type="submit"><i class="fas fa-search"></i></button>
        </form>
    <?php
        $right = ob_get_clean();
        $renderer->includePartial('components/partials/page_title', [
            'icon' => 'fas fa-book-open',
            'title' => htmlspecialchars($title ?: 'Kelas'),
            'right' => $right,
        ]);
    ?>

    <div class="row pm-section"></div>

    <div class="col-md-9 col-lg-10 ">
        <?php
        // Detect if courses have is_enrolled flag
        $hasEnrollFlag = false;
        foreach (($courses ?? []) as $c) { if (isset($c['is_enrolled'])) { $hasEnrollFlag = true; break; } }

        if ($hasEnrollFlag) {
            $myCourses = array_filter($courses, function($course) {
                return !empty($course['is_enrolled']);
            });
            if (!empty($myCourses)) {
                echo '<div class="pm-section">';
                echo '<h4 class="mb-3">Kelas Saya</h4>';
                echo '<div class="row">';
                foreach ($myCourses as $course) { $renderer->includePartial('components/partials/course_card', ['course' => $course]); }
                echo '</div></div>';
            }

            echo '<div class="pm-section">';
            echo '<h4 class="mb-3">Kelas Tersedia</h4>';
            echo '<div class="row">';
            foreach ($courses as $course) {
                if (empty($course['is_enrolled'])) { $renderer->includePartial('components/partials/course_card', ['course' => $course]); }
            }
            echo '</div></div>';
        } else {
            // Teacher view (no enrollment flag): show all as cards
            echo '<div class="pm-section">';
            echo '<div class="row">';
            foreach ($courses as $course) { $renderer->includePartial('components/partials/course_card', ['course' => $course]); }
            echo '</div></div>';
        }
        ?>
    </div>

    <!-- Pagination -->
    <nav class="d-flex justify-content-between align-items-center pm-section" aria-label="Page navigation">
        <div class="mb-3">
            Menampilkan <?= $start; ?>–<?= $end; ?> dari <?= $total_data; ?> data
        </div>
        <?php if ($total_pages > 1) : ?>
            <ul class="pagination pagination-sm flex-wrap mb-0">
                <!-- Previous Button -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($base_params, ['page' => $page - 1])); ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php
                    $render = function(int $p) use ($page, $base_params) {
                        $active = ($p === (int)$page) ? 'active' : '';
                        $qs = build_query_string(array_merge($base_params, ['page' => $p]));
                        echo '<li class="page-item ' . $active . '">';
                        echo '<a class="page-link" href="?' . $qs . '"' . ($active ? ' aria-current="page"' : '') . '>' . $p . '</a>';
                        echo '</li>';
                    };

                    if ($total_pages <= 7) {
                        for ($i = 1; $i <= $total_pages; $i++) { $render($i); }
                    } else {
                        $render(1);
                        if ($page <= 3) {
                            $end = min(4, $total_pages - 1);
                            for ($i = 2; $i <= $end; $i++) { $render($i); }
                            if ($end < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            }
                            $render($total_pages);
                        } elseif ($page >= $total_pages - 2) {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            $start = max($total_pages - 3, 2);
                            for ($i = $start; $i <= $total_pages; $i++) { $render($i); }
                        } else {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            for ($i = $page - 1; $i <= $page + 1; $i++) { $render($i); }
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            $render($total_pages);
                        }
                    }
                ?>

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
