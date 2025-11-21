<div class="container-fluid">
    <?php
    $renderer->includePartial('components/partials/page_title', [
        'icon' => 'fas fa-user',
        'title' => 'Detail Pengguna'
    ]);
    ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Informasi Pengguna</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        <img src="<?= htmlspecialchars($userDetails['avatar'] ?? '/public/assets/img/avatar.png') ?>" alt="Avatar" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        <h4 class="mt-3"><?= htmlspecialchars($userDetails['name']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($userDetails['username']) ?></p>
                    </div>
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Email</th>
                                <td><?= htmlspecialchars($userDetails['email']) ?></td>
                            </tr>
                            <tr>
                                <th>Role</th>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($userDetails['role']) ?></span></td>
                            </tr>
                            <tr>
                                <th>Total Poin</th>
                                <td><?= htmlspecialchars($userDetails['points']['total_points']) ?></td>
                            </tr>
                            <tr>
                                <th>Bio</th>
                                <td><?= nl2br(htmlspecialchars($userDetails['bio'] ?? 'N/A')) ?></td>
                            </tr>
                            <tr>
                                <th>Waktu Dibuat</th>
                                <td><?= date('d-m-Y H:i', strtotime($userDetails['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <th>Waktu Diperbarui</th>
                                <td><?= date('d-m-Y H:i', strtotime($userDetails['updated_at'])) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
