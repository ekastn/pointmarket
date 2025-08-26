<?php
// Data for this view will be passed from the AssignmentsController
$user = $user ?? ['name' => 'Guest'];
$assignments = $assignments ?? [];
$stats = $stats ?? ['total_assignments' => 0, 'completed' => 0, 'in_progress' => 0, 'overdue' => 0, 'total_points' => 0, 'average_score' => 0];
$subjects = $subjects ?? [];
$pendingEvaluations = $pendingEvaluations ?? [];
$status_filter = $status_filter ?? 'all';
$subject_filter = $subject_filter ?? 'all';
?>

<?php 
$right = '<div class="btn-group">'
       . '<a href="/weekly-evaluations" class="btn btn-sm btn-outline-primary"><i class="fas fa-calendar-check"></i> Evaluasi Mingguan</a>'
       . '<a href="/progress" class="btn btn-sm btn-outline-info ms-2"><i class="fas fa-chart-line"></i> Progress Saya</a>'
       . '</div>';
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-tasks',
  'title' => 'Tugas Saya',
  'right' => $right,
]);
?>

<!-- Pending Weekly Evaluations Alert -->
<?php if (!empty($pendingEvaluations)): ?>
<div class="row pm-section">
    <div class="col-12">
        <div class="alert pending-alert">
            <h5><i class="fas fa-bell me-2"></i>Evaluasi Mingguan Belum Selesai</h5>
            <p>Yuk selesaikan <strong><?php echo count($pendingEvaluations); ?> evaluasi mingguan</strong> biar rekomendasi AI untuk tugas makin akurat.</p>
            <a href="/weekly-evaluations" class="btn btn-warning btn-sm">
                <i class="fas fa-calendar-check me-1"></i> Selesaikan Evaluasi
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Statistics Overview -->
<div class="row pm-section">
    <div class="col-md-3 mb-3">
        <div class="card stats-card text-center">
            <div class="card-body">
                <div class="progress-ring" style="background: linear-gradient(135deg, #d1edff 0%, #a8daff 100%); color: #0c63e4;">
                    <?php echo $stats['total_assignments']; ?>
                </div>
                <h6 class="mt-2 mb-0">Total Tugas</h6>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card text-center">
            <div class="card-body">
                <div class="progress-ring" style="background: linear-gradient(135deg, #d4edda 0%, #a3d9a4 100%); color: #155724;">
                    <?php echo $stats['completed']; ?>
                </div>
                <h6 class="mt-2 mb-0">Selesai</h6>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card text-center">
            <div class="card-body">
                <div class="progress-ring" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); color: #664d03;">
                    <?php echo $stats['in_progress']; ?>
                </div>
                <h6 class="mt-2 mb-0">Proses</h6>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card text-center">
            <div class="card-body">
                <div class="progress-ring" style="background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%); color: #721c24;">
                    <?php echo $stats['overdue']; ?>
                </div>
                <h6 class="mt-2 mb-0">Terlambat</h6>
            </div>
        </div>
    </div>
</div>

<!-- Performance Summary -->
<div class="row pm-section">
    <div class="col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <h6><i class="fas fa-trophy me-2 text-warning"></i>Total Poin Didapat</h6>
                <h3 class="text-primary"><?php echo number_format($stats['total_points'], 1); ?></h3>
                <small class="text-muted">Dari tugas yang selesai</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <h6><i class="fas fa-chart-line me-2 text-success"></i>Rata-rata Nilai</h6>
                <h3 class="text-success"><?php echo number_format($stats['average_score'], 1); ?></h3>
                <small class="text-muted">Per tugas</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filter-section pm-section">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h6 class="mb-2"><i class="fas fa-filter me-2"></i>Filter Tugas</h6>
            <div class="d-flex gap-2 flex-wrap">
                <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="not_started" <?php echo $status_filter === 'not_started' ? 'selected' : ''; ?>>Belum Mulai</option>
                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>Proses</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                </select>
                <select id="subjectFilter" class="form-select form-select-sm" style="width: auto;">
                    <option value="all" <?php echo $subject_filter === 'all' ? 'selected' : ''; ?>>Semua Mapel</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo htmlspecialchars($subject['subject']); ?>" 
                                <?php echo $subject_filter === $subject['subject'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['subject']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <h6 class="mb-2"><i class="fas fa-book me-2"></i>Mata Pelajaran</h6>
            <div class="d-flex flex-wrap">
                <?php foreach ($subjects as $subject): ?>
                    <span class="subject-tag bg-light text-dark">
                        <?php echo htmlspecialchars($subject['subject']); ?>
                        <span class="badge bg-primary ms-1"><?php echo $subject['completed_assignments']; ?>/<?php echo $subject['total_assignments']; ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Assignments List -->
<div class="row pm-section">
    <?php if (!empty($assignments)): ?>
        <?php foreach ($assignments as $assignment): ?>
            <?php $renderer->includePartial('components/partials/assignment_card', ['assignment' => $assignment]); ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <?php $renderer->includePartial('components/partials/empty_state', [
                'icon' => 'fas fa-tasks',
                'title' => 'Tidak ada tugas',
                'subtitle' => ($status_filter !== 'all' || $subject_filter !== 'all') 
                    ? 'Coba ubah filter untuk melihat tugas lainnya.'
                    : 'Tugas baru akan muncul di sini saat guru membuatnya.',
            ]); ?>
            <?php if ($status_filter !== 'all' || $subject_filter !== 'all'): ?>
                <div class="text-center mb-4">
                    <a href="/assignments" class="btn btn-primary">
                        <i class="fas fa-undo me-1"></i> Reset Filter
                    </a>
                </div>
            <?php endif; ?>
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
        // Filter change handlers
        document.getElementById('statusFilter').addEventListener('change', function() {
            updateFilters();
        });

        document.getElementById('subjectFilter').addEventListener('change', function() {
            updateFilters();
        });

        function updateFilters() {
            const status = document.getElementById('statusFilter').value;
            const subject = document.getElementById('subjectFilter').value;
            
            const params = new URLSearchParams();
            if (status !== 'all') params.append('status', status);
            if (subject !== 'all') params.append('subject', subject);
            
            const newUrl = '/assignments' + (params.toString() ? '?' + params.toString() : '');
            window.location.href = newUrl;
        }

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
                body: JSON.stringify({ submission_content: submissionText })
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
