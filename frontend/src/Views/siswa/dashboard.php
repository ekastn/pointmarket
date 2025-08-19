<?php
// Data for the view will be passed from the DashboardController
// $userProfile, $studentStats, $questionnaireStats, $latestVARKResult, $messages, $aiMetrics, $assignmentStats, $weeklyProgress, $recentActivities

// Ensure variables are defined to prevent PHP notices if not passed
$studentStats = $studentStats ?? [];
$learningStyle = $studentStats['learning_style'] ?? null;

$statsItems = [
    ['title' => 'Points', 'value' => $studentStats['total_points'], 'icon' => 'fas fa-coins'],
    ['title' => 'Completed Assignments', 'value' => $studentStats['completed_assignments'], 'icon' => 'fas fa-tasks'],
    ['title' => 'MSLQ Score', 'value' => $studentStats['mslq_score'], 'icon' => 'fas fa-brain'],
    ['title' => 'AMS Score', 'value' => $studentStats['ams_score'], 'icon' => 'fas fa-heart'],
];

require_once __DIR__.'/../../Helpers/VARKHelpers.php';
require_once __DIR__.'/../../Helpers/DateHelpers.php';

?>


<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2"></i>
        Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download me-1"></i>
                Ekspor
            </button>
        </div>
    </div>
</div>

<!-- AI Features POC -->
<?php $renderer->includePartial('components/partials/ai_features_poc'); ?>

<!-- Student Stats -->
<div class="row mb-4">
    <?php foreach ($statsItems as $item) { ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <?php $renderer->includePartial('components/partials/card_stats', $item); ?>
        </div>
    <?php } ?>
</div>

<!-- VARK Learning Style Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-left-primary shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-brain me-2"></i>
                    Profil Gaya Belajar Anda
                </h6>
            </div>
            <div class="card-body">
                <?php if ($learningStyle && $learningStyle && $learningStyle['label'] !== null) { ?>
                    <?php
                    $dominantStyle = $learningStyle['label'];
                    $learningPreference = $learningStyle['type'];
                    $learningTips = \App\Helpers\getVARKLearningTips($dominantStyle);
                    $preferenceScores = $learningStyle['scores'];
                    ?>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h5 class="text-primary mb-2">
                                        <i class="<?php echo htmlspecialchars($learningTips['icon']); ?> me-2"></i>
                                        <?php echo htmlspecialchars($learningPreference); ?>
                                    </h5>
                                    <p class="text-muted mb-3">
                                        <?php echo htmlspecialchars($learningTips['description']); ?>
                                    </p>
                                    
                                    <div class="vark-scores">
                                        <small class="text-muted">VARK Scores:</small>
                                        <div class="row mt-2">
                                            <div class="col-6">
                                                <span class="badge bg-info">Visual: <?php echo htmlspecialchars($preferenceScores['Visual'] ?? 'N/A'); ?></span>
                                                <span class="badge bg-warning">Auditory: <?php echo htmlspecialchars($preferenceScores['Auditory'] ?? 'N/A'); ?></span>
                                            </div>
                                            <div class="col-6">
                                                <span class="badge bg-success">Reading: <?php echo htmlspecialchars($preferenceScores['Reading'] ?? 'N/A'); ?></span>
                                                <span class="badge bg-danger">Kinesthetic: <?php echo htmlspecialchars($preferenceScores['Kinesthetic'] ?? 'N/A'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <h6 class="text-success">Study Tips for You:</h6>
                                    <ul class="small">
                                        <?php foreach (array_slice($learningTips['study_tips'], 0, 3) as $tip) { ?>
                                            <li><?php echo htmlspecialchars($tip); ?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
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
                <?php } else { ?>
                    <div class="text-center">
                        <i class="fas fa-brain fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Learning Style Not Assessed</h6>
                        <p class="text-muted">Take the VARK assessment to discover your learning style preferences and get personalized study recommendations.</p>
                        <a href="/vark-assessment" class="btn btn-primary">
                            <i class="fas fa-brain me-1"></i>
                            Take VARK Assessment
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
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
                        <a href="/assignments" class="btn btn-primary w-100">
                            <i class="fas fa-tasks me-2"></i>
                            View Assignments
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/quiz" class="btn btn-success w-100">
                            <i class="fas fa-question-circle me-2"></i>
                            Take Quiz
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/questionnaire" class="btn btn-info w-100">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Questionnaires
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/materials" class="btn btn-warning w-100">
                            <i class="fas fa-book me-2"></i>
                            Study Materials
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Evaluations -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    Weekly Evaluations
                </h5>
            </div>
            <div class="card-body">
                <!-- Assessment Results Overview -->
                <div class="row mb-4">
                    <div class="col">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-brain me-2"></i>
                                    MSLQ Assessment
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <?php if (isset($questionnaireStats[0]) && $questionnaireStats[0]['total_completed'] > 0) { ?>
                                    <div class="score-badge badge bg-success mb-3">
                                        <?php echo htmlspecialchars(number_format($questionnaireStats[0]['average_score'], 1)); ?>/7.0
                                    </div>
                                    <h6 class="text-success">Completed</h6>
                                    <p class="small text-muted">Learning strategies and motivation assessment</p>
                                    <div class="progress progress-custom mb-2">
                                        <div class="progress-bar bg-success" style="width: <?php echo htmlspecialchars(($questionnaireStats[0]['average_score'] / 7) * 100); ?>%"></div>
                                    </div>
                                    <a href="/questionnaire" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                <?php } else { ?>
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Not Completed</h6>
                                    <p class="small text-muted">Take the assessment to get personalized learning insights</p>
                                    <a href="/questionnaire" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play me-1"></i>Start Assessment
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-heart me-2"></i>
                                    AMS Assessment
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <?php if (isset($questionnaireStats[1]) && $questionnaireStats[1]['total_completed'] > 0) { ?>
                                    <div class="score-badge badge bg-warning text-dark mb-3">
                                        <?php echo htmlspecialchars(number_format($questionnaireStats[1]['average_score'], 1)); ?>/7.0
                                    </div>
                                    <h6 class="text-warning">Completed</h6>
                                    <p class="small text-muted">Academic motivation scale assessment</p>
                                    <div class="progress progress-custom mb-2">
                                        <div class="progress-bar bg-warning" style="width: <?php echo htmlspecialchars(($questionnaireStats[1]['average_score'] / 7) * 100); ?>%"></div>
                                    </div>
                                    <a href="/questionnaire" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                <?php } else { ?>
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Not Completed</h6>
                                    <p class="small text-muted">Assess your academic motivation patterns</p>
                                    <a href="/questionnaire" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play me-1"></i>Start Assessment
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $renderer->includePartial('components/partials/ai_simulations_section'); ?>
