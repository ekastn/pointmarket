<?php
// Data for the view will be passed from the DashboardController
// $userProfile, $adminCounts, $messages

// Ensure variables are defined to prevent PHP notices if not passed
$userProfile = $_SESSION['user_data'] ?? ['name' => 'Guest', 'role' => 'guest'];

// // AdminDashboardStatsDTO holds counts for the admin dashboard.
// type AdminDashboardStatsDTO struct {
// 	TotalUsers              int64 `json:"total_users"`
// 	TotalTeachers           int64 `json:"total_teachers"`
// 	TotalStudents           int64 `json:"total_students"`
// 	TotalCourses            int64 `json:"total_courses"`
// 	TotalPointsTransactions int64 `json:"total_points_transaction"`
// 	TotalProducts           int64 `json:"total_products"`
// 	TotalMissions           int64 `json:"total_missions"`
// 	TotalBadges             int64 `json:"total_badges"`
// }
//
$adminStats = $adminStats ?? [
    'total_users' => 0,
    'total_teachers' => 0,
    'total_students' => 0,
    'total_courses' => 0,
    'total_points_transaction' => 0,
    'total_products' => 0,
    'total_missions' => 0,
    'total_badges' => 0
];

$statsItems = [
    [ 'title' => 'Users', 'value' => $adminStats['total_users'], 'icon' => 'fas fa-users' ],
    [ 'title' => 'Teachers', 'value' => $adminStats['total_teachers'], 'icon' => 'fas fa-chalkboard-teacher' ],
    [ 'title' => 'Students', 'value' => $adminStats['total_students'], 'icon' => 'fas fa-user-graduate' ],
    [ 'title' => 'Courses', 'value' => $adminStats['total_courses'], 'icon' => 'fas fa-graduation-cap' ],
    [ 'title' => 'Points Transactions', 'value' => $adminStats['total_points_transaction'], 'icon' => 'fas fa-coins' ],
    [ 'title' => 'Products', 'value' => $adminStats['total_products'], 'icon' => 'fas fa-box-open' ],
    [ 'title' => 'Missions', 'value' => $adminStats['total_missions'], 'icon' => 'fas fa-trophy' ],
    [ 'title' => 'Badges', 'value' => $adminStats['total_badges'], 'icon' => 'fas fa-medal' ],
]

?>
<div class="container-fluid">

    <?php 
    $right = '<div class="btn-group">'
           . '<button type="button" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download me-1"></i>Ekspor</button>'
           . '</div>';
    $renderer->includePartial('components/partials/page_title', [
        'icon' => 'fas fa-tachometer-alt',
        'title' => 'Admin Dashboard',
        'right' => $right,
    ]);
    ?>

    <!-- Admin Stats -->
    <div class="row pm-section">
        <?php foreach ($statsItems as $item): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <?php $renderer->includePartial('components/partials/card_stats', $item); ?>
            </div>
        <?php endforeach; ?>
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
                        <div class="col mb-2">
                            <a href="/users" class="btn btn-primary w-100 h-100">
                                <i class="fas fa-users-cog me-2"></i>
                                Manage Users
                            </a>
                        </div>
                        <div class="col mb-2">
                            <a href="/reports" class="btn btn-success w-100 h-100">
                                <i class="fas fa-chart-bar me-2"></i>
                                View Reports
                            </a>
                        </div>
                        <div class="col mb-2">
                            <a href="/settings" class="btn btn-info w-100 h-100">
                                <i class="fas fa-cog me-2"></i>
                                System Settings
                            </a>
                        </div>
                        <div class="col mb-2">
                            <a href="/backup" class="btn btn-warning w-100 h-100">
                                <i class="fas fa-database me-2"></i>
                                Backup Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Evaluation Scheduler (Admin) -->
    <div class="row pm-section">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Weekly Evaluation Scheduler
                    </h5>
                    <?php
                    $isRunning = (bool)($schedulerStatus['running'] ?? false);
                    $isJobRunning = (bool)($schedulerStatus['job_running'] ?? false);
                    $nextRunRaw = $schedulerStatus['next_run'] ?? null;
                    $nextRun = '';
                    if ($nextRunRaw) {
                        $ts = is_numeric($nextRunRaw) ? (int)$nextRunRaw : strtotime((string)$nextRunRaw);
                        if ($ts) { $nextRun = date('D, d M Y H:i', $ts); }
                    }
                    ?>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-7 mb-3 mb-lg-0">
                            <div class="p-3 bg-light rounded border h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge <?= $isRunning ? 'bg-success' : 'bg-secondary' ?> me-2">
                                        <?= $isRunning ? 'Running' : 'Stopped' ?>
                                    </span>
                                    <?php if ($isJobRunning): ?>
                                        <span class="badge bg-info">
                                            <i class="fas fa-spinner fa-spin me-1"></i> Job Running
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="far fa-clock me-1"></i>
                                    Next run: <?= htmlspecialchars($nextRun ?: '—') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <?php if (!$isRunning): ?>
                                    <form action="/weekly-evaluations/start" method="post">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-play me-1"></i> Start
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="/weekly-evaluations/stop" method="post">
                                        <button type="submit" class="btn btn-outline-secondary">
                                            <i class="fas fa-stop me-1"></i> Stop
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations Trace (Admin) -->
    <div class="row pm-section">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-search me-2"></i>
                        Recommendations Trace
                    </h5>
                </div>
                <div class="card-body">
                    <form method="get" class="row g-2 align-items-center mb-3">
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="trace_q" placeholder="Search students by name/email/student ID" value="<?= htmlspecialchars($traceQuery ?? '') ?>" />
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-1"></i> Search</button>
                        </div>
                    </form>

                    <?php if (!empty($traceResults['data'])): ?>
                        <div class="table-responsive mb-3">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Student ID</th>
                                        <th>Program</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($traceResults['data'] as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['student_id'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['program']['name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['status'] ?? '') ?></td>
                                            <td>
                                                <form method="get" class="d-inline">
                                                    <input type="hidden" name="trace_q" value="<?= htmlspecialchars($traceQuery ?? '') ?>" />
                                                    <input type="hidden" name="trace_student_id" value="<?= htmlspecialchars($row['student_id'] ?? '') ?>" />
                                                    <button class="btn btn-sm btn-primary" type="submit">
                                                        <i class="fas fa-eye me-1"></i> View Trace
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif (!empty($traceQuery)): ?>
                        <div class="alert alert-warning">No students found for query "<?= htmlspecialchars($traceQuery) ?>".</div>
                    <?php endif; ?>

                    <?php if (!empty($traceError)): ?>
                        <div class="alert alert-danger">Trace error: <?= htmlspecialchars($traceError) ?></div>
                    <?php elseif (!empty($tracePayload['trace'])): ?>
                        <?php $t = $tracePayload['trace']; ?>
                        <div class="border rounded p-3">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <div><strong>Student:</strong> <?= htmlspecialchars($tracePayload['siswa_id'] ?? '') ?></div>
                                    <div><strong>State:</strong> <?= htmlspecialchars($tracePayload['current_state'] ?? '') ?></div>
                                </div>
                                <div class="col-sm-6">
                                    <div><strong>Timestamp:</strong> <?= htmlspecialchars($t['timestamp'] ?? '') ?></div>
                                    <div><strong>Source:</strong> <?= htmlspecialchars($t['source'] ?? '') ?></div>
                                </div>
                            </div>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th colspan="2">Q-Values</th></tr></thead>
                                    <tbody>
                                    <?php foreach (($t['q_values'] ?? []) as $ac => $q): ?>
                                        <tr><td><?= htmlspecialchars((string)$ac) ?></td><td><?= htmlspecialchars(number_format((float)$q, 4)) ?></td></tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php foreach (($t['cbf']['actions'] ?? []) as $a): ?>
                                <h6 class="mt-3">Action <?= htmlspecialchars((string)($a['action_code'] ?? '')) ?> (State pool: <?= (int)($a['pool_size_state'] ?? 0) ?>, Fallback: <?= (int)($a['pool_size_action_fallback'] ?? 0) ?>)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead><tr><th>Ref Type</th><th>Ref ID</th><th>Item State</th><th>CBF Score</th></tr></thead>
                                        <tbody>
                                            <?php foreach (($a['top_items'] ?? []) as $it): ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string)($it['ref_type'] ?? '')) ?></td>
                                                <td><?= htmlspecialchars((string)($it['ref_id'] ?? '')) ?></td>
                                                <td><?= htmlspecialchars((string)($it['item_state'] ?? '')) ?></td>
                                                <td><?= htmlspecialchars(number_format((float)($it['cbf_score'] ?? 0), 4)) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                            <h6 class="mt-3">Recent Rewards</h6>
                            <?php foreach (($t['rewards'] ?? []) as $rw): ?>
                                <div class="table-responsive mb-2">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead><tr><th colspan="3">Action <?= htmlspecialchars((string)($rw['action_code'] ?? '')) ?></th></tr></thead>
                                        <tbody>
                                        <tr><th style="width: 30%">Timestamp</th><th>State</th><th style="width: 15%">Reward</th></tr>
                                        <?php foreach (($rw['recent'] ?? []) as $r): ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string)($r['timestamp'] ?? '')) ?></td>
                                                <td><?= htmlspecialchars((string)($r['state'] ?? '')) ?></td>
                                                <td><?= htmlspecialchars(number_format((float)($r['reward'] ?? 0), 4)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($traceStudentId ?? '')): ?>
                        <div class="alert alert-warning">No trace available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
