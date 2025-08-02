<?php
require_once 'includes/config.php';
requireLogin();

$user = getCurrentUser();

// Allow both students and admins to access assignments
if ($user['role'] !== 'siswa' && $user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Get student's assignments with detailed information
function getStudentAssignments($student_id, $pdo, $status = 'all') {
    try {
        $whereClause = "WHERE a.status = 'active'";
        if ($status !== 'all') {
            $whereClause .= " AND COALESCE(sa.status, 'not_started') = :assignment_status";
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.title,
                a.description,
                a.subject,
                a.points,
                a.due_date,
                a.created_at,
                u.name as teacher_name,
                COALESCE(sa.status, 'not_started') as student_status,
                sa.score,
                sa.submitted_at,
                sa.graded_at,
                CASE 
                    WHEN a.due_date < NOW() AND COALESCE(sa.status, 'not_started') != 'completed' THEN 'overdue'
                    WHEN a.due_date <= DATE_ADD(NOW(), INTERVAL 2 DAY) AND COALESCE(sa.status, 'not_started') = 'not_started' THEN 'due_soon'
                    ELSE 'normal'
                END as urgency_status,
                DATEDIFF(a.due_date, NOW()) as days_remaining
            FROM assignments a
            LEFT JOIN student_assignments sa ON (a.id = sa.assignment_id AND sa.student_id = ?)
            LEFT JOIN users u ON a.teacher_id = u.id
            $whereClause
            ORDER BY 
                CASE 
                    WHEN COALESCE(sa.status, 'not_started') = 'not_started' AND a.due_date < NOW() THEN 1
                    WHEN COALESCE(sa.status, 'not_started') = 'not_started' AND a.due_date <= DATE_ADD(NOW(), INTERVAL 2 DAY) THEN 2
                    WHEN COALESCE(sa.status, 'not_started') = 'not_started' THEN 3
                    WHEN COALESCE(sa.status, 'not_started') = 'in_progress' THEN 4
                    ELSE 5
                END,
                a.due_date ASC
        ");
        
        if ($status !== 'all') {
            $stmt->execute([$student_id, $status]);
        } else {
            $stmt->execute([$student_id]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting student assignments: " . $e->getMessage());
        return [];
    }
}

// Get assignment statistics
function getAssignmentStats($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(CASE WHEN COALESCE(sa.status, 'not_started') = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN COALESCE(sa.status, 'not_started') = 'in_progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN COALESCE(sa.status, 'not_started') = 'not_started' THEN 1 END) as not_started,
                COUNT(CASE WHEN a.due_date < NOW() AND COALESCE(sa.status, 'not_started') != 'completed' THEN 1 END) as overdue,
                COALESCE(SUM(CASE WHEN sa.status = 'completed' THEN sa.score ELSE 0 END), 0) as total_points,
                COALESCE(AVG(CASE WHEN sa.status = 'completed' THEN sa.score END), 0) as average_score,
                COUNT(a.id) as total_assignments
            FROM assignments a
            LEFT JOIN student_assignments sa ON (a.id = sa.assignment_id AND sa.student_id = ?)
            WHERE a.status = 'active'
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting assignment stats: " . $e->getMessage());
        return [];
    }
}

// Get subjects with assignment counts
function getSubjectsWithCounts($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                a.subject,
                COUNT(a.id) as total_assignments,
                COUNT(CASE WHEN COALESCE(sa.status, 'not_started') = 'completed' THEN 1 END) as completed_assignments,
                COALESCE(AVG(CASE WHEN sa.status = 'completed' THEN sa.score END), 0) as average_score
            FROM assignments a
            LEFT JOIN student_assignments sa ON (a.id = sa.assignment_id AND sa.student_id = ?)
            WHERE a.status = 'active'
            GROUP BY a.subject
            ORDER BY a.subject
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting subjects: " . $e->getMessage());
        return [];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if (isset($_POST['action']) && $_POST['action'] === 'start_assignment') {
        $assignment_id = (int)$_POST['assignment_id'];
        
        try {
            // Check if assignment exists and is active
            $stmt = $pdo->prepare("SELECT id, title FROM assignments WHERE id = ? AND status = 'active'");
            $stmt->execute([$assignment_id]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$assignment) {
                echo json_encode(['success' => false, 'message' => 'Assignment not found']);
                exit;
            }
            
            // Check if student already has a record for this assignment
            $stmt = $pdo->prepare("SELECT id, status FROM student_assignments WHERE student_id = ? AND assignment_id = ?");
            $stmt->execute([$user['id'], $assignment_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['status'] === 'completed') {
                    echo json_encode(['success' => false, 'message' => 'Assignment already completed']);
                    exit;
                }
                // Update existing record to in_progress
                $stmt = $pdo->prepare("UPDATE student_assignments SET status = 'in_progress' WHERE id = ?");
                $stmt->execute([$existing['id']]);
            } else {
                // Create new record
                $stmt = $pdo->prepare("
                    INSERT INTO student_assignments (student_id, assignment_id, status, created_at) 
                    VALUES (?, ?, 'in_progress', NOW())
                ");
                $stmt->execute([$user['id'], $assignment_id]);
            }
            
            // Log activity
            logActivity($user['id'], 'assignment_started', "Started assignment: {$assignment['title']}", $pdo);
            
            echo json_encode(['success' => true, 'message' => 'Assignment started successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'submit_assignment') {
        $assignment_id = (int)$_POST['assignment_id'];
        $submission_text = trim($_POST['submission_text'] ?? '');
        
        if (empty($submission_text)) {
            echo json_encode(['success' => false, 'message' => 'Please provide your submission content']);
            exit;
        }
        
        try {
            $pdo->beginTransaction();
            
            // Check if assignment exists
            $stmt = $pdo->prepare("SELECT id, title, points FROM assignments WHERE id = ? AND status = 'active'");
            $stmt->execute([$assignment_id]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$assignment) {
                echo json_encode(['success' => false, 'message' => 'Assignment not found']);
                $pdo->rollback();
                exit;
            }
            
            // Check if student has a record for this assignment
            $stmt = $pdo->prepare("SELECT id, status FROM student_assignments WHERE student_id = ? AND assignment_id = ?");
            $stmt->execute([$user['id'], $assignment_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing && $existing['status'] === 'completed') {
                echo json_encode(['success' => false, 'message' => 'Assignment already submitted']);
                $pdo->rollback();
                exit;
            }
            
            // Generate a simple score (70-95% of max points for demo)
            $score = rand(70, 95) * ($assignment['points'] / 100);
            
            if ($existing) {
                // Update existing record
                $stmt = $pdo->prepare("
                    UPDATE student_assignments 
                    SET status = 'completed', score = ?, submitted_at = NOW(), graded_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$score, $existing['id']]);
            } else {
                // Create new record
                $stmt = $pdo->prepare("
                    INSERT INTO student_assignments 
                    (student_id, assignment_id, status, score, submitted_at, graded_at, created_at) 
                    VALUES (?, ?, 'completed', ?, NOW(), NOW(), NOW())
                ");
                $stmt->execute([$user['id'], $assignment_id, $score]);
            }
            
            // Log activity
            logActivity($user['id'], 'assignment_submitted', "Submitted assignment: {$assignment['title']} (Score: $score)", $pdo);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Assignment submitted successfully!',
                'score' => round($score, 1)
            ]);
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$subject_filter = $_GET['subject'] ?? 'all';

// Get data
$assignments = getStudentAssignments($user['id'], $pdo, $status_filter);
$stats = getAssignmentStats($user['id'], $pdo);
$subjects = getSubjectsWithCounts($user['id'], $pdo);
$pendingEvaluations = getPendingWeeklyEvaluations($user['id'], $pdo);

// Filter by subject if specified
if ($subject_filter !== 'all') {
    $assignments = array_filter($assignments, function($assignment) use ($subject_filter) {
        return $assignment['subject'] === $subject_filter;
    });
}

$messages = getMessages();

// Debug: Final check before rendering
error_log("Assignments page: About to render with " . count($assignments) . " assignments");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - POINTMARKET</title>
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
        
        .assignment-card {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
        .assignment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .assignment-card.not-started {
            border-left-color: #6c757d;
        }
        .assignment-card.in-progress {
            border-left-color: #fd7e14;
        }
        .assignment-card.completed {
            border-left-color: #198754;
        }
        .assignment-card.overdue {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
        }
        .assignment-card.due-soon {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, #fffbf0 0%, #fff3cd 100%);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .status-badge.not-started {
            background-color: #6c757d;
        }
        .status-badge.in-progress {
            background-color: #fd7e14;
        }
        .status-badge.completed {
            background-color: #198754;
        }
        .urgency-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        .urgency-indicator.overdue {
            background-color: #dc3545;
            animation: pulse 2s infinite;
        }
        .urgency-indicator.due-soon {
            background-color: #ffc107;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .stats-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .filter-section {
            background: linear-gradient(135deg, #e7f3ff 0%, #cce7ff 100%);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .subject-tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .progress-ring {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.875rem;
        }
        .submission-modal .modal-dialog {
            max-width: 600px;
        }
        .pending-alert {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 4px solid #fd7e14;
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
                    <h1 class="h2"><i class="fas fa-tasks me-2"></i>My Assignments</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="weekly-evaluations.php" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-check"></i> Weekly Evaluations
                            </a>
                            <a href="progress.php" class="btn btn-outline-info">
                                <i class="fas fa-chart-line"></i> My Progress
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
                            <p>Complete your <strong><?php echo count($pendingEvaluations); ?> pending weekly evaluation(s)</strong> to help AI provide better assignment recommendations.</p>
                            <a href="weekly-evaluations.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-calendar-check me-1"></i> Complete Evaluations
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Statistics Overview -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <div class="progress-ring" style="background: linear-gradient(135deg, #d1edff 0%, #a8daff 100%); color: #0c63e4;">
                                    <?php echo $stats['total_assignments']; ?>
                                </div>
                                <h6 class="mt-2 mb-0">Total Assignments</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <div class="progress-ring" style="background: linear-gradient(135deg, #d4edda 0%, #a3d9a4 100%); color: #155724;">
                                    <?php echo $stats['completed']; ?>
                                </div>
                                <h6 class="mt-2 mb-0">Completed</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <div class="progress-ring" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); color: #664d03;">
                                    <?php echo $stats['in_progress']; ?>
                                </div>
                                <h6 class="mt-2 mb-0">In Progress</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <div class="progress-ring" style="background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%); color: #721c24;">
                                    <?php echo $stats['overdue']; ?>
                                </div>
                                <h6 class="mt-2 mb-0">Overdue</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Summary -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h6><i class="fas fa-trophy me-2 text-warning"></i>Total Points Earned</h6>
                                <h3 class="text-primary"><?php echo number_format($stats['total_points'], 1); ?></h3>
                                <small class="text-muted">From completed assignments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h6><i class="fas fa-chart-line me-2 text-success"></i>Average Score</h6>
                                <h3 class="text-success"><?php echo number_format($stats['average_score'], 1); ?></h3>
                                <small class="text-muted">Per assignment</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-section">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-2"><i class="fas fa-filter me-2"></i>Filter Assignments</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="not_started" <?php echo $status_filter === 'not_started' ? 'selected' : ''; ?>>Not Started</option>
                                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <select id="subjectFilter" class="form-select form-select-sm" style="width: auto;">
                                    <option value="all" <?php echo $subject_filter === 'all' ? 'selected' : ''; ?>>All Subjects</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo htmlspecialchars($subject['subject']); ?>" 
                                                <?php echo $subject_filter === $subject['subject'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2"><i class="fas fa-book me-2"></i>Subjects</h6>
                            <div class="d-flex flex-wrap">
                                <?php foreach ($subjects as $subject): ?>
                                    <span class="subject-tag bg-light text-dark">
                                        <?php echo htmlspecialchars($subject['subject']); ?>
                                        <span class="badge bg-primary ms-1"><?php echo $subject['completed_assignments']; ?>/<?php echo $subject['total_assignments']; ?></span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignments List -->
                <div class="row">
                    <div class="col-12">
                        <h4><i class="fas fa-list me-2"></i>Assignments 
                            <small class="text-muted">(<?php echo count($assignments); ?> assignments)</small>
                        </h4>
                        
                        <!-- AI Implementation Notice -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="fas fa-info-circle me-2"></i>Proof of Concept - AI Features Preview</h6>
                            <p class="mb-2">
                                <strong>📊 Current Status:</strong> This demonstration uses simulated AI scoring to showcase the planned NLP analysis capabilities.
                            </p>
                            <p class="mb-1">
                                <strong>🤖 Planned AI Implementation:</strong>
                            </p>
                            <ul class="mb-0 small">
                                <li><strong>NLP Analysis:</strong> Real-time grammar checking, content relevance scoring, and structural analysis</li>
                                <li><strong>Smart Feedback:</strong> Detailed suggestions for improvement based on writing quality</li>
                                <li><strong>Adaptive Scoring:</strong> Context-aware scoring that considers assignment complexity and student level</li>
                                <li><strong>Draft Auto-Save:</strong> Intelligent progress saving with revision tracking</li>
                            </ul>
                        </div>
                    </div>
                    
                    <?php if (!empty($assignments)): ?>
                        <?php foreach ($assignments as $assignment): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card assignment-card <?php echo $assignment['student_status']; ?> <?php echo $assignment['urgency_status']; ?> h-100">
                                <div class="card-body position-relative">
                                    <!-- Urgency Indicator -->
                                    <?php if ($assignment['urgency_status'] !== 'normal'): ?>
                                        <div class="urgency-indicator <?php echo $assignment['urgency_status']; ?>"></div>
                                    <?php endif; ?>
                                    
                                    <!-- Status Badge -->
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge status-badge <?php echo $assignment['student_status']; ?> text-white">
                                            <?php echo ucfirst(str_replace('_', ' ', $assignment['student_status'])); ?>
                                        </span>
                                        <small class="text-muted"><?php echo htmlspecialchars($assignment['subject']); ?></small>
                                    </div>

                                    <!-- Assignment Title -->
                                    <h5 class="card-title"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                                    
                                    <!-- Description -->
                                    <p class="card-text text-muted">
                                        <?php 
                                        $description = htmlspecialchars($assignment['description']);
                                        echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                        ?>
                                    </p>

                                    <!-- Assignment Details -->
                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Points</small>
                                            <div class="fw-bold text-primary"><?php echo $assignment['points']; ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Days Left</small>
                                            <div class="fw-bold <?php echo $assignment['days_remaining'] < 0 ? 'text-danger' : ($assignment['days_remaining'] <= 2 ? 'text-warning' : 'text-success'); ?>">
                                                <?php 
                                                if ($assignment['days_remaining'] < 0) {
                                                    echo abs($assignment['days_remaining']) . ' overdue';
                                                } else {
                                                    echo $assignment['days_remaining'];
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Due Date -->
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Due: <?php echo formatDate($assignment['due_date']); ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Teacher: <?php echo htmlspecialchars($assignment['teacher_name']); ?>
                                        </small>
                                    </div>

                                    <!-- Score Display (if completed) -->
                                    <?php if ($assignment['student_status'] === 'completed' && $assignment['score'] !== null): ?>
                                        <div class="alert alert-success py-2">
                                            <strong><i class="fas fa-star me-1"></i>Score: <?php echo number_format($assignment['score'], 1); ?></strong>
                                            <small class="text-muted d-block mt-1">
                                                <i class="fas fa-robot me-1"></i>AI-Simulated Score (Demo) | 
                                                Submitted: <?php echo formatDate($assignment['submitted_at']); ?>
                                            </small>
                                            <div class="mt-2">
                                                <small class="text-info">
                                                    <i class="fas fa-lightbulb me-1"></i>
                                                    <strong>Planned AI Features:</strong> Real NLP analysis will provide detailed feedback on grammar, structure, and content relevance.
                                                </small>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2">
                                        <?php if ($assignment['student_status'] === 'not_started'): ?>
                                            <button class="btn btn-primary btn-sm flex-fill" onclick="startAssignment(<?php echo $assignment['id']; ?>)">
                                                <i class="fas fa-play me-1"></i> Start
                                            </button>
                                        <?php elseif ($assignment['student_status'] === 'in_progress'): ?>
                                            <button class="btn btn-success btn-sm flex-fill" onclick="submitAssignment(<?php echo $assignment['id']; ?>, '<?php echo htmlspecialchars($assignment['title']); ?>')">
                                                <i class="fas fa-upload me-1"></i> Submit
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-outline-success btn-sm flex-fill" disabled>
                                                <i class="fas fa-check me-1"></i> Completed
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-outline-info btn-sm" onclick="viewAssignmentDetails(<?php echo $assignment['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No assignments found</h5>
                                <p class="text-muted">
                                    <?php if ($status_filter !== 'all' || $subject_filter !== 'all'): ?>
                                        Try adjusting your filters to see more assignments.
                                    <?php else: ?>
                                        New assignments will appear here when your teachers create them.
                                    <?php endif; ?>
                                </p>
                                <?php if ($status_filter !== 'all' || $subject_filter !== 'all'): ?>
                                    <a href="assignments.php" class="btn btn-primary">
                                        <i class="fas fa-undo me-1"></i> Clear Filters
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                </div> <!-- End container-fluid -->
            </main>
        </div> <!-- End content-wrapper -->
    </div> <!-- End main-wrapper -->

    <!-- Assignment Submission Modal -->
    <div class="modal fade submission-modal" id="submissionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="submissionModalTitle">
                        <i class="fas fa-upload me-2"></i>Submit Assignment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="submissionModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter change handlers
        document.getElementById('statusFilter').addEventListener('change', function() {
            updateFilters();
        });

        document.getElementById('subjectFilter').addEventListener('change', function() {
            updateFilters();
        });

        function updateFilters() {
            const status = document.getElementById('statusFilter').value;
            const subject = document.getElementById('subjectFilter').value;
            
            const params = new URLSearchParams();
            if (status !== 'all') params.append('status', status);
            if (subject !== 'all') params.append('subject', subject);
            
            const newUrl = 'assignments.php' + (params.toString() ? '?' + params.toString() : '');
            window.location.href = newUrl;
        }

        function startAssignment(assignmentId) {
            if (!confirm('Are you sure you want to start this assignment?')) {
                return;
            }

            fetch('assignments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=start_assignment&assignment_id=${assignmentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Network error. Please try again.');
            });
        }

        function submitAssignment(assignmentId, assignmentTitle) {
            document.getElementById('submissionModalTitle').innerHTML = `
                <i class="fas fa-upload me-2"></i>Submit: ${assignmentTitle}
            `;
            
            document.getElementById('submissionModalBody').innerHTML = `
                <form id="submissionForm">
                    <div class="mb-3">
                        <label for="submissionText" class="form-label">Your Submission</label>
                        <textarea class="form-control" id="submissionText" rows="6" 
                                  placeholder="Enter your assignment solution, answers, or attach relevant content..."
                                  required></textarea>
                        <div class="form-text">Provide your complete solution or attach relevant files.</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Demo Notice:</strong> This is a proof-of-concept demonstration. In the full implementation, AI will provide:
                        <ul class="mb-0 mt-2">
                            <li><strong>Real-time NLP Analysis:</strong> Grammar checking and content analysis as you type</li>
                            <li><strong>Intelligent Scoring:</strong> Context-aware evaluation based on assignment requirements</li>
                            <li><strong>Detailed Feedback:</strong> Specific suggestions for improvement</li>
                            <li><strong>Draft Management:</strong> Auto-save and revision tracking capabilities</li>
                        </ul>
                        <small class="text-muted mt-2 d-block">Current submission will receive a simulated AI score for demonstration purposes.</small>
                    </div>
                </form>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success" onclick="confirmSubmission(${assignmentId})">
                        <i class="fas fa-upload me-1"></i> Submit Assignment
                    </button>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('submissionModal'));
            modal.show();
        }

        function confirmSubmission(assignmentId) {
            const submissionText = document.getElementById('submissionText').value.trim();
            
            if (!submissionText) {
                alert('Please provide your submission content.');
                return;
            }

            if (!confirm('Are you sure you want to submit this assignment? You cannot make changes after submission.')) {
                return;
            }

            // Show loading
            document.getElementById('submissionModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Submitting your assignment...</p>
                </div>
            `;

            fetch('assignments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=submit_assignment&assignment_id=${assignmentId}&submission_text=${encodeURIComponent(submissionText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('submissionModalBody').innerHTML = `
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                            <h5>Assignment Submitted Successfully!</h5>
                            <p>Your simulated AI score: <strong>${data.score}</strong></p>
                            <div class="alert alert-info mt-3 mb-3">
                                <small>
                                    <i class="fas fa-robot me-1"></i>
                                    <strong>Demo Mode:</strong> This score is generated for demonstration. 
                                    In production, our NLP AI will analyze your content for grammar, structure, relevance, and provide detailed feedback.
                                </small>
                            </div>
                            <p class="mb-0">${data.message}</p>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh Page
                            </button>
                        </div>
                    `;
                } else {
                    document.getElementById('submissionModalBody').innerHTML = `
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
                document.getElementById('submissionModalBody').innerHTML = `
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

        function viewAssignmentDetails(assignmentId) {
            // This would open a detailed view of the assignment
            // For now, we'll just show an alert
            alert('Assignment details view - Feature coming soon!');
        }

        // Auto-refresh every 5 minutes to update due dates and overdue status
        setInterval(() => {
            // Only refresh if there are pending assignments
            const pendingCards = document.querySelectorAll('.assignment-card.not-started, .assignment-card.in-progress');
            if (pendingCards.length > 0) {
                location.reload();
            }
        }, 300000); // 5 minutes
    </script>
</body>
</html>
