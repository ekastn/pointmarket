<?php
/**
 * This view renders the user badge awards table specifically for the admin badges page.
 * It uses the generic table partial for consistent styling but handles its own pagination logic.
 *
 * @var array $awards The array of award data.
 * @var array $awards_meta Pagination metadata for awards.
 * @var string $awards_search The current search query for awards.
 */

// Helper function to build query strings for pagination
if (!function_exists('build_query_string')) {
    function build_query_string(array $params): string
    {
        return http_build_query(array_filter($params));
    }
}

$aPage = $awards_meta['page'] ?? 1;
$aTotal = $awards_meta['total'] ?? 0;
$aLimit = $awards_meta['limit'] ?? 10;
$aTotalPages = ($aLimit > 0) ? ceil($aTotal / $aLimit) : 1;
if ($aTotalPages < 1) $aTotalPages = 1;

// Prepare base parameters for award pagination links
$current_get_params = $_GET;
unset($current_get_params['page']); // Remove badge list page param
unset($current_get_params['awards_page']); // Remove current awards page param

// Prepare columns for the generic table partial
$columns = [
    ['label' => 'Date', 'key' => 'awarded_at', 'formatter' => fn($val) => date('d M Y H:i', strtotime($val))],
    ['label' => 'Badge', 'key' => 'badge_title'],
    ['label' => 'User', 'key' => 'user_name'],
    ['label' => 'Email', 'key' => 'user_email'],
];

$actions = []; // No actions for now

?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-trophy me-2"></i>User Badge Awards</h6>
        <form method="GET" class="d-flex">
            <?php foreach($current_get_params as $key => $value): ?>
                <?php if (!is_array($value)): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="text" name="awards_search" class="form-control form-control-sm me-2" placeholder="Search awards..." value="<?= htmlspecialchars($awards_search ?? '') ?>">
            <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
        </form>
    </div>
    
    <!-- Use generic table partial ONLY for rendering the table, NOT pagination -->
    <?php
    $renderer->includePartial('components/partials/table', [
        'columns' => $columns,
        'actions' => $actions,
        'data' => $awards,
        'empty_message' => 'No badge awards found.',
    ]);
    ?>

    <!-- Manual Pagination Controls for Awards -->
    <?php if ($aTotalPages > 1): ?>
    <div class="card-footer bg-white border-top-0 pt-0">
        <nav class="d-flex justify-content-between align-items-center" aria-label="Awards page navigation">
            <div class="mb-0 small text-muted">
                Showing <?= htmlspecialchars(($aPage - 1) * $aLimit + 1); ?> to <?= htmlspecialchars(min($aPage * $aLimit, $aTotal)); ?> of <?= htmlspecialchars($aTotal); ?> entries
            </div>
            <ul class="pagination pagination-sm flex-wrap mb-0">
                <li class="page-item <?= $aPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($current_get_params, ['awards_page' => $aPage - 1])) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo; Previous</span>
                    </a>
                </li>
                <?php
                    $renderPage = function(int $p) use ($aPage, $current_get_params) {
                        $active = ($p === $aPage) ? 'active' : '';
                        $qs = build_query_string(array_merge($current_get_params, ['awards_page' => $p]));
                        echo '<li class="page-item ' . $active . '">';
                        echo '<a class="page-link" href="?' . $qs . '"' . ($active ? ' aria-current="page"' : '') . '>' . $p . '</a>';
                        echo '</li>';
                    };

                    if ($aTotalPages <= 7) {
                        for ($p = 1; $p <= $aTotalPages; $p++) { $renderPage($p); }
                    } else {
                        $renderPage(1);
                        if ($aPage <= 3) {
                            for ($p = 2; $p <= 4; $p++) { $renderPage($p); }
                            if (4 < $aTotalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            }
                            $renderPage($aTotalPages);
                        } elseif ($aPage >= $aTotalPages - 2) {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            for ($p = $aTotalPages - 3; $p <= $aTotalPages; $p++) { $renderPage($p); }
                        } else {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            for ($p = $aPage - 1; $p <= $aPage + 1; $p++) { $renderPage($p); }
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            $renderPage($aTotalPages);
                        }
                    }
                ?>
                <li class="page-item <?= $aPage >= $aTotalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($current_get_params, ['awards_page' => $aPage + 1])) ?>" aria-label="Next">
                        <span aria-hidden="true">Next &raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
