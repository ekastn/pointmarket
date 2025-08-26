<?php
$userProfile = $_SESSION['user_data'] ?? ['name' => 'Guest', 'role' => 'guest'];
$multimodal_threshold = $multimodal_threshold ?? 0;
?>

<?php $renderer->includePartial('components/partials/page_title', [
    'icon' => 'fas fa-cogs',
    'title' => 'Pengaturan Sistem',
]); ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-brain me-2"></i>
                    Multimodal learning style threshold
                </h5>
            </div>
            <div class="card-body">
                <form id="multimodal-form">
                    <div class="mb-3">
                        <label for="multimodal-threshold" class="form-label">Threshold: <span id="threshold-value"><?php echo htmlspecialchars($multimodal_threshold); ?></span></label>
                        <input type="range" class="form-range" id="multimodal-threshold" min="0" max="10" step="0.01" value="<?php echo htmlspecialchars($multimodal_threshold); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const thresholdInput = document.getElementById('multimodal-threshold');
    const thresholdValue = document.getElementById('threshold-value');

    thresholdInput.addEventListener('input', () => {
        thresholdValue.textContent = parseFloat(thresholdInput.value).toFixed(2);
    });

    document.getElementById('multimodal-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const threshold = parseFloat(thresholdInput.value).toFixed(2);
        fetch('/settings/multimodal', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ threshold: threshold })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ambang multimodal berhasil diperbarui');
            } else {
                alert('Gagal memperbarui ambang multimodal');
            }
        });
    });
</script>
