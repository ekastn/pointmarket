<?php
require_once __DIR__ . '/../../Helpers/VARKHelpers.php';
require_once __DIR__ . '/../../Helpers/DateHelpers.php';

// Data for this view will be passed from the ProfileController
$user = $user ?? ['name' => 'Guest', 'email' => 'N/A', 'username' => 'N/A', 'role' => 'siswa', 'avatar' => null];
$assignmentStats = $assignmentStats ?? null;
$questionnaireStats = $questionnaireStats ?? [];
$nlpStats = $nlpStats ?? null;
$varkResult = $varkResult ?? null;
$weeklyProgress = $weeklyProgress ?? [];
$recentActivities = $recentActivities ?? [];

use function App\Helpers\formatDate;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <img src="<?php echo htmlspecialchars($user['avatar'] ?? '/assets/img/default-avatar.png'); ?>" class="rounded-circle mb-3" alt="Avatar" style="width: 150px; height: 150px; object-fit: cover;">
                <h5 class="card-title mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                <p class="text-muted mb-0"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                <p class="text-muted"><small>@<?php echo htmlspecialchars($user['username']); ?></small></p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Edit Profile Information</h5>
            </div>
            <div class="card-body">
                <form action="/profile" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar URL</label>
                        <input type="text" class="form-control" id="avatar" name="avatar" value="<?php echo htmlspecialchars($user['avatar'] ?? ''); ?>">
                        <div class="form-text">Link to your profile picture (e.g., from Gravatar or an image hosting service).</div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Learning Insights -->
<?php if (!empty($questionnaireStats) || $varkResult): // Only show if there's some data ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <small><i class="fas fa-info-circle me-1"></i><strong>Demo Notice:</strong> The learning insights and recommendations shown below are generated based on basic scoring algorithms for demonstration purposes. In the full version, these will be powered by advanced AI analysis and machine learning models.</small>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Your Learning Insights
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php 
                        $learningInsights = [];
                        if (isset($questionnaireStats['mslq']) && $questionnaireStats['mslq']['total_completed'] > 0) {
                            if ($questionnaireStats['mslq']['average_score'] >= 4.0) {
                                $learningInsights[] = "🎓 Strong learning strategies and motivation";
                            } elseif ($questionnaireStats['mslq']['average_score'] >= 3.0) {
                                $learningInsights[] = "📚 Good learning approach with room for improvement";
                            } else {
                                $learningInsights[] = "🎯 Focus on developing better learning strategies";
                            }
                        }
                        if (isset($questionnaireStats['ams']) && $questionnaireStats['ams']['total_completed'] > 0) {
                            if ($questionnaireStats['ams']['average_score'] >= 4.0) {
                                $learningInsights[] = "⭐ High academic motivation";
                            } elseif ($questionnaireStats['ams']['average_score'] >= 3.0) {
                                $learningInsights[] = "💪 Moderate motivation levels";
                            } else {
                                $learningInsights[] = "🚀 Work on building academic motivation";
                            }
                        }
                        if ($varkResult) {
                            $learningInsights[] = "🧠 " . htmlspecialchars($varkResult['dominant_style']) . " learning style preference";
                        }
                        ?>
                        <?php if (!empty($learningInsights)): ?>
                            <?php foreach ($learningInsights as $insight): ?>
                                <div class="insight-item">
                                    <?php echo htmlspecialchars($insight); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info text-center">Complete your assessments to see personalized learning insights.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <h6 class="text-primary">💡 Recommendation</h6>
                            <ul class="small mb-0">
                                <?php if ($varkResult): ?>
                                    <?php $tips = \app\Helpers\getVARKLearningTips($varkResult['dominant_style']); ?>
                                    <?php foreach (array_slice($tips['study_tips'], 0, 3) as $tip): ?>
                                        <li><?php echo htmlspecialchars($tip); ?></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>Complete your assessments for personalized tips</li>
                                    <li>Start with VARK to discover your learning style</li>
                                    <li>Regular practice improves learning outcomes</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Performance Statistics -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Performance Statistics
                </h5>
            </div>
            <div class="card-body">
                <?php if ($assignmentStats && $assignmentStats['total_assignments'] > 0): ?>
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <h4 class="text-success"><?php echo htmlspecialchars(number_format($assignmentStats['best_score'], 1)); ?></h4>
                            <small class="text-muted">Best Score</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info"><?php echo htmlspecialchars(number_format($assignmentStats['avg_score'], 1)); ?></h4>
                            <small class="text-muted">Average Score</small>
                        </div>
                    </div>
                    <div class="progress progress-custom mb-2">
                        <div class="progress-bar bg-info" style="width: <?php echo htmlspecialchars(($assignmentStats['avg_score'] / 100) * 100); ?>%"></div>
                    </div>
                    <p class="small text-muted">
                        Based on <?php echo htmlspecialchars($assignmentStats['total_assignments']); ?> completed assignments
                    </p>
                <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No performance data yet</h6>
                        <p class="small text-muted">Complete assignments to see your statistics</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Weekly Evaluation Progress -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    Weekly Evaluations
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($weeklyProgress)): ?>
                    <div class="text-center mb-3">
                        <h4 class="text-warning"><?php echo htmlspecialchars(count($weeklyProgress)); ?></h4>
                        <small class="text-muted">Completed Evaluations</small>
                    </div>
                    <div class="row">
                        <?php 
                        $recentWeeks = array_slice($weeklyProgress, -4, 4, true);
                        foreach ($recentWeeks as $progress): 
                        ?>
                            <div class="col-6 mb-2">
                                <div class="small">
                                    <strong>Week <?php echo htmlspecialchars($progress['week_number']); ?>:</strong><br>
                                    <span class="text-success">MSLQ: <?php echo htmlspecialchars(number_format($progress['mslq_score'], 1)); ?></span><br>
                                    <span class="text-info">AMS: <?php echo htmlspecialchars(number_format($progress['ams_score'], 1)); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/weekly-evaluations" class="btn btn-sm btn-warning">
                        <i class="fas fa-arrow-right me-1"></i>View All
                    </a>
                <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No evaluations yet</h6>
                        <p class="small text-muted">Weekly evaluations help track your progress</p>
                        <a href="/weekly-evaluations" class="btn btn-sm btn-primary">
                            <i class="fas fa-play me-1"></i>Start Evaluating
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivities)): ?>
                    <div class="row">
                        <?php foreach (array_slice($recentActivities, 0, 6) as $activity): ?>
                            <div class="col-md-6">
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo htmlspecialchars($activity['action']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($activity['description']); ?></small>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(formatDate($activity['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No recent activity</h6>
                        <p class="small text-muted">Your learning activities will appear here</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


