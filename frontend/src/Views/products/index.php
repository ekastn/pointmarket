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
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </form>
        </div>
        <div class="col-12 col-md-6">
            <form method="GET" class="d-flex">
                <select name="category_id" class="form-select me-2">
                    <option value="">All Categories</option>
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

    <div class="row">
        <?php if (empty($products)) : ?>
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    No products found.
                </div>
            </div>
        <?php else : ?>
            <?php foreach ($products as $product) : ?>
                <?php $renderer->includePartial('components/partials/product_card', ['product' => $product]); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <nav class="d-flex justify-content-between align-items-center" aria-label="Page navigation">
        <div class="mb-3">
            Showing <?= ($page - 1) * $limit + 1; ?> to <?= min($page * $limit, $total_data); ?> of <?= $total_data; ?> entries
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
