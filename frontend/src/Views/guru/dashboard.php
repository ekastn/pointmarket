<?php
// Ensure variables are defined to prevent PHP notices if not passed
$userProfile = $userProfile ?? ['name' => 'Guest', 'role' => 'guest'];
$teacherStats = $teacherStats ?? [];
$messages = $messages ?? [];

?>

<?php 
$right = '<div class="btn-group">'
       . '<button type="button" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download me-1"></i>Ekspor</button>'
       . '</div>';
$renderer->includePartial('components/partials/page_title', [
    'icon' => 'fas fa-tachometer-alt',
    'title' => 'Dashboard',
    'right' => $right,
]);
?>

<!-- Statistik Guru -->
<div class="row pm-section">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Assignments
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo htmlspecialchars($teacherStats['total_assignments'] ?? 0); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tasks fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Quizzes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo htmlspecialchars($teacherStats['total_quizzes'] ?? 0); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-question-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Courses
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo htmlspecialchars($teacherStats['total_courses'] ?? 0); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Students
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo htmlspecialchars($teacherStats['total_students'] ?? 0); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row pm-section">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="/guru/assignments/create" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>
                            Buat Tugas
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/guru/quizzes/create" class="btn btn-success w-100">
                            <i class="fas fa-plus me-2"></i>
                            Buat Kuis
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/courses" class="btn btn-info w-100">
                            <i class="fas fa-book-open me-2"></i>
                            Lihat Kelas
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/weekly-evaluations" class="btn btn-warning w-100">
                            <i class="fas fa-chart-line me-2"></i>
                            Monitoring Evaluasi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ringkasan Evaluasi Mingguan -->
<?php $evalSummary = $evalSummary ?? ['completed'=>0,'pending'=>0,'overdue'=>0,'total'=>0]; ?>
<div class="row pm-section">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-calendar-check me-2"></i> Ringkasan Evaluasi Mingguan
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Selesai</span>
                    <span class="badge bg-success"><?php echo (int)($evalSummary['completed'] ?? 0); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Menunggu</span>
                    <span class="badge bg-warning text-dark"><?php echo (int)($evalSummary['pending'] ?? 0); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Terlambat</span>
                    <span class="badge bg-danger"><?php echo (int)($evalSummary['overdue'] ?? 0); ?></span>
                </div>
                <hr />
                <div class="d-flex justify-content-between">
                    <strong>Total</strong>
                    <span><?php echo (int)($evalSummary['total'] ?? 0); ?></span>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="/weekly-evaluations" class="btn btn-sm btn-outline-primary">
                    Lihat Monitoring
                </a>
            </div>
        </div>
    </div>

    <!-- Tugas Terbaru -->
    <?php $recentAssignments = $recentAssignments ?? []; ?>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-stream me-2"></i> Tugas Terbaru
            </div>
            <div class="card-body">
                <?php if (empty($recentAssignments)): ?>
                    <div class="text-muted">Belum ada tugas.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentAssignments as $a): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($a['title'] ?? ''); ?></div>
                                    <small class="text-muted"><?php echo !empty($a['created_at']) ? date('Y-m-d', strtotime($a['created_at'])) : '-'; ?></small>
                                </div>
                                <div class="btn-group">
                                    <a href="/guru/assignments/<?php echo (int)($a['id'] ?? 0); ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <a href="/guru/assignments/<?php echo (int)($a['id'] ?? 0); ?>/submissions" class="btn btn-sm btn-outline-primary">Subm.</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer text-end">
                <a href="/guru/assignments" class="btn btn-sm btn-outline-secondary">Kelola Tugas</a>
            </div>
        </div>
    </div>

    <!-- Kuis Terbaru -->
    <?php $recentQuizzes = $recentQuizzes ?? []; ?>
    <div class="col-xl-4 col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-question me-2"></i> Kuis Terbaru
            </div>
            <div class="card-body">
                <?php if (empty($recentQuizzes)): ?>
                    <div class="text-muted">Belum ada kuis.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentQuizzes as $q): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($q['title'] ?? ''); ?></div>
                                    <small class="text-muted"><?php echo !empty($q['created_at']) ? date('Y-m-d', strtotime($q['created_at'])) : '-'; ?></small>
                                </div>
                                <div class="btn-group">
                                    <a href="/guru/quizzes/<?php echo (int)($q['id'] ?? 0); ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <a href="/quiz/<?php echo (int)($q['id'] ?? 0); ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer text-end">
                <a href="/guru/quizzes" class="btn btn-sm btn-outline-secondary">Kelola Kuis</a>
            </div>
        </div>
    </div>
</div>

<!-- Insight Per Kelas -->
<?php $courseInsights = $courseInsights ?? []; ?>
<div class="row pm-section">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-bar me-2"></i> Rata-rata AMS & MSLQ per Kelas</span>
                <small class="text-muted">Menampilkan hingga 10 kelas terakhir</small>
            </div>
            <div class="card-body">
                <canvas id="chart-ams-mslq" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 mt-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-column me-2"></i> Rata-rata VARK per Kelas</span>
                <small class="text-muted">Menampilkan hingga 10 kelas terakhir</small>
            </div>
            <div class="card-body">
                <canvas id="chart-vark" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (function(){
        const insights = <?php echo json_encode($courseInsights, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?> || [];
        if (!Array.isArray(insights) || insights.length === 0) { return; }

        const labels = insights.map(i => i.course_title || ('Kelas ' + i.course_id));
        const ams = insights.map(i => Number(i.avg_ams || 0));
        const mslq = insights.map(i => Number(i.avg_mslq || 0));

        const ctx1 = document.getElementById('chart-ams-mslq');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'AMS', data: ams, backgroundColor: '#36a2eb' },
                        { label: 'MSLQ', data: mslq, backgroundColor: '#4bc0c0' },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Skor' } } },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }

        const v = insights.map(i => Number((i.vark_avg && i.vark_avg.visual) || 0));
        const a = insights.map(i => Number((i.vark_avg && i.vark_avg.auditory) || 0));
        const r = insights.map(i => Number((i.vark_avg && i.vark_avg.reading) || 0));
        const k = insights.map(i => Number((i.vark_avg && i.vark_avg.kinesthetic) || 0));

        const ctx2 = document.getElementById('chart-vark');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Visual', data: v, backgroundColor: '#9966ff' },
                        { label: 'Auditory', data: a, backgroundColor: '#ffcd56' },
                        { label: 'Reading', data: r, backgroundColor: '#ff6384' },
                        { label: 'Kinesthetic', data: k, backgroundColor: '#36a2eb' },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Skor' } } },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }
    })();
</script>
