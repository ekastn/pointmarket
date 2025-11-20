<?php
$assignments = $data['assignments'] ?? [];
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-tasks',
  'title' => 'Kelola Tugas',
  'right' => '<a href="/guru/assignments/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tugas Baru</a>'
]);
?>
<?php
$columns = [
    ['label' => '#', 'key' => 'id', 'type' => 'index'],
    ['label' => 'Judul', 'key' => 'title'],
    ['label' => 'Course', 'key' => 'course_title'],
    ['label' => 'Due', 'key' => 'due_date', 'formatter' => function($v){ return $v ? date('Y-m-d', strtotime($v)) : '-'; }],
    ['label' => 'Status', 'key' => 'status'],
    ['label' => 'Poin', 'key' => 'reward_points'],
];
$actions = [
    [
        'label' => 'Edit',
        'icon' => 'fas fa-edit',
        'class' => 'btn-outline-primary',
        'attributes' => function($row){ return ['href' => '/guru/assignments/' . (int)$row['id'] . '/edit']; }
    ],
    [
        'label' => 'Submissions',
        'icon' => 'fas fa-list',
        'class' => 'btn-outline-secondary',
        'attributes' => function($row){ return ['href' => '/guru/assignments/' . (int)$row['id'] . '/submissions']; }
    ],
    [
        'label' => 'Hapus',
        'icon' => 'fas fa-trash',
        'class' => 'btn-outline-danger',
        'attributes' => function($row){ return ['onclick' => "if(confirm('Hapus tugas ini?')){ fetch('/guru/assignments/".(int)$row['id']."/delete',{method:'POST'}).then(()=>location.reload()); }", 'type' => 'button']; }
    ],
];
$pagination = ['current_page'=>1, 'total_pages'=>1, 'total_records'=>count($assignments), 'start_record'=>1, 'end_record'=>count($assignments), 'base_params'=>[]];
$renderer->includePartial('components/partials/table', compact('columns','actions') + ['data' => $assignments, 'pagination' => $pagination, 'empty_message' => 'Belum ada tugas.']);
?>