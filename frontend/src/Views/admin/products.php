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
        $right = '<div class="d-flex gap-2 align-items-center">'
            . '<form method="GET" class="d-flex">'
            . '<input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari produk..." value="'.htmlspecialchars($search).'">'
            . '<select name="category_id" class="form-select form-select-sm me-2"><option value="">Semua Kategori</option>';
        foreach ($categories as $category) {
            $sel = ($category_id == $category['id']) ? 'selected' : '';
            $right .= '<option value="'.htmlspecialchars($category['id']).'" '.$sel.'>'.htmlspecialchars($category['name']).'</option>';
        }
        $right .= '</select><button class="btn btn-outline-secondary btn-sm" type="submit">Cari</button></form>'
            . '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahProduct"><i class="fas fa-plus"></i> Input</button>'
            . '</div>';
        $renderer->includePartial('components/partials/page_title', [
            'icon' => 'fas fa-box-open',
            'title' => 'Data Produk',
            'right' => $right,
        ]);
    ?>

    <div class="pm-section">
    <?php
    // Pass data to the partial
    $renderer->includePartial('components/partials/table_products', [
        'products' => $products,
        'page' => $page,
        'limit' => $limit,
        'total_data' => $total_data,
        'total_pages' => $total_pages,
        'start' => ($page - 1) * $limit + 1,
        'end' => min($page * $limit, $total_data),
        'search' => $search,
        'base_params' => $base_params,
    ]);
    ?>
    </div>
</div>

<!-- Data Modal Box Tambah Product -->
<div class="modal fade" id="modalTambahProduct" tabindex="-1" aria-labelledby="modalTambahProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahProductLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/products" method="post">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">No Category</option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?= htmlspecialchars($category['id']) ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="points_price" class="form-label">Points Price</label>
                        <input type="number" class="form-control" id="points_price" name="points_price" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <input type="text" class="form-control" id="type" name="type" required>
                    </div>
                    <div class="mb-3">
                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Is Active</label>
                    </div>
                    <div class="mb-3">
                        <label for="metadata" class="form-label">Metadata (JSON)</label>
                        <textarea class="form-control" id="metadata" name="metadata" rows="3">{}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Data Modal Box Edit Product -->
<div class="modal fade" id="modalEditProduct" tabindex="-1" aria-labelledby="modalEditProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditProductLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="edit-product-form">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" id="edit-product-id">
                    <div class="mb-3">
                        <label for="edit-category_id" class="form-label">Category</label>
                        <select class="form-select" id="edit-category_id" name="category_id">
                            <option value="">No Category</option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?= htmlspecialchars($category['id']) ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-points_price" class="form-label">Points Price</label>
                        <input type="number" class="form-control" id="edit-points_price" name="points_price" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-type" class="form-label">Type</label>
                        <input type="text" class="form-control" id="edit-type" name="type" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-stock_quantity" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" id="edit-stock_quantity" name="stock_quantity">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit-is_active">Is Active</label>
                    </div>
                    <div class="mb-3">
                        <label for="edit-metadata" class="form-label">Metadata (JSON)</label>
                        <textarea class="form-control" id="edit-metadata" name="metadata" rows="3">{}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/public/assets/js/admin-products.js"></script>
