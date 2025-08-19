<?php
/**
 * This view defines the configuration for the badges table
 * and renders the reusable table component.
 */
$columns = [
    ['label' => 'No', 'type' => 'index'],
    ['label' => 'Title', 'key' => 'title'],
    ['label' => 'Description', 'key' => 'description', 'formatter' => fn($val) => $val ?? '-'],
    [
        'label' => 'Criteria',
        'key' => 'criteria',
        'formatter' => fn($val) => '<pre>' . htmlspecialchars(json_encode($val, JSON_PRETTY_PRINT)) . '</pre>'
    ],
    ['label' => 'Repeatable', 'key' => 'repeatable', 'formatter' => fn($val) => $val ? 'Yes' : 'No'],
    ['label' => 'Created At', 'key' => 'created_at', 'formatter' => fn($val) => date('d-m-Y H:i', strtotime($val))]
];

$actions = [
    [
        'label' => 'Edit',
        'icon' => 'fas fa-edit',
        'class' => 'btn-warning btn-edit-badge',
        'attributes' => fn($row) => [
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modalEditBadge',
            'data-badge-id' => $row['id'],
            'data-badge-title' => htmlspecialchars($row['title']),
            'data-badge-description' => htmlspecialchars($row['description'] ?? ''),
            'data-badge-criteria' => htmlspecialchars(json_encode($row['criteria'])),
            'data-badge-repeatable' => $row['repeatable'] ? '1' : '0'
        ]
    ],
    [
        'label' => 'Delete',
        'icon' => 'fas fa-trash',
        'class' => 'btn-danger btn-delete-badge',
        'attributes' => fn($row) => ['data-badge-id' => $row['id']]
    ],
    [
        'label' => 'Award',
        'icon' => 'fas fa-trophy',
        'class' => 'btn-success btn-award-badge',
        'attributes' => fn($row) => [
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modalAwardBadge',
            'data-badge-id' => $row['id']
        ]
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
    'data' => $badges,
    'pagination' => $pagination,
    'empty_message' => 'No badges found.'
]);
?>

