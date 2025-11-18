<?php
/**
 * This view defines the configuration for the courses table
 * and renders the reusable table component.
 */
$columns = [
    ['label' => 'No', 'type' => 'index'],
    ['label' => 'Title', 'key' => 'title'],
    ['label' => 'Slug', 'key' => 'slug'],
    ['label' => 'Description', 'key' => 'description', 'formatter' => fn($val) => $val ?? '-'],
    ['label' => 'Owner ID', 'key' => 'owner_id'],
    ['label' => 'Created At', 'key' => 'created_at', 'formatter' => fn($val) => date('d-m-Y H:i', strtotime($val))],
    ['label' => 'Updated At', 'key' => 'updated_at', 'formatter' => fn($val) => date('d-m-Y H:i', strtotime($val))]
];

$actions = [
    [
        'label' => 'Detail',
        'icon' => 'fas fa-eye',
        'class' => 'btn-primary',
        'attributes' => fn($row) => [
            'href' => '/courses/' . $row['slug'],
            'title' => 'Lihat detail kelas'
        ]
    ],
    [
        'label' => 'Edit',
        'icon' => 'fas fa-edit',
        'class' => 'btn-warning btn-edit-course',
        'attributes' => fn($row) => [
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modalEditCourse',
            'data-course-id' => $row['id'],
            'data-course-title' => htmlspecialchars($row['title']),
            'data-course-slug' => htmlspecialchars($row['slug']),
            'data-course-description' => htmlspecialchars($row['description'] ?? ''),
            'data-course-metadata' => json_encode($row['metadata'])
        ]
    ],
    [
        'label' => 'Delete',
        'icon' => 'fas fa-trash',
        'class' => 'btn-danger btn-delete-course',
        'attributes' => fn($row) => ['data-course-id' => $row['id']]
    ]
];

$pagination = [
    'current_page' => $page,
    'total_pages' => $total_pages,
    'total_records' => $total_data,
    'start_record' => $start,
    'end_record' => $end,
    'base_params' => $base_params ?? []
];

$renderer->includePartial('components/partials/table', [
    'columns' => $columns,
    'actions' => $actions,
    'data' => $courses,
    'pagination' => $pagination,
    'empty_message' => 'No courses found.'
]);
?>
