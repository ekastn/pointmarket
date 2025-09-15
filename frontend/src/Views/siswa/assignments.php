<?php 
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-tasks',
  'title' => 'Tugas Saya',
]);
?>

<!-- Assignments List -->
<div class="row pm-section">
    <?php if (!empty($assignments)): ?>
        <?php foreach ($assignments as $a): ?>
            <div class="col-md-6 col-lg-4 mb-4">
              <div class="card h-100">
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($a['status'] ?? ''); ?></span>
                    <small class="text-muted">Poin: <?php echo htmlspecialchars($a['reward_points'] ?? 0); ?></small>
                  </div>
                  <h5 class="card-title mb-1"><?php echo htmlspecialchars($a['title'] ?? ''); ?></h5>
                  <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars($a['description'] ?? ''); ?></p>
                  <div class="mb-2 small text-muted"><i class="fas fa-calendar me-1"></i>Jatuh tempo: <?php echo !empty($a['due_date']) ? htmlspecialchars(date('Y-m-d', strtotime($a['due_date']))) : '-'; ?></div>
                  <div class="d-flex justify-content-between align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-fill" onclick="startAssignment(<?php echo (int)($a['id'] ?? 0); ?>)"><i class="fas fa-play me-1"></i> Mulai</button>
                    <button class="btn btn-sm btn-success flex-fill" onclick="submitAssignment(<?php echo (int)($a['id'] ?? 0); ?>, '<?php echo htmlspecialchars($a['title'] ?? ''); ?>')"><i class="fas fa-upload me-1"></i> Submit</button>
                  </div>
                </div>
              </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <?php $renderer->includePartial('components/partials/empty_state', [
                'icon' => 'fas fa-tasks',
                'title' => 'Tidak ada tugas',
                'subtitle' => 'Tugas baru akan muncul di sini saat guru membuatnya.',
            ]); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Assignment Submission Modal -->
<div class="modal fade submission-modal" id="submissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submissionModalTitle">
                    <i class="fas fa-upload me-2"></i>Submit Tugas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="submissionModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Removed unused filter handlers for cleaner UI

    async function startAssignment(assignmentId) {
        if (!confirm('Yakin mulai tugas ini?')) {
            return;
        }

        const response = await fetch(API_BASE_URL + '/api/v1/assignments/' + assignmentId + '/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + JWT_TOKEN
            },
            body: JSON.stringify({})
        });
        const data = await response.json();

        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    }

    function submitAssignment(assignmentId, assignmentTitle) {
        document.getElementById('submissionModalTitle').innerHTML = `
            <i class="fas fa-upload me-2"></i>Submit: ${assignmentTitle}
        `;
        
        document.getElementById('submissionModalBody').innerHTML = `
            <form id="submissionForm">
                <div class="mb-3">
                    <label for="submissionText" class="form-label">Jawaban Kamu</label>
                    <textarea class="form-control" id="submissionText" rows="6" 
                              placeholder="Tulis jawaban/solusi tugas kamu di sini..."
                              required></textarea>
                    <div class="form-text">Tulis jawaban lengkap kamu. (Lampiran belum tersedia di demo)</div>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Demo Notice:</strong> Ini masih demo. Nantinya AI akan menyediakan:
                    <ul class="mb-0 mt-2">
                        <li><strong>Real-time NLP Analysis:</strong> Cek grammar dan analisis konten saat kamu mengetik</li>
                        <li><strong>Intelligent Scoring:</strong> Penilaian sesuai konteks dan ketentuan tugas</li>
                        <li><strong>Detailed Feedback:</strong> Saran detail untuk perbaikan</li>
                        <li><strong>Draft Management:</strong> Auto-save dan tracking revisi</li>
                    </ul>
                    <small class="text-muted mt-2 d-block">Submit sekarang akan dapat nilai AI simulasi untuk keperluan demo.</small>
                </div>
            </form>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-success" onclick="confirmSubmission(${assignmentId})">
                    <i class="fas fa-upload me-1"></i> Submit Assignment
                </button>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('submissionModal'));
        modal.show();
    }

    async function confirmSubmission(assignmentId) {
        const submissionText = document.getElementById('submissionText').value.trim();
        
        if (!submissionText) {
            alert('Isi jawaban dulu ya.');
            return;
        }

        if (!confirm('Yakin submit tugas ini? Setelah submit nggak bisa diubah.')) {
            return;
        }

        // Show loading
        document.getElementById('submissionModalBody').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                <p>Submitting your assignment...</p>
            </div>
        `;

        const response = await fetch(API_BASE_URL + '/api/v1/assignments/' + assignmentId + '/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + JWT_TOKEN
            },
            body: JSON.stringify({ submission: submissionText })
        });
        const data = await response.json();

        if (data.success) {
            document.getElementById('submissionModalBody').innerHTML = `
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <h5>Tugas berhasil di-submit!</h5>
                        <p>Nilai AI (simulasi): <strong>${data.score}</strong></p>
                        <div class="alert alert-info mt-3 mb-3">
                            <small>
                                <i class="fas fa-robot me-1"></i>
                                <strong>Demo Mode:</strong> Nilai ini hanya simulasi. 
                                Nanti AI NLP kami akan analisis konten kamu (grammar, struktur, relevansi) dan kasih feedback detail.
                            </small>
                        </div>
                        <p class="mb-0">${data.message}</p>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh Halaman
                        </button>
                    </div>
                `;
        } else {
            document.getElementById('submissionModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error: ${data.message}
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                `;
        }
    }

    function viewAssignmentDetails(assignmentId) {
        // This would open a detailed view of the assignment
        // For now, we'll just show an alert
        alert('Detail tugas - Fitur segera hadir!');
    }

    // Auto-refresh every 5 minutes to update due dates and overdue status
    setInterval(() => {
        // Only refresh if there are pending assignments
        const pendingCards = document.querySelectorAll('.assignment-card.not-started, .assignment-card.in-progress');
        if (pendingCards.length > 0) {
            location.reload();
        }
    }, 300000); // 5 minutes
</script>
