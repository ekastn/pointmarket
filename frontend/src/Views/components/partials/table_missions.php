<?php
/**
 * This view defines the configuration for the missions table
 * and renders the reusable table component.
 */
$columns = [
    ['label' => 'ID', 'key' => 'id'],
    ['label' => 'Title', 'key' => 'title'],
    ['label' => 'Description', 'key' => 'description', 'formatter' => fn($val) => $val ?? '-'],
    ['label' => 'Reward Points', 'key' => 'reward_points', 'formatter' => fn($val) => $val ?? '-'],
    ['label' => 'Created At', 'key' => 'created_at', 'formatter' => fn($val) => date('d M Y H:i', strtotime($val))],
    ['label' => 'Updated At', 'key' => 'updated_at', 'formatter' => fn($val) => date('d M Y H:i', strtotime($val))]
];

$actions = [
    [
        'label' => 'Edit',
        'class' => 'warning edit-mission-btn',
        'condition' => fn() => $role === 'admin',
        'attributes' => fn($row) => [
            'data-id' => $row['id'],
            'data-title' => htmlspecialchars($row['title']),
            'data-description' => htmlspecialchars($row['description'] ?? ''),
            'data-reward-points' => htmlspecialchars($row['reward_points'] ?? ''),
            'data-metadata' => json_encode($row['metadata']),
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#missionModal'
        ]
    ],
    [
        'label' => 'Delete',
        'class' => 'danger delete-mission-btn',
        'condition' => fn() => $role === 'admin',
        'attributes' => fn($row) => ['data-id' => $row['id']]
    ]
];

$renderer->includePartial('components/partials/table', [
    'columns' => $columns,
    'actions' => $actions,
    'data' => $missions,
    'empty_message' => 'No missions found.'
]);
?>

