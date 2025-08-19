<?php
/**
 * This view defines the configuration for the product categories table
 * and renders the reusable table component.
 */

$columns = [
    ['label' => 'No', 'type' => 'index'],
    ['label' => 'Name', 'key' => 'name'],
    ['label' => 'Description', 'key' => 'description', 'formatter' => fn($val) => $val ?? '-']
];

$actions = [
    [
        'label' => 'Edit',
        'icon' => 'fas fa-edit',
        'class' => 'btn-warning btn-edit-category',
        'attributes' => fn($row) => [
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modalEditCategory',
            'data-category-id' => $row['id'],
            'data-category-name' => htmlspecialchars($row['name']),
            'data-category-description' => htmlspecialchars($row['description'] ?? '')
        ]
    ],
    [
        'label' => 'Delete',
        'icon' => 'fas fa-trash',
        'class' => 'btn-danger btn-delete-category',
        'attributes' => fn($row) => ['data-category-id' => $row['id']]
    ]
];

// 3. Prepare Pagination Data
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
    'data' => $categories,
    'pagination' => $pagination,
    'empty_message' => 'No product categories found.'
]);
?>

