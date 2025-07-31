<?php
// Data for this view will be passed from the MaterialsController
$user = $user ?? ['name' => 'Guest', 'role' => 'siswa'];
$materials = $materials ?? [];
$messages = $messages ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-book me-2"></i>Study Materials</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($user['role'] === 'guru' || $user['role'] === 'admin'): ?>
            <a href="/materials/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Material
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($type); ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="row">
    <?php if (!empty($materials)): ?>
        <?php foreach ($materials as $material): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($material['title']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($material['subject']); ?></h6>
                        <p class="card-text"><?php echo htmlspecialchars($material['description']); ?></p>
                        <p class="card-text"><small class="text-muted">Type: <?php echo htmlspecialchars($material['file_type'] ?? 'N/A'); ?></small></p>
                        <p class="card-text"><small class="text-muted">Status: <?php echo htmlspecialchars(ucfirst($material['status'])); ?></small></p>
                        
                        <?php if ($material['file_path']): ?>
                            <a href="<?php echo htmlspecialchars($material['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-eye me-1"></i>View Material
                            </a>
                        <?php endif; ?>

                        <?php if ($user['role'] === 'guru' || $user['role'] === 'admin'): ?>
                            <a href="/materials/edit/<?php echo htmlspecialchars($material['id']); ?>" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteMaterial(<?php echo htmlspecialchars($material['id']); ?>)">
                                <i class="fas fa-trash-alt me-1"></i>Delete
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center" role="alert">
                <h4 class="alert-heading">No Study Materials Available</h4>
                <p>There are no study materials uploaded yet. Please check back later.</p>
                <?php if ($user['role'] === 'guru' || $user['role'] === 'admin'): ?>
                    <hr>
                    <p class="mb-0">
                        <a href="/materials/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Upload Your First Material
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteMaterial(id) {
    if (confirm('Are you sure you want to delete this material?')) {
        fetch('/materials/delete/' + id, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('jwt_token')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Material deleted successfully!');
                location.reload();
            } else {
                alert('Failed to delete material: ' + (data.error || data.message));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error. Failed to delete material.');
        });
    }
}
</script>
