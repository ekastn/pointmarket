<?php
/**
 * @var array $user
 * @var array $questionnaires
 * @var array $messages
 */
$user = $user ?? ['name' => 'Guest'];
$questionnaires = $questionnaires ?? [];
$messages = $messages ?? [];

$columns = [
    ['label' => 'ID', 'key' => 'id'],
    ['label' => 'Name', 'key' => 'name'],
    ['label' => 'Type', 'key' => 'type'],
    ['label' => 'Total Questions', 'key' => 'total_questions'],
    [
        'label' => 'Status',
        'key' => 'status',
        'formatter' => function($status) {
            $status_class = ($status === 'active') ? 'success' : 'secondary';
            return "<span class=\"badge bg-{$status_class}\">" . htmlspecialchars(ucfirst($status)) . "</span>";
        }
    ]
];

$actions = [
    [
        'label' => 'Edit',
        'icon' => 'fas fa-edit',
        'class' => 'btn-primary',
        'attributes' => fn($row) => [
            'href' => '/questionnaires/' . htmlspecialchars($row['id']) . '/edit'
        ]
    ],
    [
        'label' => 'Delete',
        'icon' => 'fas fa-trash',
        'class' => 'btn-danger delete-questionnaire-btn',
        'attributes' => fn($row) => [
            'data-id' => $row['id']
        ]
    ]
];

$pagination = [
    'current_page' => 1,
    'total_pages' => 1,
    'total_records' => count($questionnaires),
    'start_record' => 1,
    'end_record' => count($questionnaires),
    'base_params' => []
];

?>

<?php $renderer->includePartial('components/partials/page_title', [
    'icon' => 'fas fa-clipboard-list',
    'title' => 'Data Kuesioner',
    'right' => '<a href="/questionnaires/create" class="btn btn-success"><i class="fas fa-plus me-1"></i> Buat Kuesioner Baru</a>'
]); ?>

<?php
$renderer->includePartial('components/partials/table', [
    'columns' => $columns,
    'actions' => $actions,
    'data' => $questionnaires,
    'pagination' => $pagination,
    'empty_message' => 'Belum ada kuesioner. Klik "Buat Kuesioner Baru" untuk menambahkan.'
]);
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-questionnaire-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', async function() {
                const questionnaireId = this.dataset.id;
                if (confirm('Are you sure you want to delete this questionnaire? This action cannot be undone.')) {
                    try {
                        const response = await fetch(`/api/v1/questionnaires/${questionnaireId}`, {
                            method: 'DELETE',
                            headers: {
                                'Authorization': 'Bearer ' + JWT_TOKEN // Assuming JWT_TOKEN is globally available
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            alert('Questionnaire deleted successfully!');
                            location.reload(); // Reload the page to update the list
                        } else {
                            alert('Error deleting questionnaire: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Network error: Unable to connect to the server.');
                    }
                }
            });
        });
    });
</script>
