<?php
// Helper function to build query strings
function build_query_string(array $params): string
{
    return http_build_query(array_filter($params));
}

$base_params = [
    'search' => $search ?? '',
    'role' => $role ?? ''
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
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahCourse">
                <i class="fas fa-plus"></i> Input
            </button>
        </div>
        <div class="col-12 col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by title or slug" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Course List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php
                // Pass data to the partial
                $renderer->includePartial('components/partials/tabel_courses', [
                    'courses' => $courses,
                    'page' => $page,
                    'limit' => $limit,
                    'total_data' => $total_data,
                    'total_pages' => $total_pages,
                    'start' => $start,
                    'end' => $end,
                    'search' => $search,
                    'base_params' => $base_params,
                ]);
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Data Modal Box Tambah Course -->
<div class="modal fade" id="modalTambahCourse" tabindex="-1" aria-labelledby="modalTambahCourseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahCourseLabel">Add New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/courses" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

<!-- Data Modal Box Edit Course -->
<div class="modal fade" id="modalEditCourse" tabindex="-1" aria-labelledby="modalEditCourseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditCourseLabel">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="edit-course-form">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" id="edit-course-id">
                    <div class="mb-3">
                        <label for="edit-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit-title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="edit-slug" name="slug" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
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
