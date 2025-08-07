<?php
// Tentukan jumlah data per halaman
$limit = 10;

// Ambil halaman saat ini dari URL, jika tidak ada, set ke 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Hitung total data
$total_data = count($users);

// Hitung total halaman
$total_pages = ceil($total_data / $limit);

// Hitung offset untuk query
$offset = ($page - 1) * $limit;

// Ambil data untuk halaman saat ini
$users = array_slice($users, $offset, $limit);

// Hitung data yang ditampilkan
$start = $offset + 1; // Data pertama yang ditampilkan
$end = min($offset + $limit, $total_data); // Data terakhir yang ditampilkan
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
        <?php $i = 1; ?>
        <?php
        // Cari data pengguna berdasarkan kata kunci dan role
        if (isset($_GET['search']) || isset($_GET['role'])) {
            $search = strtolower($_GET['search'] ?? '');  // Mengonversi kata kunci pencarian menjadi huruf kecil
            $role = $_GET['role'] ?? ''; // Mengambil role yang dipilih

            $users = array_filter($users, function ($data) use ($search, $role) {
                $matchesSearch = strpos(strtolower($data['username']), $search) !== false ||
                    strpos(strtolower($data['email']), $search) !== false;
                $matchesRole = empty($role) || strtolower($data['role']) === strtolower($role);

                return $matchesSearch && $matchesRole;
            });
            // Perbarui total data setelah pencarian
            $total_data = count($users); // Total data setelah filter

            // Hitung total halaman
            $total_pages = ceil($total_data / $limit); // Total halaman berdasarkan limit

            // Hitung offset untuk query
            $offset = ($page - 1) * $limit; // Offset untuk data yang diambil

            // Ambil data untuk halaman saat ini
            $users = array_slice($users, $offset, $limit); // Ambil data sesuai offset dan limit

            // Hitung data yang ditampilkan
            $start = $offset + 1; // Data pertama yang ditampilkan
            $end = min($offset + $limit, $total_data); // Data terakhir yang ditampilkan

            // Jika total data adalah 6, maka end akan menjadi 6
            if ($total_data < $limit) {
                $end = $total_data; // Set end ke total data jika kurang dari limit
            }
        }
        foreach ($users as $u) : ?>
            <tr>
                <td><?= $i++; ?></td>
                <td><?= $u['username']; ?></td>
                <td><?= $u['email']; ?></td>
                <td>
                    <span class="badge bg-<?php
                                                if ($u['role'] === 'superadmin') {
                                                    echo 'success';
                                                } elseif ($u['role'] === 'admin') {
                                                    echo 'warning';
                                                } elseif ($u['role'] === 'dosen') {
                                                    echo 'danger';
                                                } else {
                                                    echo 'info';
                                                }
                                                ?>">
                        <?php echo $u['role']; ?>
                    </span>
                </td>
                <td><?= date('d-m-Y', strtotime($u['created_at'])); ?></td>
                <!-- <td><?= $u['created_at']; ?></td> -->
                <td>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalDetail<?php echo $u['id']; ?>"><i class="fas fa-eye"></i><span class="d-none d-md-inline"> Detail</span></button>
                </td>
                <?php if (isset($_SESSION['user_groups']) && in_array('superadmin', $_SESSION['user_groups'])) : ?>
                    <td>
                        <button href="/User/delete_User/<?= $u['id']; ?>" class="btn btn-danger btn-hapus"><i class="fas fa-trash"></i><span class="d-none d-md-inline"> Hapus</span></button>
                    </td>
                <?php endif ?>
            </tr>
        <?php endforeach; ?>
    </tbody>

</table>

<!-- Pagination -->
<nav class="d-flex justify-content-between align-items-center" aria-label="Page navigation">
    <!-- Menampilkan informasi jumlah data di kiri -->
    <div class="mb-3">
        Showing <?= $start; ?> to <?= $end; ?> of <?= $total_data; ?> entries
    </div>
    <!-- Menampilkan pagination di kanan -->
    <ul class="pagination mb-0">
        <!-- Tombol Previous -->
        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?= $page - 1; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>

        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
            <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?= $i; ?>">
                    <?= $i; ?>
                </a>
            </li>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?= $page + 1; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>

<!-- Modal Box Detail -->
<?php foreach ($users as $u) : ?>
    <div class="modal fade" id="modalDetail<?php echo $u['id']; ?>" tabindex="-1" aria-labelledby="modalDetailLabel<?php echo $u['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailLabel<?php echo $u['id']; ?>">Profil : <?= $u['username']; ?> </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                    <div class="col-lg-13">
                        <div class="card mb-3">
                            <div class="row g-0">
                                <!-- <div class="col-md-4">
                                    <img src="/img/admin.jpg" class="img-fluid rounded-start" alt="<?= $u['username']; ?>">
                                </div> -->
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <h5 class="card-title"><b>Nama :</b></h5>
                                            <li class="list-group-item">
                                                <h4><?= $u['username']; ?></h4>
                                            </li>
                                            <h5 class="card-title"><b>Email :</b></h5>
                                            <li class="list-group-item">
                                                <h4><?= $u['email']; ?></h4>
                                            </li>
                                            <li class="list-group-item">
                                                <span class="badge bg-<?php
                                                                            if ($u['role'] === 'superadmin') {
                                                                                echo 'success';
                                                                            } elseif ($u['role'] === 'admin') {
                                                                                echo 'warning';
                                                                            } elseif ($u['role'] === 'dosen') {
                                                                                echo 'danger';
                                                                            } else {
                                                                                echo 'info';
                                                                            }
                                                                            ?>">
                                                    <?php echo $u['role']; ?>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php endforeach; ?>
