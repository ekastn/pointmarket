<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Type</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Active</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($products)) : ?>
            <tr>
                <td colspan="9" class="text-center">No products found.</td>
            </tr>
        <?php else : ?>
            <?php $i = $start; ?>
            <?php foreach ($products as $product) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($product['name']); ?></td>
                    <td><?= htmlspecialchars($product['description'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($product['points_price']); ?></td>
                    <td><?= htmlspecialchars($product['type']); ?></td>
                    <td><?= htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($product['stock_quantity'] ?? '-'); ?></td>
                    <td><?= $product['is_active'] ? 'Yes' : 'No'; ?></td>
                    <td><?= date('d-m-Y H:i', strtotime($product['created_at'])); ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm btn-edit-product"
                            data-bs-toggle="modal" data-bs-target="#modalEditProduct"
                            data-product-id="<?= $product['id']; ?>"
                            data-product-name="<?= htmlspecialchars($product['name']); ?>"
                            data-product-description="<?= htmlspecialchars($product['description'] ?? ''); ?>"
                            data-product-points-price="<?= htmlspecialchars($product['points_price']); ?>"
                            data-product-type="<?= htmlspecialchars($product['type']); ?>"
                            data-product-stock-quantity="<?= htmlspecialchars($product['stock_quantity'] ?? ''); ?>"
                            data-product-is-active="<?= $product['is_active'] ? '1' : '0'; ?>"
                            data-product-metadata="<?= htmlspecialchars(json_encode($product['metadata'])); ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm btn-delete-product" data-product-id="<?= $product['id']; ?>">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Pagination -->
<nav class="d-flex justify-content-between align-items-center" aria-label="Page navigation">
    <div class="mb-3">
        Showing <?= $start; ?> to <?= $end; ?> of <?= $total_data; ?> entries
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
