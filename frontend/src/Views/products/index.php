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

    <?php if (isset($_SESSION['messages'])): ?>
        <div class="alert alert-<?= key($_SESSION['messages']) ?> alert-dismissible fade show" role="alert">
            <?= reset($_SESSION['messages']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['messages']); ?>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
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
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['description'] ?? 'No description provided.') ?></p>
                            <p class="card-text"><strong>Price:</strong> <?= htmlspecialchars($product['points_price']) ?> Points</p>
                            <p class="card-text"><strong>Type:</strong> <?= htmlspecialchars($product['type']) ?></p>
                            <?php if ($product['stock_quantity'] !== null) : ?>
                                <p class="card-text"><strong>Stock:</strong> <?= htmlspecialchars($product['stock_quantity']) ?></p>
                            <?php endif; ?>
                            <button class="btn btn-primary btn-purchase" data-product-id="<?= $product['id']; ?>">Purchase</button>
                        </div>
                    </div>
                </div>
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
