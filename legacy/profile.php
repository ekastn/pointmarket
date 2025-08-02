<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Get all assessment data
$allScores = getAllQuestionnaireScores($user['id'], $pdo);
$mslqScores = $allScores['mslq'];
$amsScores = $allScores['ams'];
$varkResult = $allScores['vark'];

// Get weekly evaluation progress
$weeklyProgress = getWeeklyEvaluationProgress($user['id'], $pdo);

// Get activity log
try {
    $stmt = $pdo->prepare("
        SELECT action_type, description, created_at 
        FROM activity_log 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error getting activity log: " . $e->getMessage());
    $recentActivities = [];
}

// Get assignment/quiz statistics
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_assignments,
            AVG(score) as avg_score,
            MAX(score) as best_score,
            MIN(score) as lowest_score
        FROM assignment_submissions 
        WHERE student_id = ? AND score IS NOT NULL
    ");
    $stmt->execute([$user['id']]);
    $assignmentStats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error getting assignment stats: " . $e->getMessage());
    $assignmentStats = ['total_assignments' => 0, 'avg_score' => 0, 'best_score' => 0, 'lowest_score' => 0];
}

// Calculate learning insights
$learningInsights = [];

if ($mslqScores) {
    if ($mslqScores >= 4.0) {
        $learningInsights[] = "🎓 Strong learning strategies and motivation";
    } elseif ($mslqScores >= 3.0) {
        $learningInsights[] = "📚 Good learning approach with room for improvement";
    } else {
        $learningInsights[] = "🎯 Focus on developing better learning strategies";
    }
}

if ($amsScores) {
    if ($amsScores >= 4.0) {
        $learningInsights[] = "⭐ High academic motivation";
    } elseif ($amsScores >= 3.0) {
        $learningInsights[] = "💪 Moderate motivation levels";
    } else {
        $learningInsights[] = "🚀 Work on building academic motivation";
    }
}

if ($varkResult) {
    $dominantStyle = $varkResult['dominant_style'];
    $learningInsights[] = "🧠 {$dominantStyle} learning style preference";
}

$messages = getMessages();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - POINTMARKET</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Fix sidebar overlapping issue */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        
        .content-wrapper {
            display: flex;
            flex: 1;
        }
        
        .sidebar-wrapper {
            width: 250px;
            min-width: 250px;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        
        .main-content {
            flex: 1;
            overflow-x: auto;
            padding: 0;
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .sidebar-wrapper {
                width: 100%;
                min-width: 100%;
                order: 2;
            }
            
            .main-content {
                order: 1;
            }
        }
        
        .profile-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .profile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .score-badge {
            font-size: 1.2em;
            padding: 0.5rem 1rem;
        }
        .progress-custom {
            height: 20px;
        }
        .insight-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #0d6efd;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 0 8px 8px 0;
        }
        .assessment-status {
            position: relative;
            overflow: hidden;
        }
        .assessment-status::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-left: 20px solid transparent;
            border-top: 20px solid #28a745;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .activity-item {
            border-left: 3px solid #dee2e6;
            padding-left: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }
        .activity-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 8px;
            width: 12px;
            height: 12px;
            background: #0d6efd;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="content-wrapper">
            <!-- Sidebar -->
            <div class="sidebar-wrapper">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <main class="main-content">
                <div class="container-fluid px-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-user-circle me-2 text-primary"></i>
                        Student Profile
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>
                                Print Profile
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <?php
                if (!empty($messages['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($messages['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Basic Profile Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card profile-card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-id-card me-2"></i>
                                    Basic Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                                <i class="fas fa-user fa-2x text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h4>
                                                <p class="text-muted mb-0">Student ID: <?php echo htmlspecialchars($user['id']); ?></p>
                                                <small class="text-muted">Role: <?php echo ucfirst($user['role']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="stats-number"><?php echo $assignmentStats['total_assignments']; ?></div>
                                                <small class="text-muted">Assignments</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="stats-number"><?php echo $assignmentStats['avg_score'] ? number_format($assignmentStats['avg_score'], 1) : '0'; ?></div>
                                                <small class="text-muted">Avg Score</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="stats-number"><?php echo count($weeklyProgress); ?></div>
                                                <small class="text-muted">Evaluations</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assessment Results Overview -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card profile-card h-100 <?php echo $mslqScores ? 'assessment-status' : ''; ?>">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-brain me-2"></i>
                                    MSLQ Assessment
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($mslqScores): ?>
                                    <div class="score-badge badge bg-success mb-3">
                                        <?php echo number_format($mslqScores, 1); ?>/5.0
                                    </div>
                                    <h6 class="text-success">Completed</h6>
                                    <p class="small text-muted">Learning strategies and motivation assessment</p>
                                    <div class="progress progress-custom mb-2">
                                        <div class="progress-bar bg-success" style="width: <?php echo ($mslqScores / 5) * 100; ?>%"></div>
                                    </div>
                                    <a href="questionnaire.php" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                <?php else: ?>
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Not Completed</h6>
                                    <p class="small text-muted">Take the assessment to get personalized learning insights</p>
                                    <a href="questionnaire.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play me-1"></i>Start Assessment
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card profile-card h-100 <?php echo $amsScores ? 'assessment-status' : ''; ?>">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-heart me-2"></i>
                                    AMS Assessment
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($amsScores): ?>
                                    <div class="score-badge badge bg-warning text-dark mb-3">
                                        <?php echo number_format($amsScores, 1); ?>/5.0
                                    </div>
                                    <h6 class="text-warning">Completed</h6>
                                    <p class="small text-muted">Academic motivation scale assessment</p>
                                    <div class="progress progress-custom mb-2">
                                        <div class="progress-bar bg-warning" style="width: <?php echo ($amsScores / 5) * 100; ?>%"></div>
                                    </div>
                                    <a href="questionnaire.php" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                <?php else: ?>
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Not Completed</h6>
                                    <p class="small text-muted">Assess your academic motivation patterns</p>
                                    <a href="questionnaire.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play me-1"></i>Start Assessment
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card profile-card h-100 <?php echo $varkResult ? 'assessment-status' : ''; ?>">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    VARK Assessment
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($varkResult): ?>
                                    <?php $learningTips = getVARKLearningTips($varkResult['dominant_style']); ?>
                                    <div class="mb-3">
                                        <i class="<?php echo $learningTips['icon']; ?> fa-3x text-info mb-2"></i>
                                    </div>
                                    <h6 class="text-info"><?php echo htmlspecialchars($varkResult['learning_preference']); ?></h6>
                                    <p class="small text-muted"><?php echo $learningTips['description']; ?></p>
                                    <a href="vark-assessment.php" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                <?php else: ?>
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Not Completed</h6>
                                    <p class="small text-muted">Discover your learning style preferences</p>
                                    <a href="vark-assessment.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play me-1"></i>Start Assessment
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Insights -->
                <?php if (!empty($learningInsights)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle me-1"></i><strong>Demo Notice:</strong> The learning insights and recommendations shown below are generated based on basic scoring algorithms for demonstration purposes. In the full version, these will be powered by advanced AI analysis and machine learning models.</small>
                        </div>
                        <div class="card profile-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Your Learning Insights
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <?php foreach ($learningInsights as $insight): ?>
                                            <div class="insight-item">
                                                <?php echo $insight; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-light p-3 rounded">
                                            <h6 class="text-primary">💡 Recommendation</h6>
                                            <p class="small mb-2">Based on your assessments:</p>
                                            <ul class="small mb-0">
                                                <?php if ($varkResult): ?>
                                                    <?php $tips = getVARKLearningTips($varkResult['dominant_style']); ?>
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
                        <div class="card profile-card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Performance Statistics
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($assignmentStats['total_assignments'] > 0): ?>
                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <h4 class="text-success"><?php echo number_format($assignmentStats['best_score'], 1); ?></h4>
                                            <small class="text-muted">Best Score</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-info"><?php echo number_format($assignmentStats['avg_score'], 1); ?></h4>
                                            <small class="text-muted">Average Score</small>
                                        </div>
                                    </div>
                                    <div class="progress progress-custom mb-2">
                                        <div class="progress-bar bg-info" style="width: <?php echo ($assignmentStats['avg_score'] / 100) * 100; ?>%"></div>
                                    </div>
                                    <p class="small text-muted">
                                        Based on <?php echo $assignmentStats['total_assignments']; ?> completed assignments
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
                        <div class="card profile-card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Weekly Evaluations
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($weeklyProgress)): ?>
                                    <div class="text-center mb-3">
                                        <h4 class="text-warning"><?php echo count($weeklyProgress); ?></h4>
                                        <small class="text-muted">Completed Evaluations</small>
                                    </div>
                                    <div class="row">
                                        <?php 
                                        $recentWeeks = array_slice($weeklyProgress, -4, 4, true);
                                        foreach ($recentWeeks as $week): 
                                        ?>
                                            <div class="col-6 mb-2">
                                                <div class="small">
                                                    <strong>Week <?php echo $week['week_number']; ?>:</strong><br>
                                                    <span class="text-success">MSLQ: <?php echo number_format($week['mslq_score'], 1); ?></span><br>
                                                    <span class="text-info">AMS: <?php echo number_format($week['ams_score'], 1); ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <a href="weekly-evaluations.php" class="btn btn-sm btn-warning">
                                        <i class="fas fa-arrow-right me-1"></i>View All
                                    </a>
                                <?php else: ?>
                                    <div class="text-center">
                                        <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">No evaluations yet</h6>
                                        <p class="small text-muted">Weekly evaluations help track your progress</p>
                                        <a href="weekly-evaluations.php" class="btn btn-sm btn-primary">
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
                        <div class="card profile-card">
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
                                                            <strong><?php echo htmlspecialchars($activity['action_type']); ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($activity['description']); ?></small>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?php echo date('M j, H:i', strtotime($activity['created_at'])); ?>
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

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card profile-card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-rocket me-2"></i>
                                    Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3 mb-3">
                                        <a href="dashboard.php" class="btn btn-outline-primary d-block">
                                            <i class="fas fa-tachometer-alt fa-2x mb-2"></i><br>
                                            Dashboard
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="questionnaire.php" class="btn btn-outline-success d-block">
                                            <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>
                                            Assessments
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="weekly-evaluations.php" class="btn btn-outline-warning d-block">
                                            <i class="fas fa-calendar-check fa-2x mb-2"></i><br>
                                            Weekly Evaluations
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="vark-assessment.php" class="btn btn-outline-info d-block">
                                            <i class="fas fa-brain fa-2x mb-2"></i><br>
                                            VARK Style
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                </div> <!-- End container-fluid -->
            </main>
        </div> <!-- End content-wrapper -->
    </div> <!-- End main-wrapper -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bars
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.transition = 'width 1s ease-in-out';
                    bar.style.width = width;
                }, 300);
            });
        });
    </script>
</body>
</html>
