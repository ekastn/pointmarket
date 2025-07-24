<?php
// Data for this view will be passed from the TeacherEvaluationMonitoringController
$user = $user ?? ['name' => 'Guest'];
$studentStatus = $studentStatus ?? [];
$weeklyOverview = $weeklyOverview ?? [];
$messages = $messages ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Student Evaluation Monitoring</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="exportData()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo $type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Current Week Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card status-card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                </h5>
                <h3 class="text-success">
                    <?php 
                    $completed = array_sum(array_column($studentStatus, 'completed_this_week'));
                    echo $completed;
                    ?>
                </h3>
                <p class="card-text">Completed This Week</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card status-card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                </h5>
                <h3 class="text-warning">
                    <?php 
                    $pending = array_sum(array_column($studentStatus, 'pending_this_week'));
                    echo $pending;
                    ?>
                </h3>
                <p class="card-text">Pending This Week</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card status-card text-center">
            <div class="card-body">
                <h5 class="card-title text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                </h5>
                <h3 class="text-danger">
                    <?php 
                    $overdue = array_sum(array_column($studentStatus, 'overdue_this_week'));
                    echo $overdue;
                    ?>
                </h3>
                <p class="card-text">Overdue This Week</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card status-card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">
                    <i class="fas fa-users fa-2x mb-2"></i>
                </h5>
                <h3 class="text-primary"><?php echo count($studentStatus); ?></h3>
                <p class="card-text">Total Students</p>
            </div>
        </div>
    </div>
</div>

<!-- Student Status Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student Evaluation Status - Week <?php echo date('W'); ?>/<?php echo date('Y'); ?></h5>
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" id="searchStudent" placeholder="Search students...">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="studentTable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>MSLQ Score</th>
                                <th>AMS Score</th>
                                <th>Last Evaluation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentStatus as $student): ?>
                            <tr class="student-row">
                                <td>
                                    <strong><?php echo htmlspecialchars($student['student_name']); ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo htmlspecialchars($student['student_email']); ?></small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($student['completed_this_week'] > 0): ?>
                                            <span class="badge status-badge completed">
                                                <?php echo $student['completed_this_week']; ?> Completed
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($student['pending_this_week'] > 0): ?>
                                            <span class="badge status-badge pending">
                                                <?php echo $student['pending_this_week']; ?> Pending
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($student['overdue_this_week'] > 0): ?>
                                            <span class="badge status-badge overdue">
                                                <?php echo $student['overdue_this_week']; ?> Overdue
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($student['mslq_score_this_week'] !== null): ?>
                                        <?php 
                                        $score = $student['mslq_score_this_week'];
                                        $scoreClass = $score >= 5.5 ? 'score-high' : ($score >= 4 ? 'score-medium' : 'score-low');
                                        ?>
                                        <span class="score-badge <?php echo $scoreClass; ?>">
                                            <?php echo number_format($score, 2); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student['ams_score_this_week'] !== null): ?>
                                        <?php 
                                        $score = $student['ams_score_this_week'];
                                        $scoreClass = $score >= 5.5 ? 'score-high' : ($score >= 4 ? 'score-medium' : 'score-low');
                                        ?>
                                        <span class="score-badge <?php echo $scoreClass; ?>">
                                            <?php echo number_format($score, 2); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student['last_evaluation']): ?>
                                        <small><?php echo date('d M Y', strtotime($student['last_evaluation'])); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewStudentDetail(<?php echo $student['student_id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Progress Overview -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Weekly Progress Overview</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($weeklyOverview)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Week/Year</th>
                                    <th>Questionnaire</th>
                                    <th>Completion Rate</th>
                                    <th>Average Score</th>
                                    <th>Status Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($weeklyOverview as $overview): ?>
                                <tr>
                                    <td><strong><?php echo $overview['week_number']; ?>/<?php echo $overview['year']; ?></strong></td>
                                    <td>
                                        <?php echo strtoupper($overview['questionnaire_type']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $completion_rate = $overview['total_count'] > 0 ? 
                                            ($overview['completed_count'] / $overview['total_count']) * 100 : 0;
                                        ?>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" style="width: <?php echo $completion_rate; ?>%">
                                                <?php echo number_format($completion_rate, 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($overview['average_score'] !== null): ?>
                                            <strong><?php echo $overview['average_score']; ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <span class="text-success"><?php echo $overview['completed_count']; ?> completed</span> |
                                            <span class="text-warning"><?php echo $overview['pending_count']; ?> pending</span> |
                                            <span class="text-danger"><?php echo $overview['overdue_count']; ?> overdue</span>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No evaluation data available</h5>
                        <p class="text-muted">Weekly evaluation data will appear here once students start completing evaluations.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>