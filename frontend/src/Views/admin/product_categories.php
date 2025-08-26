<?php
// Helper function to build query strings
function build_query_string(array $params): string
{
    return http_build_query(array_filter($params));
}

$base_params = [
    'search' => $search ?? '',
];
?>

<div class="container-fluid">
    <?php 
        $right = '<div class="d-flex gap-2 align-items-center">'
            . '<form method="GET" class="d-flex">'
            . '<input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari nama kategori" value="'.htmlspecialchars($search).'">'
            . '<button class="btn btn-outline-secondary btn-sm" type="submit">Cari</button>'
            . '</form>'
            . '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahCategory"><i class="fas fa-plus"></i> Input</button>'
            . '</div>';
        $renderer->includePartial('components/partials/page_title', [
            'icon' => 'fas fa-tags',
            'title' => htmlspecialchars($title ?: 'Kelola Kategori Produk'),
            'right' => $right,
        ]);
    ?>

    <div class="pm-section">
    <?php
    // Pass data to the partial
    $renderer->includePartial('components/partials/table_product_categories', [
        'categories' => $categories,
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

<!-- Data Modal Box Tambah Category -->
<div class="modal fade" id="modalTambahCategory" tabindex="-1" aria-labelledby="modalTambahCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahCategoryLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/product-categories" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

<!-- Data Modal Box Edit Category -->
<div class="modal fade" id="modalEditCategory" tabindex="-1" aria-labelledby="modalEditCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditCategoryLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="edit-category-form">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" id="edit-category-id">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
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

<script src="/public/assets/js/admin-product-categories.js"></script>
