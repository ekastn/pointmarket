<?php
/**
 * This view renders the user mission progress table specifically for the admin missions page.
 * It uses the generic table partial for consistent styling but handles its own pagination logic.
 *
 * @var array $progress The array of progress data.
 * @var array $progress_meta Pagination metadata for progress.
 * @var string $progress_search The current search query for progress.
 */

// Helper function to build query strings for pagination
if (!function_exists('build_query_string')) {
    function build_query_string(array $params): string
    {
        return http_build_query(array_filter($params));
    }
}

$pPage = $progress_meta['page'] ?? 1;
$pTotal = $progress_meta['total'] ?? 0;
$pLimit = $progress_meta['limit'] ?? 10;
$pTotalPages = ($pLimit > 0) ? ceil($pTotal / $pLimit) : 1;
if ($pTotalPages < 1) $pTotalPages = 1;

// Prepare base parameters for progress pagination links
$current_get_params = $_GET;
unset($current_get_params['page']); // Remove mission list page param
unset($current_get_params['progress_page']); // Remove current progress page param

// Prepare columns for the generic table partial
$columns = [
    ['label' => 'Started', 'key' => 'started_at', 'formatter' => fn($val) => date('d M Y H:i', strtotime($val))],
    ['label' => 'Completed', 'key' => 'completed_at', 'formatter' => fn($val) => $val ? date('d M Y H:i', strtotime($val)) : '-'],
    ['label' => 'Mission', 'key' => 'mission_title'],
    ['label' => 'User', 'key' => 'user_name'],
    ['label' => 'Status', 'key' => 'status', 'formatter' => function($val) {
        $color = 'secondary';
        if ($val === 'completed') $color = 'success';
        elseif ($val === 'in_progress') $color = 'primary';
        elseif ($val === 'not_started') $color = 'warning';
        return '<span class="badge bg-' . $color . '">' . ucfirst(str_replace('_', ' ', $val)) . '</span>';
    }],
];

$actions = []; // No actions for now

?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tasks me-2"></i>User Mission Progress</h6>
        <form method="GET" class="d-flex">
            <?php foreach($current_get_params as $key => $value): ?>
                <?php if (!is_array($value)): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="text" name="progress_search" class="form-control form-control-sm me-2" placeholder="Search progress..." value="<?= htmlspecialchars($progress_search ?? '') ?>">
            <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
        </form>
    </div>
    
    <!-- Use generic table partial ONLY for rendering the table, NOT pagination -->
    <?php
    $renderer->includePartial('components/partials/table', [
        'columns' => $columns,
        'actions' => $actions,
        'data' => $progress,
        'empty_message' => 'No mission progress found.',
    ]);
    ?>

    <!-- Manual Pagination Controls for Progress -->
    <?php if ($pTotalPages > 1): ?>
    <div class="card-footer bg-white border-top-0 pt-0">
        <nav class="d-flex justify-content-between align-items-center" aria-label="Progress page navigation">
            <div class="mb-0 small text-muted">
                Showing <?= htmlspecialchars(($pPage - 1) * $pLimit + 1); ?> to <?= htmlspecialchars(min($pPage * $pLimit, $pTotal)); ?> of <?= htmlspecialchars($pTotal); ?> entries
            </div>
            <ul class="pagination pagination-sm flex-wrap mb-0">
                <li class="page-item <?= $pPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($current_get_params, ['progress_page' => $pPage - 1])) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo; Previous</span>
                    </a>
                </li>
                <?php
                    $renderPage = function(int $p) use ($pPage, $current_get_params) {
                        $active = ($p === $pPage) ? 'active' : '';
                        $qs = build_query_string(array_merge($current_get_params, ['progress_page' => $p]));
                        echo '<li class="page-item ' . $active . '">';
                        echo '<a class="page-link" href="?' . $qs . '"' . ($active ? ' aria-current="page"' : '') . '>' . $p . '</a>';
                        echo '</li>';
                    };

                    if ($pTotalPages <= 7) {
                        for ($p = 1; $p <= $pTotalPages; $p++) { $renderPage($p); }
                    } else {
                        $renderPage(1);
                        if ($pPage <= 3) {
                            for ($p = 2; $p <= 4; $p++) { $renderPage($p); }
                            if (4 < $pTotalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            }
                            $renderPage($pTotalPages);
                        } elseif ($pPage >= $pTotalPages - 2) {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            for ($p = $pTotalPages - 3; $p <= $pTotalPages; $p++) { $renderPage($p); }
                        } else {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            for ($p = $pPage - 1; $p <= $pPage + 1; $p++) { $renderPage($p); }
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            $renderPage($pTotalPages);
                        }
                    }
                ?>
                <li class="page-item <?= $pPage >= $pTotalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($current_get_params, ['progress_page' => $pPage + 1])) ?>" aria-label="Next">
                        <span aria-hidden="true">Next &raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
