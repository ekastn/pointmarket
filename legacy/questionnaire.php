<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Get available questionnaires
function getAvailableQuestionnaires($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT id, name, description, type, total_questions, status 
            FROM questionnaires 
            WHERE status = 'active' AND type != 'vark'
            ORDER BY type
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting questionnaires: " . $e->getMessage());
        return [];
    }
}

// Get student's questionnaire history
function getQuestionnaireHistory($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                qr.id,
                qr.total_score,
                qr.completed_at,
                qr.week_number,
                qr.year,
                q.name as questionnaire_name,
                q.type as questionnaire_type,
                q.description as questionnaire_description
            FROM questionnaire_results qr
            JOIN questionnaires q ON qr.questionnaire_id = q.id
            WHERE qr.student_id = ?
            ORDER BY qr.completed_at DESC, qr.week_number DESC
            LIMIT 20
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting questionnaire history: " . $e->getMessage());
        return [];
    }
}

// Get questionnaire statistics for student
function getQuestionnaireStats($student_id, $pdo) {
    try {
        $stats = [];
        
        // Get regular questionnaire stats (MSLQ, AMS)
        $stmt = $pdo->prepare("
            SELECT 
                q.type,
                COUNT(qr.id) as total_completed,
                AVG(qr.total_score) as average_score,
                MAX(qr.total_score) as best_score,
                MIN(qr.total_score) as lowest_score,
                MAX(qr.completed_at) as last_completed
            FROM questionnaires q
            LEFT JOIN questionnaire_results qr ON (q.id = qr.questionnaire_id AND qr.student_id = ?)
            WHERE q.status = 'active' AND q.type IN ('mslq', 'ams')
            GROUP BY q.id, q.type
            ORDER BY q.type
        ");
        $stmt->execute([$student_id]);
        $regularStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add regular stats to result
        foreach ($regularStats as $stat) {
            $stats[] = $stat;
        }
        
        // Get VARK stats separately
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_completed, MAX(completed_at) as last_completed
            FROM vark_results 
            WHERE student_id = ?
        ");
        $stmt->execute([$student_id]);
        $varkStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Add VARK stats if exists
        if ($varkStats && $varkStats['total_completed'] > 0) {
            // For VARK, we don't have a traditional "score" like 1-7 scale
            // Instead we can show completion status and dominant learning style
            $stats[] = [
                'type' => 'vark',
                'total_completed' => $varkStats['total_completed'],
                'average_score' => null, // VARK doesn't have traditional scoring
                'best_score' => null,
                'lowest_score' => null,
                'last_completed' => $varkStats['last_completed']
            ];
        } else {
            // Add VARK entry even if not completed yet
            $stats[] = [
                'type' => 'vark',
                'total_completed' => 0,
                'average_score' => null,
                'best_score' => null,
                'lowest_score' => null,
                'last_completed' => null
            ];
        }
        
        return $stats;
    } catch (Exception $e) {
        error_log("Error getting questionnaire stats: " . $e->getMessage());
        return [];
    }
}

// Handle AJAX requests for questionnaire management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if (isset($_POST['action']) && $_POST['action'] === 'get_questionnaire_detail') {
        $questionnaire_id = (int)$_POST['questionnaire_id'];
        
        try {
            // Get questionnaire details
            $stmt = $pdo->prepare("SELECT * FROM questionnaires WHERE id = ? AND status = 'active'");
            $stmt->execute([$questionnaire_id]);
            $questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$questionnaire) {
                echo json_encode(['success' => false, 'message' => 'Questionnaire not found']);
                exit;
            }
            
            // Get questions
            $stmt = $pdo->prepare("
                SELECT question_number, question_text, subscale 
                FROM questionnaire_questions 
                WHERE questionnaire_id = ? 
                ORDER BY question_number
            ");
            $stmt->execute([$questionnaire_id]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get student's recent score for this questionnaire
            $stmt = $pdo->prepare("
                SELECT total_score, completed_at 
                FROM questionnaire_results 
                WHERE student_id = ? AND questionnaire_id = ? 
                ORDER BY completed_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$user['id'], $questionnaire_id]);
            $recent_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'questionnaire' => $questionnaire,
                'questions' => $questions,
                'recent_result' => $recent_result
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'submit_practice_questionnaire') {
        $questionnaire_id = (int)$_POST['questionnaire_id'];
        $answers = $_POST['answers'] ?? [];
        
        if (empty($answers)) {
            echo json_encode(['success' => false, 'message' => 'Please answer all questions']);
            exit;
        }
        
        try {
            // Calculate total score (simplified scoring)
            $total_score = 0;
            $answer_count = 0;
            foreach ($answers as $answer) {
                $total_score += (int)$answer;
                $answer_count++;
            }
            $average_score = $answer_count > 0 ? $total_score / $answer_count : 0;
            
            // Insert practice result (without week tracking for general practice)
            $stmt = $pdo->prepare("
                INSERT INTO questionnaire_results 
                (student_id, questionnaire_id, answers, total_score, completed_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user['id'],
                $questionnaire_id,
                json_encode($answers),
                $average_score
            ]);
            
            // Log activity
            $stmt = $pdo->prepare("SELECT name FROM questionnaires WHERE id = ?");
            $stmt->execute([$questionnaire_id]);
            $questionnaire_name = $stmt->fetchColumn();
            
            logActivity($user['id'], 'questionnaire_practice', "Completed practice $questionnaire_name", $pdo);
            
            echo json_encode([
                'success' => true,
                'message' => 'Practice questionnaire completed successfully!',
                'score' => round($average_score, 2)
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error submitting questionnaire: ' . $e->getMessage()]);
        }
        exit;
    }
}

$questionnaires = getAvailableQuestionnaires($pdo);
$history = getQuestionnaireHistory($user['id'], $pdo);
$stats = getQuestionnaireStats($user['id'], $pdo);
$pendingEvaluations = getPendingWeeklyEvaluations($user['id'], $pdo);

// Get VARK result for display
$varkResult = getStudentVARKResult($user['id'], $pdo);

$messages = getMessages();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionnaires - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        
        .questionnaire-card {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .questionnaire-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .questionnaire-card.mslq {
            border-left-color: #198754;
        }
        .questionnaire-card.ams {
            border-left-color: #fd7e14;
        }
        .stats-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .history-timeline {
            border-left: 3px solid #dee2e6;
            padding-left: 1.5rem;
            margin-left: 1rem;
        }
        .history-item {
            position: relative;
            margin-bottom: 2rem;
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .history-item::before {
            content: '';
            position: absolute;
            left: -1.75rem;
            top: 1rem;
            width: 12px;
            height: 12px;
            background: #0d6efd;
            border-radius: 50%;
            border: 3px solid white;
        }
        .score-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .score-high {
            background-color: #d1edff;
            color: #0c63e4;
        }
        .score-medium {
            background-color: #fff3cd;
            color: #664d03;
        }
        .score-low {
            background-color: #f8d7da;
            color: #721c24;
        }
        .pending-alert {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 4px solid #fd7e14;
        }
        .question-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .question-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .scale-option {
            text-align: center;
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin: 0 2px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .scale-option:hover {
            background-color: #e9ecef;
        }
        .scale-option.selected {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
        .info-section {
            background: linear-gradient(135deg, #e7f3ff 0%, #cce7ff 100%);
            border-left: 4px solid #0d6efd;
            border-radius: 8px;
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
                    <h1 class="h2"><i class="fas fa-clipboard-list me-2"></i>Questionnaires</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="weekly-evaluations.php" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-check"></i> Weekly Evaluations
                            </a>
                            <a href="ai-explanation.php" class="btn btn-outline-info">
                                <i class="fas fa-robot"></i> AI Explanation
                            </a>
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

                <!-- Pending Weekly Evaluations Alert -->
                <?php if (!empty($pendingEvaluations)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert pending-alert">
                            <h5><i class="fas fa-bell me-2"></i>Weekly Evaluations Pending</h5>
                            <p>You have <strong><?php echo count($pendingEvaluations); ?> weekly evaluation(s)</strong> that need to be completed.</p>
                            <a href="weekly-evaluations.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-calendar-check me-1"></i> Complete Weekly Evaluations
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Information Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="info-section p-4">
                            <h4><i class="fas fa-info-circle me-2"></i>About Questionnaires & AI Integration</h4>
                            
                            <!-- AI Implementation Notice -->
                            <div class="alert alert-primary mb-3">
                                <h6><i class="fas fa-robot me-2"></i>AI-Powered Learning Personalization (Proof of Concept)</h6>
                                <p class="mb-2">This demonstration showcases how psychological questionnaires will integrate with our AI systems:</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>🧠 NLP Integration:</strong> Questionnaire results will guide AI feedback style and complexity level for assignments.
                                    </div>
                                    <div class="col-md-4">
                                        <strong>🎯 RL Optimization:</strong> Learning patterns will be analyzed to recommend optimal study schedules and content difficulty.
                                    </div>
                                    <div class="col-md-4">
                                        <strong>📚 CBF Matching:</strong> Content recommendations will be personalized based on learning style and motivation profiles.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-brain me-2 text-success"></i>MSLQ (Motivated Strategies for Learning)</h6>
                                    <p class="mb-3">Mengukur motivasi dan strategi belajar Anda. Hasil MSLQ membantu AI POINTMARKET memahami gaya belajar Anda dan memberikan rekomendasi yang sesuai.</p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-heart me-2 text-warning"></i>AMS (Academic Motivation Scale)</h6>
                                    <p class="mb-3">Mengukur tipe motivasi akademik Anda. Data AMS digunakan AI untuk menyesuaikan sistem reward dan mendorong engagement belajar.</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Tip:</strong> Untuk hasil terbaik, jawab dengan jujur berdasarkan kondisi Anda saat ini. 
                                    Data ini digunakan AI untuk personalisasi pembelajaran yang lebih efektif.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Overview -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4><i class="fas fa-chart-bar me-2"></i>Your Statistics</h4>
                    </div>
                    <?php foreach ($stats as $stat): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card stats-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-<?php echo $stat['type'] === 'mslq' ? 'brain' : ($stat['type'] === 'ams' ? 'heart' : 'graduation-cap'); ?> me-2"></i>
                                    <?php echo strtoupper($stat['type']); ?>
                                </h5>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Completed:</strong></p>
                                        <h4 class="text-primary"><?php echo $stat['total_completed'] ?? 0; ?></h4>
                                    </div>
                                    <div class="col-6">
                                        <?php if ($stat['type'] === 'vark'): ?>
                                            <p class="mb-1"><strong>Learning Style:</strong></p>
                                            <?php if ($stat['total_completed'] > 0 && $varkResult): ?>
                                                <h6 class="text-success"><?php echo htmlspecialchars($varkResult['dominant_style']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($varkResult['learning_preference']); ?></small>
                                            <?php else: ?>
                                                <h6 class="text-muted">Not assessed</h6>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="mb-1"><strong>Average Score:</strong></p>
                                            <?php if ($stat['average_score'] !== null): ?>
                                                <?php 
                                                $avg_score = $stat['average_score'];
                                                $scoreClass = $avg_score >= 5.5 ? 'score-high' : ($avg_score >= 4 ? 'score-medium' : 'score-low');
                                                ?>
                                                <h4 class="<?php echo $scoreClass; ?>"><?php echo number_format($avg_score, 2); ?></h4>
                                            <?php else: ?>
                                                <h4 class="text-muted">-</h4>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($stat['last_completed']): ?>
                                    <small class="text-muted">
                                        Last completed: <?php echo formatDate($stat['last_completed']); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Available Questionnaires -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4><i class="fas fa-list me-2"></i>Available Questionnaires</h4>
                        <p class="text-muted">Practice questionnaires untuk memahami format dan konten. Untuk evaluasi mingguan resmi, gunakan <a href="weekly-evaluations.php">Weekly Evaluations</a>.</p>
                    </div>
                    <?php foreach ($questionnaires as $questionnaire): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card questionnaire-card <?php echo $questionnaire['type']; ?> h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-<?php echo $questionnaire['type'] === 'mslq' ? 'brain' : 'heart'; ?> me-2"></i>
                                    <?php echo htmlspecialchars($questionnaire['name']); ?>
                                </h5>
                                <p class="card-text"><?php echo htmlspecialchars($questionnaire['description']); ?></p>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-question-circle me-1"></i>
                                        <?php echo $questionnaire['total_questions']; ?> Questions
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Estimated time: <?php echo ceil($questionnaire['total_questions'] * 0.5); ?> minutes
                                    </small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm" onclick="startPracticeQuestionnaire(<?php echo $questionnaire['id']; ?>)">
                                        <i class="fas fa-play me-1"></i> Practice
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="viewQuestionnaireInfo(<?php echo $questionnaire['id']; ?>)">
                                        <i class="fas fa-info me-1"></i> Info
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Recent History -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Questionnaire History</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($history)): ?>
                                    <div class="history-timeline">
                                        <?php foreach ($history as $item): ?>
                                        <div class="history-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i class="fas fa-<?php echo $item['questionnaire_type'] === 'mslq' ? 'brain' : 'heart'; ?> me-2"></i>
                                                        <?php echo htmlspecialchars($item['questionnaire_name']); ?>
                                                    </h6>
                                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($item['questionnaire_description']); ?></p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo formatDate($item['completed_at']); ?>
                                                        <?php if ($item['week_number'] && $item['year']): ?>
                                                            | Week <?php echo $item['week_number']; ?>/<?php echo $item['year']; ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <?php 
                                                    $score = $item['total_score'];
                                                    $scoreClass = $score >= 5.5 ? 'score-high' : ($score >= 4 ? 'score-medium' : 'score-low');
                                                    ?>
                                                    <span class="score-badge <?php echo $scoreClass; ?>">
                                                        Score: <?php echo number_format($score, 2); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No questionnaire history yet</h5>
                                        <p class="text-muted">Complete your first questionnaire to see your progress here.</p>
                                        <a href="weekly-evaluations.php" class="btn btn-primary">
                                            <i class="fas fa-calendar-check me-1"></i> Start Weekly Evaluations
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VARK Learning Style Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-brain me-2"></i>
                                    VARK Learning Style Assessment
                                </h5>
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
                                                <i class="<?php echo $learningTips['icon']; ?> fa-2x text-primary me-3"></i>
                                                <div>
                                                    <h5 class="mb-1"><?php echo htmlspecialchars($varkResult['learning_preference']); ?></h5>
                                                    <p class="text-muted mb-0"><?php echo $learningTips['description']; ?></p>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-sm-6">
                                                    <h6>VARK Scores:</h6>
                                                    <div class="small">
                                                        <span class="badge bg-info me-1">Visual: <?php echo $varkResult['visual_score']; ?></span>
                                                        <span class="badge bg-warning me-1">Auditory: <?php echo $varkResult['auditory_score']; ?></span>
                                                        <br class="d-sm-none">
                                                        <span class="badge bg-success me-1 mt-1">Reading: <?php echo $varkResult['reading_score']; ?></span>
                                                        <span class="badge bg-danger me-1 mt-1">Kinesthetic: <?php echo $varkResult['kinesthetic_score']; ?></span>
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
                                                Completed: <?php echo date('d M Y H:i', strtotime($varkResult['completed_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="bg-light p-3 rounded">
                                                <h6 class="text-muted">Assessment Status</h6>
                                                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                                                <p class="text-success mb-2">Completed</p>
                                                <a href="vark-assessment.php" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-sync-alt me-1"></i>
                                                    Retake Assessment
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="text-primary">Discover Your Learning Style</h6>
                                            <p>VARK stands for <strong>V</strong>isual, <strong>A</strong>uditory, <strong>R</strong>eading/Writing, and <strong>K</strong>inesthetic learning styles. 
                                            This assessment helps identify your preferred learning mode and provides personalized study recommendations.</p>
                                            
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <h6>The Four Learning Styles:</h6>
                                                    <ul class="list-unstyled small">
                                                        <li><i class="fas fa-eye text-info me-2"></i><strong>Visual:</strong> Charts, diagrams, images</li>
                                                        <li><i class="fas fa-volume-up text-warning me-2"></i><strong>Auditory:</strong> Discussion, listening</li>
                                                        <li><i class="fas fa-book-open text-success me-2"></i><strong>Reading/Writing:</strong> Text, notes</li>
                                                        <li><i class="fas fa-hand-rock text-danger me-2"></i><strong>Kinesthetic:</strong> Hands-on practice</li>
                                                    </ul>
                                                </div>
                                                <div class="col-sm-6">
                                                    <h6>Assessment Details:</h6>
                                                    <ul class="list-unstyled small">
                                                        <li><i class="fas fa-clock me-2"></i>Time: ~10-15 minutes</li>
                                                        <li><i class="fas fa-list-ol me-2"></i>Questions: 16 scenarios</li>
                                                        <li><i class="fas fa-tasks me-2"></i>Format: Multiple choice</li>
                                                        <li><i class="fas fa-chart-line me-2"></i>Result: Learning profile</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="bg-light p-3 rounded">
                                                <h6 class="text-muted">Take Assessment</h6>
                                                <i class="fas fa-brain fa-3x text-muted mb-2"></i>
                                                <p class="text-muted mb-2">Not completed</p>
                                                <a href="vark-assessment.php" class="btn btn-primary">
                                                    <i class="fas fa-brain me-1"></i>
                                                    Start VARK Assessment
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Questionnaire Info Modal -->
    <div class="modal fade" id="questionnaireInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionnaireInfoModalTitle">
                        <i class="fas fa-info-circle me-2"></i>Questionnaire Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="questionnaireInfoModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Practice Questionnaire Modal -->
    <div class="modal fade" id="practiceQuestionnaireModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="practiceQuestionnaireModalTitle">
                        <i class="fas fa-clipboard-list me-2"></i>Practice Questionnaire
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="practiceQuestionnaireModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentQuestionnaireId = null;

        function viewQuestionnaireInfo(questionnaireId) {
            currentQuestionnaireId = questionnaireId;
            
            // Show loading
            document.getElementById('questionnaireInfoModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Loading questionnaire information...</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('questionnaireInfoModal'));
            modal.show();
            
            // Load questionnaire info
            fetch('questionnaire.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=get_questionnaire_detail&questionnaire_id=${questionnaireId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadQuestionnaireInfo(data.questionnaire, data.questions, data.recent_result);
                } else {
                    document.getElementById('questionnaireInfoModalBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading questionnaire: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('questionnaireInfoModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Network error. Please try again.
                    </div>
                `;
            });
        }

        function loadQuestionnaireInfo(questionnaire, questions, recentResult) {
            document.getElementById('questionnaireInfoModalTitle').innerHTML = `
                <i class="fas fa-info-circle me-2"></i>${questionnaire.name}
            `;
            
            let html = `
                <div class="mb-4">
                    <h6>Description</h6>
                    <p>${questionnaire.description}</p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <h5 class="text-primary">${questionnaire.total_questions}</h5>
                            <small class="text-muted">Total Questions</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <h5 class="text-success">${Math.ceil(questionnaire.total_questions * 0.5)}</h5>
                            <small class="text-muted">Est. Minutes</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <h5 class="text-info">1-7</h5>
                            <small class="text-muted">Scale Range</small>
                        </div>
                    </div>
                </div>
            `;
            
            if (recentResult) {
                html += `
                    <div class="alert alert-info">
                        <h6><i class="fas fa-chart-line me-2"></i>Your Last Result</h6>
                        <p class="mb-0">
                            Score: <strong>${parseFloat(recentResult.total_score).toFixed(2)}</strong> | 
                            Completed: ${new Date(recentResult.completed_at).toLocaleDateString('id-ID')}
                        </p>
                    </div>
                `;
            }
            
            // Group questions by subscale
            const subscales = {};
            questions.forEach(q => {
                if (!subscales[q.subscale]) {
                    subscales[q.subscale] = [];
                }
                subscales[q.subscale].push(q);
            });
            
            html += `
                <h6>Question Categories</h6>
                <div class="accordion" id="subscaleAccordion">
            `;
            
            let accordionIndex = 0;
            for (const [subscale, subscaleQuestions] of Object.entries(subscales)) {
                html += `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading${accordionIndex}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse${accordionIndex}">
                                <strong>${subscale}</strong> 
                                <span class="badge bg-primary ms-2">${subscaleQuestions.length} questions</span>
                            </button>
                        </h2>
                        <div id="collapse${accordionIndex}" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ol>
                `;
                
                subscaleQuestions.forEach(q => {
                    html += `<li class="mb-2">${q.question_text}</li>`;
                });
                
                html += `
                                </ol>
                            </div>
                        </div>
                    </div>
                `;
                accordionIndex++;
            }
            
            html += `
                </div>
                <div class="mt-4 text-center">
                    <button type="button" class="btn btn-primary" onclick="startPracticeQuestionnaire(${questionnaire.id})">
                        <i class="fas fa-play me-1"></i> Start Practice
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            `;
            
            document.getElementById('questionnaireInfoModalBody').innerHTML = html;
        }

        function startPracticeQuestionnaire(questionnaireId) {
            currentQuestionnaireId = questionnaireId;
            
            // Close info modal if open
            const infoModal = bootstrap.Modal.getInstance(document.getElementById('questionnaireInfoModal'));
            if (infoModal) {
                infoModal.hide();
            }
            
            // Show loading
            document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Loading practice questionnaire...</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('practiceQuestionnaireModal'));
            modal.show();
            
            // Load questionnaire data
            fetch('questionnaire.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=get_questionnaire_detail&questionnaire_id=${questionnaireId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadPracticeForm(data.questionnaire, data.questions);
                } else {
                    document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading questionnaire: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Network error. Please try again.
                    </div>
                `;
            });
        }

        function loadPracticeForm(questionnaire, questions) {
            document.getElementById('practiceQuestionnaireModalTitle').innerHTML = `
                <i class="fas fa-clipboard-list me-2"></i>${questionnaire.name} - Practice
            `;
            
            let html = `
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Practice Mode</h6>
                    <p class="mb-0">This is a practice session. For official weekly evaluations, please use the 
                    <a href="weekly-evaluations.php" class="alert-link">Weekly Evaluations</a> page.</p>
                </div>
                
                <div class="mb-3">
                    <p><strong>Instructions:</strong> Rate each statement on a scale of 1-7:</p>
                    <div class="row text-center mb-3">
                        <div class="col">1 = Not at all true of me</div>
                        <div class="col">4 = Somewhat true of me</div>
                        <div class="col">7 = Very true of me</div>
                    </div>
                </div>
                
                <form id="practiceQuestionnaireForm">
                    <div style="max-height: 400px; overflow-y: auto;">
            `;
            
            questions.forEach((question, index) => {
                html += `
                    <div class="question-card p-3 mb-3">
                        <div class="mb-2">
                            <strong>Question ${question.question_number}:</strong>
                            <small class="text-muted ms-2">(${question.subscale})</small>
                        </div>
                        <p class="mb-3">${question.question_text}</p>
                        <div class="row">
                            ${[1,2,3,4,5,6,7].map(value => `
                                <div class="col">
                                    <div class="scale-option" onclick="selectPracticeAnswer(${question.question_number}, ${value})">
                                        <strong>${value}</strong>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <input type="hidden" name="answers[${question.question_number}]" id="practice_answer_${question.question_number}">
                    </div>
                `;
            });
            
            html += `
                    </div>
                </form>
                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success" onclick="submitPracticeQuestionnaire()">
                        <i class="fas fa-check me-1"></i> Submit Practice
                    </button>
                </div>
            `;
            
            document.getElementById('practiceQuestionnaireModalBody').innerHTML = html;
        }

        function selectPracticeAnswer(questionNumber, value) {
            // Remove previous selection
            document.querySelectorAll(`[onclick*="selectPracticeAnswer(${questionNumber}"]`).forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selection to clicked option
            event.target.classList.add('selected');
            
            // Set hidden input value
            document.getElementById(`practice_answer_${questionNumber}`).value = value;
        }

        function submitPracticeQuestionnaire() {
            const form = document.getElementById('practiceQuestionnaireForm');
            const formData = new FormData(form);
            formData.append('ajax', '1');
            formData.append('action', 'submit_practice_questionnaire');
            formData.append('questionnaire_id', currentQuestionnaireId);
            
            // Check if all questions are answered
            const answers = {};
            const inputs = form.querySelectorAll('input[name^="answers"]');
            let allAnswered = true;
            
            inputs.forEach(input => {
                if (!input.value) {
                    allAnswered = false;
                } else {
                    const match = input.name.match(/answers\[(\d+)\]/);
                    if (match) {
                        answers[match[1]] = input.value;
                    }
                }
            });
            
            if (!allAnswered) {
                alert('Please answer all questions before submitting.');
                return;
            }
            
            // Convert FormData to URLSearchParams
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                params.append(key, value);
            }
            
            // Add answers
            for (const [questionNumber, answer] of Object.entries(answers)) {
                params.append(`answers[${questionNumber}]`, answer);
            }
            
            // Show loading
            document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Submitting your practice questionnaire...</p>
                </div>
            `;
            
            fetch('questionnaire.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                            <h5>Practice Completed Successfully!</h5>
                            <p>Your practice score: <strong>${data.score}</strong></p>
                            <p class="mb-0">${data.message}</p>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh Page
                            </button>
                        </div>
                    `;
                } else {
                    document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error: ${data.message}
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Network error. Please try again.
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                `;
            });
        }
    </script>
    
                </div> <!-- End container-fluid -->
            </main>
        </div> <!-- End content-wrapper -->
    </div> <!-- End main-wrapper -->
</body>
</html>
