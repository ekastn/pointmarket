<?php
// Data for this view will be passed from the ProgressController
$user = $user ?? ['name' => 'Guest', 'role' => 'siswa'];
$assignmentStats = $assignmentStats ?? null;
$recentActivities = $recentActivities ?? [];
$weeklyProgress = $weeklyProgress ?? [];
$questionnaireStats = $questionnaireStats ?? [];
$nlpStats = $nlpStats ?? null;
$varkResult = $varkResult ?? null;
$messages = $messages ?? [];

// Helper function to format date (ideally in a utility file or passed from controller)
if (!function_exists('formatDate')) {
    function formatDate($dateString) {
        if (!$dateString) return 'N/A';
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
    }
}

// Helper function for VARK tips (assuming it's loaded elsewhere, e.g., in main layout or a global helper)
// if (!function_exists('getVARKLearningTips')) { /* ... */ }
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

    <!-- Questionnaire Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Questionnaire Statistics</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($questionnaireStats)): ?>
                        <div class="row">
                            <?php foreach ($questionnaireStats as $stat): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded h-100">
                                        <h6><?php echo htmlspecialchars(strtoupper($stat['type'])); ?> (<?php echo htmlspecialchars($stat['name']); ?>)</h6>
                                        <p class="mb-1">Completed: <strong><?php echo htmlspecialchars($stat['total_completed']); ?></strong></p>
                                        <p class="mb-1">Average Score: <strong><?php echo htmlspecialchars(number_format($stat['average_score'], 1)); ?></strong></p>
                                        <p class="mb-1">Best Score: <strong><?php echo htmlspecialchars(number_format($stat['best_score'], 1)); ?></strong></p>
                                        <p class="mb-1">Last Completed: <strong><?php echo htmlspecialchars(formatDate($stat['last_completed'])); ?></strong></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No questionnaire statistics available yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- NLP Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-brain me-2"></i>NLP Analysis Statistics</h5>
                </div>
                <div class="card-body">
                    <?php if ($nlpStats && $nlpStats['overall'] && $nlpStats['overall']['total_analyses'] > 0): ?>
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-primary">Total Analyses</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars($nlpStats['overall']['total_analyses']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-success">Average Score</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars(number_format($nlpStats['overall']['average_score'], 1)); ?>%</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-info">Best Score</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars(number_format($nlpStats['overall']['best_score'], 1)); ?>%</p>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-md-4">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-warning">Grammar Improvement</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars(number_format($nlpStats['overall']['grammar_improvement'], 1)); ?>%</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-secondary">Keyword Improvement</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars(number_format($nlpStats['overall']['keyword_improvement'], 1)); ?>%</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded mb-3">
                                    <h6 class="text-primary">Structure Improvement</h6>
                                    <p class="h4 mb-0"><?php echo htmlspecialchars(number_format($nlpStats['overall']['structure_improvement'], 1)); ?>%</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No NLP analysis statistics available yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- VARK Learning Style -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>VARK Learning Style</h5>
                </div>
                <div class="card-body">
                    <?php if ($varkResult): ?>
                        <?php 
                        $learningTips = getVARKLearningTips($varkResult['dominant_style']); 
                        ?>
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="text-primary">Your Learning Style Profile</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="<?php echo htmlspecialchars($learningTips['icon']); ?> fa-2x text-primary me-3"></i>
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($varkResult['learning_preference']); ?></h5>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($learningTips['description']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <h6>VARK Scores:</h6>
                                        <div class="small">
                                            <span class="badge bg-info me-1">Visual: <?php echo htmlspecialchars($varkResult['visual_score']); ?></span>
                                            <span class="badge bg-warning me-1">Auditory: <?php echo htmlspecialchars($varkResult['auditory_score']); ?></span>
                                            <br class="d-sm-none">
                                            <span class="badge bg-success me-1 mt-1">Reading: <?php echo htmlspecialchars($varkResult['reading_score']); ?></span>
                                            <span class="badge bg-danger me-1 mt-1">Kinesthetic: <?php echo htmlspecialchars($varkResult['kinesthetic_score']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <h6>Study Tips:</h6>
                                        <ul class="small mb-0">
                                            <?php foreach (array_slice($learningTips['study_tips'], 0, 3) as $tip): ?>
                                                <li><?php echo htmlspecialchars($tip); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Completed: <?php echo htmlspecialchars(date('d M Y H:i', strtotime($varkResult['completed_at']))); ?>
                                </small>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="<?php echo htmlspecialchars($learningTips['icon']); ?> fa-4x text-primary opacity-50 mb-3"></i>
                                <br>
                                <a href="/vark-assessment" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Retake Assessment
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <i class="fas fa-brain fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Learning Style Not Assessed</h6>
                            <p class="text-muted">Take the VARK assessment to discover your learning style preferences and get personalized study recommendations.</p>
                            <a href="/vark-assessment" class="btn btn-primary">
                                <i class="fas fa-brain me-1"></i>
                                Take VARK Assessment
                            </a>
                        </div>
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