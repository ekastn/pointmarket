<?php
// Helper function to build query strings
function build_query_string(array $params): string
{
    return http_build_query(array_filter($params));
}

$base_params = [
    'search' => $search ?? '',
    'category_id' => $category_id ?? null,
];
?>

<div class="container-fluid">
    <?php 
        ob_start();
    ?>
        <form method="GET" class="d-flex gap-2 align-items-center">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width: 220px" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
            <select name="category_id" class="form-select form-select-sm" style="max-width: 220px">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?= htmlspecialchars($category['id']) ?>" <?= ($category_id == $category['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-secondary btn-sm" type="submit"><i class="fas fa-search"></i></button>
        </form>
    <?php
        $right = ob_get_clean();
        $renderer->includePartial('components/partials/page_title', [
            'icon' => 'fas fa-store',
            'title' => htmlspecialchars($title ?: 'Produk'),
            'right' => $right,
        ]);
    ?>

    <div class="row pm-section"></div>

    <div class="row pm-section">
        <?php if (empty($products)) : ?>
            <div class="col-12">
                <?php $renderer->includePartial('components/partials/empty_state', [
                    'icon' => 'fas fa-box-open',
                    'title' => 'Tidak ada produk',
                    'subtitle' => 'Coba ubah kata kunci pencarian atau filter kategori.',
                ]); ?>
            </div>
        <?php else : ?>
            <?php foreach ($products as $product) : ?>
                <?php if (!(isset($_SESSION['user_data']['role']) && $_SESSION['user_data']['role'] === 'admin') && isset($product['is_active']) && !$product['is_active']) { continue; } ?>
                <?php $renderer->includePartial('components/partials/product_card', ['product' => $product]); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <nav class="d-flex justify-content-between align-items-center pm-section" aria-label="Page navigation">
        <div class="mb-3">
            Menampilkan <?= ($page - 1) * $limit + 1; ?>–<?= min($page * $limit, $total_data); ?> dari <?= $total_data; ?> data
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

<script src="/public/assets/js/products.js"></script>

<!-- Generic Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="messageModalBody"></p>
            </div>
            <div class="modal-footer" id="notificationFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            <div class="modal-footer" id="confirmationFooter" style="display: none;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="modalCancelBtn">Batal</button>
                <button type="button" class="btn btn-primary" id="modalConfirmBtn">Ya</button>
            </div>
        </div>
    </div>
</div>
