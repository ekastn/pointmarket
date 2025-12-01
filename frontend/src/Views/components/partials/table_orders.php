<?php
/**
 * This view renders the orders/transactions table specifically for the admin product management page.
 * It uses the generic table partial for consistent styling but handles its own pagination logic
 * to avoid conflict with the main products table pagination.
 *
 * @var array $orders The array of order data.
 * @var array $orders_meta Pagination metadata for orders.
 * @var string $orders_search The current search query for orders.
 */

// Helper function to build query strings for pagination
if (!function_exists('build_query_string')) {
    function build_query_string(array $params): string
    {
        return http_build_query(array_filter($params));
    }
}

$oPage = $orders_meta['page'] ?? 1;
$oTotal = $orders_meta['total'] ?? 0;
$oLimit = $orders_meta['limit'] ?? 10;
$oTotalPages = ($oLimit > 0) ? ceil($oTotal / $oLimit) : 1;
if ($oTotalPages < 1) $oTotalPages = 1;

// Prepare base parameters for order pagination links
$current_get_params = $_GET;
unset($current_get_params['page']); // Remove product page param
unset($current_get_params['orders_page']); // Remove current orders page param

// Prepare columns for the generic table partial
$columns = [
    ['label' => 'ID', 'key' => 'id'],
    ['label' => 'Date', 'key' => 'ordered_at', 'formatter' => fn($val) => date('d M Y H:i', strtotime($val))],
    ['label' => 'User', 'key' => 'user_name'],
    ['label' => 'Current Points', 'key' => 'user_current_points', 'formatter' => fn($val) => number_format($val)],
    ['label' => 'Product', 'key' => 'product_name'],
    ['label' => 'Points', 'key' => 'points_spent', 'formatter' => fn($val) => '<span class="text-danger">-' . $val . '</span>'],
    ['label' => 'Status', 'key' => 'status', 'formatter' => function($val) {
        $color = ($val === 'completed') ? 'success' : 'warning';
        return '<span class="badge bg-' . $color . '">' . ucfirst($val) . '</span>';
    }],
];

$actions = []; // No actions for now

?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history me-2"></i>Transaction History</h6>
        <form method="GET" class="d-flex">
            <?php foreach($current_get_params as $key => $value): ?>
                <?php if (!is_array($value)): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="text" name="orders_search" class="form-control form-control-sm me-2" placeholder="Search orders..." value="<?= htmlspecialchars($orders_search ?? '') ?>">
            <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
        </form>
    </div>
    
    <!-- Use generic table partial ONLY for rendering the table, NOT pagination -->
    <?php
    $renderer->includePartial('components/partials/table', [
        'columns' => $columns,
        'actions' => $actions,
        'data' => $orders,
        'empty_message' => 'No transactions found.',
        // 'pagination' is intentionally omitted so the partial doesn't render its default pagination
    ]);
    ?>

    <!-- Manual Pagination Controls for Orders -->
    <?php if ($oTotalPages > 1): ?>
    <div class="card-footer bg-white border-top-0 pt-0">
        <nav class="d-flex justify-content-between align-items-center" aria-label="Orders page navigation">
            <div class="mb-0 small text-muted">
                Showing <?= htmlspecialchars(($oPage - 1) * $oLimit + 1); ?> to <?= htmlspecialchars(min($oPage * $oLimit, $oTotal)); ?> of <?= htmlspecialchars($oTotal); ?> entries
            </div>
            <ul class="pagination pagination-sm flex-wrap mb-0">
                <li class="page-item <?= $oPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($current_get_params, ['orders_page' => $oPage - 1])) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php
                    $renderPage = function(int $p) use ($oPage, $current_get_params) {
                        $active = ($p === $oPage) ? 'active' : '';
                        $qs = build_query_string(array_merge($current_get_params, ['orders_page' => $p]));
                        echo '<li class="page-item ' . $active . '">';
                        echo '<a class="page-link" href="?' . $qs . '"' . ($active ? ' aria-current="page"' : '') . '>' . $p . '</a>';
                        echo '</li>';
                    };

                    if ($oTotalPages <= 7) {
                        for ($p = 1; $p <= $oTotalPages; $p++) { $renderPage($p); }
                    } else {
                        $renderPage(1);
                        if ($oPage <= 3) {
                            for ($p = 2; $p <= 4; $p++) { $renderPage($p); }
                            if (4 < $oTotalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            }
                            $renderPage($oTotalPages);
                        } elseif ($oPage >= $oTotalPages - 2) {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            for ($p = $oTotalPages - 3; $p <= $oTotalPages; $p++) { $renderPage($p); }
                        } else {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            for ($p = $oPage - 1; $p <= $oPage + 1; $p++) { $renderPage($p); }
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                            $renderPage($oTotalPages);
                        }
                    }
                ?>
                <li class="page-item <?= $oPage >= $oTotalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= build_query_string(array_merge($current_get_params, ['orders_page' => $oPage + 1])) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
