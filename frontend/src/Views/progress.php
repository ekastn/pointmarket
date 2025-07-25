<?php
// Data for this view will be passed from the ProgressController
$user = $user ?? ['name' => 'Guest', 'role' => 'siswa'];
$assignmentStats = $assignmentStats ?? null;
$recentActivities = $recentActivities ?? [];
$weeklyProgress = $weeklyProgress ?? [];
$messages = $messages ?? [];

// Helper function to format date (ideally in a utility file or passed from controller)
if (!function_exists('formatDate')) {
    function formatDate($dateString) {
        if (!$dateString) return 'N/A';
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chart-line me-2"></i>My Progress</h1>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($type); ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="card-title mb-2">
                            Hello, <?php echo htmlspecialchars($user['name']); ?>!
                        </h4>
                        <p class="card-text mb-0">
                            Here's an overview of your academic progress.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <i class="fas fa-chart-bar fa-4x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($user['role'] === 'siswa'): ?>
    <!-- Assignment Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Assignment Statistics</h5>
                </div>
                <div class="card-body">
                    <?php if ($assignmentStats): ?>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-primary">Total Assignments</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars($assignmentStats['total_assignments']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-success">Average Score</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars(number_format($assignmentStats['avg_score'], 1)); ?>%</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-info">Best Score</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars(number_format($assignmentStats['best_score'], 1)); ?>%</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-danger">Lowest Score</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars(number_format($assignmentStats['lowest_score'], 1)); ?>%</p>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-warning">High Scores (>= 80%)</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars($assignmentStats['high_scores']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-secondary">Late Submissions</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars($assignmentStats['late_submissions']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No assignment statistics available yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Evaluation Progress -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Weekly Evaluation Progress</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($weeklyProgress)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Week</th>
                                        <th>Year</th>
                                        <th>Questionnaire</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Completed At</th>
                                        <th>MSLQ Score</th>
                                        <th>AMS Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($weeklyProgress as $progress): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($progress['week_number']); ?></td>
                                            <td><?php echo htmlspecialchars($progress['year']); ?></td>
                                            <td><?php echo htmlspecialchars($progress['questionnaire_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    if ($progress['status'] === 'completed') echo 'success';
                                                    else if ($progress['status'] === 'pending') echo 'warning';
                                                    else if ($progress['status'] === 'overdue') echo 'danger';
                                                    else echo 'secondary';
                                                ?>">
                                                    <?php echo htmlspecialchars(ucfirst($progress['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars(formatDate($progress['due_date'])); ?></td>
                                            <td><?php echo htmlspecialchars(formatDate($progress['completed_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($progress['mslq_score'] !== null ? number_format($progress['mslq_score'], 1) : 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($progress['ams_score'] !== null ? number_format($progress['ams_score'], 1) : 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No weekly evaluation progress available yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentActivities)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentActivities as $activity): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($activity['action']); ?>:</strong> 
                                        <?php echo htmlspecialchars($activity['description']); ?>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars(formatDate($activity['created_at'])); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No recent activity to display.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: // For Teachers/Admins, show a different message or redirect ?>
    <div class="alert alert-warning text-center" role="alert">
        <h4 class="alert-heading">Access Denied</h4>
        <p>This page is primarily for student progress tracking. Please navigate to your respective dashboard.</p>
        <a href="/dashboard" class="btn btn-warning"><i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard</a>
    </div>
<?php endif; ?>
