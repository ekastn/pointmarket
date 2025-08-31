<?php
// $students, $programs, $filters, $pagination
?>
<div class="container-fluid">
    <?php 
        // Right-side: filters + add/edit via modal is inline per-row
        ob_start();
    ?>
        <form method="GET" class="d-flex gap-2 align-items-center">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width: 220px" placeholder="Cari nama/NIM/email" value="<?= htmlspecialchars($filters['search']); ?>">
            <select name="program_id" class="form-select form-select-sm" style="max-width: 220px">
                <option value="">Semua Prodi</option>
                <?php foreach ($programs as $p): ?>
                    <option value="<?= (int)$p['id']; ?>" <?= ($filters['program_id'] == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="cohort_year" class="form-control form-control-sm" style="max-width: 140px" placeholder="Angkatan" value="<?= htmlspecialchars($filters['cohort_year']); ?>">
            <select name="status" class="form-select form-select-sm" style="max-width: 160px">
                <option value="">Semua Status</option>
                <?php foreach (["active"=>"Aktif","leave"=>"Cuti","graduated"=>"Lulus","dropped"=>"Dropout"] as $k=>$v): ?>
                    <option value="<?= $k; ?>" <?= ($filters['status'] === $k) ? 'selected' : '' ?>><?= $v; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-search"></i></button>
        </form>
    <?php
        $right = ob_get_clean();
        $renderer->includePartial('components/partials/page_title', [
            'icon' => 'fas fa-user-graduate',
            'title' => 'Data Siswa',
            'right' => $right,
        ]);
    ?>

    <?php
        $columns = [
            ['label' => '#', 'key' => 'index', 'type' => 'index'],
            ['label' => 'Nama', 'key' => 'name'],
            ['label' => 'Email', 'key' => 'email'],
            ['label' => 'NIM', 'key' => 'student_id'],
            ['label' => 'Program', 'key' => 'program_name', 'formatter' => function($val, $row){ return htmlspecialchars($row['program']['name'] ?? ''); }],
            ['label' => 'Angkatan', 'key' => 'cohort_year', 'formatter' => function($v){ return htmlspecialchars($v ?? '-'); }],
            ['label' => 'Status', 'key' => 'status', 'formatter' => function($v){
                $map = ['active'=>'Aktif','leave'=>'Cuti','graduated'=>'Lulus','dropped'=>'Dropout'];
                return htmlspecialchars($map[$v] ?? $v);
            }],
        ];

        $actions = [
            [
                'label' => 'Edit',
                'icon' => 'fas fa-edit',
                'class' => 'btn-primary btn-edit-student',
                'attributes' => function($row) {
                    return [
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#modalEditStudent',
                        'data-user-id' => $row['user_id'],
                        'data-student-id' => $row['student_id'],
                        'data-program-id' => $row['program']['id'] ?? '',
                        'data-cohort-year' => $row['cohort_year'] ?? '',
                        'data-status' => $row['status'],
                    ];
                }
            ],
        ];

        $renderer->includePartial('components/partials/table', [
            'columns' => $columns,
            'actions' => $actions,
            'data' => $students,
            'pagination' => $pagination,
            'empty_message' => 'Belum ada data siswa',
        ]);
    ?>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="modalEditStudent" tabindex="-1" aria-labelledby="modalEditStudentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditStudentLabel">Edit Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-student-form">
                    <input type="hidden" id="edit-user-id">
                    <div class="mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" class="form-control" id="edit-student-id" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <select class="form-select" id="edit-program-id" required>
                            <option value="" disabled selected>Pilih Program</option>
                            <?php foreach ($programs as $p): ?>
                                <option value="<?= (int)$p['id']; ?>"><?= htmlspecialchars($p['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Angkatan</label>
                        <input type="number" class="form-control" id="edit-cohort-year" placeholder="contoh: 2024">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="edit-status">
                            <option value="active">Aktif</option>
                            <option value="leave">Cuti</option>
                            <option value="graduated">Lulus</option>
                            <option value="dropped">Dropout</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</div>

<script src="/public/assets/js/admin-students.js"></script>

