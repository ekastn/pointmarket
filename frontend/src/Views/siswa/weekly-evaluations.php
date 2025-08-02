<?php
// Data for this view will be passed from the WeeklyEvaluationsController
$user = $user ?? ['name' => 'Guest', 'role' => 'guest'];
$weeklyProgress = $weeklyProgress ?? [];
$messages = $messages ?? [];

use function App\Helpers\formatDate;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-check me-2"></i>
        Weekly Evaluations
    </h1>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($type); ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Weekly Evaluation Progress</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($weeklyProgress)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Questionnaire</th>
                                    <th>MSLQ Score</th>
                                    <th>AMS Score</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Completed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($weeklyProgress as $progress): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($progress['year']); ?></td>
                                        <td><?php echo htmlspecialchars($progress['week_number']); ?></td>
                                        <td><?php echo htmlspecialchars($progress['questionnaire_name']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($progress['mslq_score'], 1)); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($progress['ams_score'], 1)); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $progress['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo htmlspecialchars(ucfirst($progress['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(formatDate($progress['due_date'])); ?></td>
                                        <td><?php echo $progress['completed_at'] ? htmlspecialchars(formatDate($progress['completed_at'])) : 'N/A'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>No weekly evaluation data available yet.
                        <p class="mt-2 mb-0">Complete your weekly assessments to see your progress here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
