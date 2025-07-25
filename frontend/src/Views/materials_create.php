<?php
// Data for this view will be passed from the MaterialsController
$user = $user ?? ['name' => 'Guest', 'role' => 'siswa'];
$messages = $messages ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-plus me-2"></i>Add New Study Material</h1>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($type); ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Material Details</h5>
    </div>
    <div class="card-body">
        <form action="/materials/create" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="file_path" class="form-label">File Path (URL or relative path)</label>
                <input type="text" class="form-control" id="file_path" name="file_path">
                <div class="form-text">e.g., /assets/docs/material.pdf or https://example.com/video.mp4</div>
            </div>
            <div class="mb-3">
                <label for="file_type" class="form-label">File Type</label>
                <select class="form-select" id="file_type" name="file_type">
                    <option value="">Select Type</option>
                    <option value="pdf">PDF</option>
                    <option value="video">Video</option>
                    <option value="audio">Audio</option>
                    <option value="document">Document</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload me-2"></i>Upload Material
            </button>
            <a href="/materials" class="btn btn-secondary ms-2">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
        </form>
    </div>
</div>
