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

<table class="table table-bordered table-striped">
    <thead class="bg-info">
        <tr>
            <th scope="col">No</th>
            <th scope="col">Username</th>
            <th scope="col">Email</th>
            <th scope="col">Role</th>
            <th scope="col">Waktu Dibuat</th>
            <th scope="col" colspan="2">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($users)) : ?>
            <tr>
                <td colspan="6" class="text-center">No users found.</td>
            </tr>
        <?php else : ?>
            <?php $i = $start; ?>
            <?php foreach ($users as $u) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($u['username']); ?></td>
                    <td><?= htmlspecialchars($u['email']); ?></td>
                    <td>
                        <span class="badge bg-<?php
                                                    $role_class = 'info';
                                                    if ($u['role'] === 'admin') $role_class = 'warning';
                                                    if ($u['role'] === 'guru') $role_class = 'danger';
                                                    if ($u['role'] === 'siswa') $role_class = 'success';
                                                    echo $role_class;
                                                    ?>">
                            <?= htmlspecialchars($u['role']); ?>
                        </span>
                    </td>
                    <td><?= date('d-m-Y H:i', strtotime($u['created_at'])); ?></td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $u['id']; ?>"><i class="fas fa-eye"></i><span class="d-none d-md-inline"> Detail</span></button>
                        <button type="button" class="btn btn-warning btn-sm btn-edit-user" data-bs-toggle="modal" data-bs-target="#modalEditUser"
                            data-user-id="<?= $u['id']; ?>"
                            data-user-name="<?= htmlspecialchars($u['name']); ?>"
                            data-user-username="<?= htmlspecialchars($u['username']); ?>"
                            data-user-email="<?= htmlspecialchars($u['email']); ?>"
                            data-user-role="<?= htmlspecialchars($u['role']); ?>">
                            <i class="fas fa-edit"></i><span class="d-none d-md-inline"> Edit</span>
                        </button>
                    </td>
                    <?php if (isset($_SESSION['user_data']) && $_SESSION['user_data']['role'] === 'admin') : ?>
                        <td>
                            <button class="btn btn-danger btn-sm btn-hapus" data-user-id="<?= $u['id']; ?>"><i class="fas fa-trash"></i><span class="d-none d-md-inline"> Hapus</span></button>
                        </td>
                    <?php endif; ?>
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
            <!-- Tombol Previous -->
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

            <!-- Tombol Next -->
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?= build_query_string(array_merge($base_params, ['page' => $page + 1])); ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    <?php endif; ?>
</nav>

<!-- Modal Box Detail -->
<?php foreach ($users as $u) : ?>
    <div class="modal fade" id="modalDetail<?= $u['id']; ?>" tabindex="-1" aria-labelledby="modalDetailLabel<?= $u['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailLabel<?= $u['id']; ?>">Profil : <?= htmlspecialchars($u['username']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><b>Username:</b> <?= htmlspecialchars($u['username']); ?></li>
                        <li class="list-group-item"><b>Email:</b> <?= htmlspecialchars($u['email']); ?></li>
                        <li class="list-group-item"><b>Role:</b> <?= htmlspecialchars($u['role']); ?></li>
                        <li class="list-group-item"><b>Created At:</b> <?= date('d-m-Y H:i:s', strtotime($u['created_at'])); ?></li>
                        <li class="list-group-item"><b>Updated At:</b> <?= date('d-m-Y H:i:s', strtotime($u['updated_at'])); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
