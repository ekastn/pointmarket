<div class="container-fluid mt-3">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-users-cog me-2"></i>
            Data Pengguna
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $title; ?></li>
            </ol>
        </nav>
    </div>

    <div class="row mb-3">
        <div class="col-12 col-md-6">
            <?php if (isset($_SESSION['user_groups']) && in_array('superadmin', $_SESSION['user_groups'])) : ?>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahUser"><i class="fas fa-plus"></i> Input</button>
            <?php endif ?>
        </div>
        <div class="col-12 col-md-3">
            <form method="GET" class="d-flex">
                <select name="role" class="form-select me-2">
                    <option value="">Semua Role</option>
                    <option value="admin" <?= (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="guru" <?= (isset($_GET['role']) && $_GET['role'] === 'guru') ? 'selected' : ''; ?>>Guru</option>
                    <option value="siswa" <?= (isset($_GET['role']) && $_GET['role'] === 'siswa') ? 'selected' : ''; ?>>Siswa</option>
                </select>
                <button class="btn btn-success me-2" type="submit">Filter Role</button>
            </form>
        </div>
        <div class="col-12 col-md-3">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <?php $renderer->includePartial('components/partials/tabel_user', ['users' => $users, 'roles' => $roles, 'page' => $page, 'limit' => $limit, 'total_data' => $total_data, 'total_pages' => $total_pages, 'start' => $start, 'end' => $end]); ?>
            </div>
        </div>
    </div>
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
                <form action="/admin/users/save" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required oninvalid="this.setCustomValidity('Data Tidak Boleh Kosong')" oninput="setCustomValidity('')">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required oninvalid="this.setCustomValidity('Data Tidak Boleh Kosong')" oninput="setCustomValidity('')">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required oninvalid="this.setCustomValidity('Data Tidak Boleh Kosong')" oninput="setCustomValidity('')">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="">Pilih Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars(ucfirst($role)) ?></option>
                            <?php endforeach; ?>
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
