<?php
/**
 * This view renders the course enrollments table specifically for the admin courses page.
 * It uses the generic table partial for consistent styling but handles its own pagination logic.
 *
 * @var array $enrollments The array of enrollment data.
 * @var array $enrollments_meta Pagination metadata for enrollments.
 * @var string $enrollments_search The current search query for enrollments.
 */

// Helper function to build query strings for pagination
if (!function_exists('build_query_string')) {
    function build_query_string(array $params): string
    {
        return http_build_query(array_filter($params));
    }
}

$ePage = $enrollments_meta['page'] ?? 1;
$eTotal = $enrollments_meta['total'] ?? 0;
$eLimit = $enrollments_meta['limit'] ?? 10;
$eTotalPages = ($eLimit > 0) ? ceil($eTotal / $eLimit) : 1;
if ($eTotalPages < 1) $eTotalPages = 1;

// Prepare base parameters for enrollment pagination links
$current_get_params = $_GET;
unset($current_get_params['page']); // Remove course list page param
unset($current_get_params['enrollments_page']); // Remove current enrollments page param

// Prepare columns for the generic table partial
$columns = [
    ['label' => 'Date', 'key' => 'enrolled_at', 'formatter' => fn($val) => date('d M Y H:i', strtotime($val))],
    ['label' => 'Course', 'key' => 'course_title'],
    ['label' => 'Student', 'key' => 'student_name'],
    ['label' => 'Email', 'key' => 'student_email'],
];

$actions = []; // No actions for now

?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users me-2"></i>User Enrollments</h6>
        <form method="GET" class="d-flex">
            <?php foreach($current_get_params as $key => $value): ?>
                <?php if (!is_array($value)): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="text" name="enrollments_search" class="form-control form-control-sm me-2" placeholder="Search enrollments..." value="<?= htmlspecialchars($enrollments_search ?? '') ?>">
            <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
        </form>
    </div>
    
    <!-- Use generic table partial ONLY for rendering the table, NOT pagination -->
    <?php
    $renderer->includePartial('components/partials/table', [
        'columns' => $columns,
        'actions' => $actions,
        'data' => $enrollments,
        'empty_message' => 'No enrollments found.',
    ]);
    ?>

    <!-- Manual Pagination Controls for Enrollments -->
    <?php if ($eTotalPages > 1): ?>
    <div class="card-footer bg-white border-top-0 pt-0">
        <nav class="d-flex justify-content-between align-items-center" aria-label="Enrollments page navigation">
            <div class="mb-0 small text-muted">
                Showing <?= htmlspecialchars(($ePage - 1) * $eLimit + 1); ?> to <?= htmlspecialchars(min($ePage * $eLimit, $eTotal)); ?> of <?= htmlspecialchars($eTotal); ?> entries
            </div>
            <ul class="pagination pagination-sm flex-wrap mb-0">
                <li class="page-item <?= $ePage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($current_get_params, ['enrollments_page' => $ePage - 1])) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo; Previous</span>
                    </a>
                </li>
                <?php
                    $renderPage = function(int $p) use ($ePage, $current_get_params) {
                        $active = ($p === $ePage) ? 'active' : '';
                        $qs = build_query_string(array_merge($current_get_params, ['enrollments_page' => $p]));
                        echo '<li class="page-item ' . $active . '">';
                        echo '<a class="page-link" href="?' . $qs . '"' . ($active ? ' aria-current="page"' : '') . '>' . $p . '</a>';
                        echo '</li>';
                    };

                    if ($eTotalPages <= 7) {
                        for ($p = 1; $p <= $eTotalPages; $p++) { $renderPage($p); }
                    } else {
                        $renderPage(1);
                        if ($ePage <= 3) {
                            for ($p = 2; $p <= 4; $p++) { $renderPage($p); }
                            if (4 < $eTotalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            }
                            $renderPage($eTotalPages);
                        } elseif ($ePage >= $eTotalPages - 2) {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            for ($p = $eTotalPages - 3; $p <= $eTotalPages; $p++) { $renderPage($p); }
                        } else {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            for ($p = $ePage - 1; $p <= $ePage + 1; $p++) { $renderPage($p); }
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            $renderPage($eTotalPages);
                        }
                    }
                ?>
                <li class="page-item <?= $ePage >= $eTotalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($current_get_params, ['enrollments_page' => $ePage + 1])) ?>" aria-label="Next">
                        <span aria-hidden="true">Next &raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
