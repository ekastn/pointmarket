<div class="container-fluid mt-3">
    <?php 
        $right = '<div class="d-flex gap-2 align-items-center">'
            . '<form method="GET" class="d-flex">'
            . '<select name="role" class="form-select form-select-sm me-2">'
            . '<option value="">Semua Role</option>'
            . '<option value="admin" '.((isset($_GET['role']) && $_GET['role']==='admin') ? 'selected' : '').'>Admin</option>'
            . '<option value="guru" '.((isset($_GET['role']) && $_GET['role']==='guru') ? 'selected' : '').'>Guru</option>'
            . '<option value="siswa" '.((isset($_GET['role']) && $_GET['role']==='siswa') ? 'selected' : '').'>Siswa</option>'
            . '</select>'
            . '<input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari..." value="'.(isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '').'">'
            . '<button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-search"></i></button>'
            . '</form>'
            . '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser"><i class="fas fa-plus"></i> Input</button>'
            . '</div>';
        $renderer->includePartial('components/partials/page_title', [
            'icon' => 'fas fa-users-cog',
            'title' => 'Data Pengguna',
            'right' => $right,
        ]);
    ?>

    <?php $renderer->includePartial('components/partials/table_user', ['users' => $users, 'roles' => $roles, 'page' => $page, 'limit' => $limit, 'total_data' => $total_data, 'total_pages' => $total_pages, 'start' => $start, 'end' => $end]); ?>
</div>

<!--Data Modal Box Tambah User-->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahUserLabel">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/users" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="" disabled selected>Pilih Role</option>
                            <?php foreach ($roles as $role) { ?>
                                <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars(ucfirst($role)) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Tambah</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!--Data Modal Box Edit User-->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditUserLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" id="edit-user-form">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" id="edit-user-id">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit-username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit-email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-role" class="form-label">Role</label>
                        <select class="form-select" id="edit-role" name="role" required>
                            <option value="" disabled selected>Pilih Role</option>
                            <?php foreach ($roles as $role) { ?>
                                <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars(ucfirst($role)) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/public/assets/js/admin-users.js"></script>
