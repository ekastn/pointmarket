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
    <?php $renderer->includePartial('components/partials/page_title', [
        'icon' => 'fas fa-store',
        'title' => htmlspecialchars($title ?: 'Produk'),
    ]); ?>

    <div class="row pm-section">
        <div class="col-12 col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </form>
        </div>
        <div class="col-12 col-md-6">
            <form method="GET" class="d-flex">
                <select name="category_id" class="form-select me-2">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>" <?= ($category_id == $category['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </form>
        </div>
    </div>

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

<script src="/public/assets/js/products.js"></script>
