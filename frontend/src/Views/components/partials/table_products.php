<?php
/**
 * This view defines the configuration for the products table
 * and renders the reusable table component.
 */

$columns = [
    ['label' => 'No', 'type' => 'index'],
    ['label' => 'Name', 'key' => 'name'],
    ['label' => 'Description', 'key' => 'description', 'formatter' => fn($val) => $val ?? '-'],
    ['label' => 'Price', 'key' => 'points_price'],
    ['label' => 'Type', 'key' => 'type'],
    ['label' => 'Category', 'key' => 'category_name', 'formatter' => fn($val) => $val ?? '-'],
    ['label' => 'Stock', 'key' => 'stock_quantity', 'formatter' => fn($val) => $val ?? '-'],
    ['label' => 'Active', 'key' => 'is_active', 'formatter' => fn($val) => $val ? 'Yes' : 'No'],
    ['label' => 'Created At', 'key' => 'created_at', 'formatter' => fn($val) => date('d-m-Y H:i', strtotime($val))]
];

$actions = [
    [
        'label' => 'Edit',
        'icon' => 'fas fa-edit',
        'class' => 'btn-warning btn-edit-product',
        'attributes' => fn($row) => [
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modalEditProduct',
            'data-product-id' => $row['id'],
            'data-product-name' => htmlspecialchars($row['name']),
            'data-product-description' => htmlspecialchars($row['description'] ?? ''),
            'data-product-points-price' => htmlspecialchars($row['points_price']),
            'data-product-type' => htmlspecialchars($row['type']),
            'data-product-stock-quantity' => htmlspecialchars($row['stock_quantity'] ?? ''),
            'data-product-is-active' => $row['is_active'] ? '1' : '0',
            'data-product-metadata' => json_encode($row['metadata'])
        ]
    ],
    [
        'label' => 'Delete',
        'icon' => 'fas fa-trash',
        'class' => 'btn-danger btn-delete-product',
        'attributes' => fn($row) => ['data-product-id' => $row['id']]
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
    'data' => $products,
    'pagination' => $pagination,
    'empty_message' => 'No products found.'
]);
?>

