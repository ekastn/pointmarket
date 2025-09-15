<?php
$quizzes = $data['quizzes'] ?? [];
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-question-circle',
  'title' => 'Kelola Kuis',
  'right' => '<a href="/guru/quizzes/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Kuis Baru</a>'
]);
?>
<?php
$columns = [
    ['label' => '#', 'key' => 'id', 'type' => 'index'],
    ['label' => 'Judul', 'key' => 'title'],
    ['label' => 'Course ID', 'key' => 'course_id'],
    ['label' => 'Status', 'key' => 'status'],
    ['label' => 'Poin', 'key' => 'reward_points'],
    ['label' => 'Durasi', 'key' => 'duration_minutes'],
];
$actions = [
    [
        'label' => 'Edit',
        'icon' => 'fas fa-edit',
        'class' => 'btn-outline-primary',
        'attributes' => function($row){ return ['href' => '/guru/quizzes/' . (int)$row['id'] . '/edit']; }
    ],
    [
        'label' => 'Hapus',
        'icon' => 'fas fa-trash',
        'class' => 'btn-outline-danger',
        'attributes' => function($row){ return ['onclick' => "if(confirm('Hapus kuis ini?')){ fetch('/guru/quizzes/".(int)$row['id']."/delete',{method:'POST'}).then(()=>location.reload()); }", 'type' => 'button']; }
    ],
    [
        'label' => 'Detail',
        'icon' => 'fas fa-eye',
        'class' => 'btn-outline-secondary',
        'attributes' => function($row){ return ['href' => '/quiz/' . (int)$row['id']]; }
    ],
];
$pagination = ['current_page'=>1, 'total_pages'=>1, 'total_records'=>count($quizzes), 'start_record'=>1, 'end_record'=>count($quizzes), 'base_params'=>[]];
$renderer->includePartial('components/partials/table', compact('columns','actions') + ['data' => $quizzes, 'pagination' => $pagination, 'empty_message' => 'Belum ada kuis.']);
?>
