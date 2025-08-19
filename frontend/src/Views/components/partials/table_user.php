<?php
/**
 * This view defines the configuration for the user table
 * and renders the reusable table component.
 *
 * The required variables are passed from the controller:
 * @var array $users The user data.
 * @var int $page The current page number.
 * @var int $total_pages The total number of pages.
 * @var int $total_data The total number of users.
 * @var int $start The starting record number.
 * @var int $end The ending record number.
 * @var string $search The search query.
 * @var string $role The role filter.
 */

$columns = [
    ['label' => 'No', 'type' => 'index'],
    ['label' => 'Username', 'key' => 'username'],
    ['label' => 'Email', 'key' => 'email'],
    [
        'label' => 'Role',
        'key' => 'role',
        'formatter' => function($role) {
            $role_class = 'info';
            if ($role === 'admin') $role_class = 'warning';
            if ($role === 'guru') $role_class = 'danger';
            if ($role === 'siswa') $role_class = 'success';
            return "<span class=\"badge bg-{$role_class}\">" . htmlspecialchars($role) . "</span>";
        }
    ],
    [
        'label' => 'Waktu Dibuat',
        'key' => 'created_at',
        'formatter' => fn($date) => date('d-m-Y H:i', strtotime($date))
    ]
];

$actions = [
    [
        'label' => 'Detail',
        'icon' => 'fas fa-eye',
        'class' => 'btn-info',
        'attributes' => fn($row) => [
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modalDetail' . $row['id']
        ]
    ],
    [
        'label' => 'Edit',
        'icon' => 'fas fa-edit',
        'class' => 'btn-warning btn-edit-user',
        'attributes' => fn($row) => [
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modalEditUser',
            'data-user-id' => $row['id'],
            'data-user-name' => htmlspecialchars($row['name']),
            'data-user-username' => htmlspecialchars($row['username']),
            'data-user-email' => htmlspecialchars($row['email']),
            'data-user-role' => htmlspecialchars($row['role'])
        ]
    ],
    [
        'label' => 'Hapus',
        'icon' => 'fas fa-trash',
        'class' => 'btn-danger btn-hapus',
        'attributes' => fn($row) => ['data-user-id' => $row['id']],
        'condition' => fn() => isset($_SESSION['user_data']) && $_SESSION['user_data']['role'] === 'admin'
    ]
];

$pagination = [
    'current_page' => $page,
    'total_pages' => $total_pages,
    'total_records' => $total_data,
    'start_record' => $start,
    'end_record' => $end,
    'base_params' => [
        'search' => $search ?? '',
        'role' => $role ?? ''
    ]
];

$renderer->includePartial('components/partials/table', [
    'columns' => $columns,
    'actions' => $actions,
    'data' => $users,
    'pagination' => $pagination,
    'empty_message' => 'No users found.'
]);

?>

<!-- 
    The user detail modals that were here previously have been kept for now to avoid breaking changes. 
    For true reusability, these should be moved to the main view file that includes this partial, 
    for example, `src/Views/admin/users.php`.
-->
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
<?php endforeach;
