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
            . '<input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari judul" value="'.htmlspecialchars($search).'">'
            . '<button class="btn btn-outline-secondary btn-sm" type="submit">Cari</button>'
            . '</form>'
            . '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBadge"><i class="fas fa-plus"></i> Input</button>'
            . '</div>';
        $renderer->includePartial('components/partials/page_title', [
            'icon' => 'fas fa-id-badge',
            'title' => htmlspecialchars($title ?: 'Kelola Lencana'),
            'right' => $right,
        ]);
    ?>

    <div class="pm-section">
    <?php
    // Pass data to the partial
    echo $renderer->includePartial('components/partials/table_badges', [
        'badges' => $badges,
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

<!-- Data Modal Box Tambah Badge -->
<div class="modal fade" id="modalTambahBadge" tabindex="-1" aria-labelledby="modalTambahBadgeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahBadgeLabel">Add New Badge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/badges" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="criteria" class="form-label">Criteria (JSON)</label>
                        <textarea class="form-control" id="criteria" name="criteria" rows="3">{}</textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="repeatable" name="repeatable" value="1">
                        <label class="form-check-label" for="repeatable">Repeatable</label>
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

<!-- Data Modal Box Edit Badge -->
<div class="modal fade" id="modalEditBadge" tabindex="-1" aria-labelledby="modalEditBadgeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditBadgeLabel">Edit Badge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="edit-badge-form">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" id="edit-badge-id">
                    <div class="mb-3">
                        <label for="edit-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit-title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-criteria" class="form-label">Criteria (JSON)</label>
                        <textarea class="form-control" id="edit-criteria" name="criteria" rows="3">{}</textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-repeatable" name="repeatable" value="1">
                        <label class="form-check-label" for="edit-repeatable">Repeatable</label>
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

<!-- Data Modal Box Award Badge -->
<div class="modal fade" id="modalAwardBadge" tabindex="-1" aria-labelledby="modalAwardBadgeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAwardBadgeLabel">Award Badge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/badges/award" method="post" id="award-badge-form">
                    <input type="hidden" name="badge_id" id="award-badge-id">
                    <div class="mb-3">
                        <label for="award-user-id" class="form-label">User ID</label>
                        <input type="number" class="form-control" id="award-user-id" name="user_id" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Award</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
