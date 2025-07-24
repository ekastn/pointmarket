<?php
require_once 'includes/config.php';
requireLogin();
requireRole('guru');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Update overdue evaluations
updateOverdueEvaluations($pdo);

// Get students and their evaluation status
function getStudentEvaluationStatus($pdo) {
    try {
        $current_week = getCurrentWeekNumber();
        $current_year = getCurrentYear();
        
        $stmt = $pdo->prepare("
            SELECT 
                u.id as student_id,
                u.name as student_name,
                u.email as student_email,
                COUNT(CASE WHEN we.status = 'completed' THEN 1 END) as completed_this_week,
                COUNT(CASE WHEN we.status = 'pending' THEN 1 END) as pending_this_week,
                COUNT(CASE WHEN we.status = 'overdue' THEN 1 END) as overdue_this_week,
                AVG(CASE WHEN qr.week_number = ? AND qr.year = ? AND q.type = 'mslq' THEN qr.total_score END) as mslq_score_this_week,
                AVG(CASE WHEN qr.week_number = ? AND qr.year = ? AND q.type = 'ams' THEN qr.total_score END) as ams_score_this_week,
                MAX(qr.completed_at) as last_evaluation
            FROM users u
            LEFT JOIN weekly_evaluations we ON (u.id = we.student_id AND we.week_number = ? AND we.year = ?)
            LEFT JOIN questionnaire_results qr ON (u.id = qr.student_id AND qr.week_number = ? AND qr.year = ?)
            LEFT JOIN questionnaires q ON qr.questionnaire_id = q.id
            WHERE u.role = 'siswa'
            GROUP BY u.id, u.name, u.email
            ORDER BY u.name
        ");
        
        $stmt->execute([
            $current_week, $current_year, 
            $current_week, $current_year,
            $current_week, $current_year,
            $current_week, $current_year
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting student evaluation status: " . $e->getMessage());
        return [];
    }
}

// Get weekly progress for all students
function getWeeklyProgressOverview($pdo, $weeks = 4) {
    try {
        $current_week = getCurrentWeekNumber();
        $current_year = getCurrentYear();
        
        $stmt = $pdo->prepare("
            SELECT 
                we.week_number,
                we.year,
                q.type as questionnaire_type,
                COUNT(CASE WHEN we.status = 'completed' THEN 1 END) as completed_count,
                COUNT(CASE WHEN we.status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN we.status = 'overdue' THEN 1 END) as overdue_count,
                COUNT(*) as total_count,
                ROUND(AVG(qr.total_score), 2) as average_score
            FROM weekly_evaluations we
            JOIN questionnaires q ON we.questionnaire_id = q.id
            LEFT JOIN questionnaire_results qr ON (
                qr.student_id = we.student_id 
                AND qr.questionnaire_id = we.questionnaire_id 
                AND qr.week_number = we.week_number 
                AND qr.year = we.year
            )
            WHERE ((we.year = ? AND we.week_number >= ?) OR we.year > ?)
            GROUP BY we.week_number, we.year, q.type
            ORDER BY we.year DESC, we.week_number DESC, q.type
            LIMIT ?
        ");
        
        $start_week = max(1, $current_week - $weeks + 1);
        $limit = $weeks * 2; // 2 questionnaires per week
        
        $stmt->execute([$current_year, $start_week, $current_year, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting weekly progress overview: " . $e->getMessage());
        return [];
    }
}

// Get detailed student data for modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if (isset($_POST['action']) && $_POST['action'] === 'get_student_detail') {
        $student_id = (int)$_POST['student_id'];
        
        try {
            // Get student info
            $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ? AND role = 'siswa'");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                echo json_encode(['success' => false, 'message' => 'Student not found']);
                exit;
            }
            
            // Get student's evaluation progress
            $progress = getWeeklyEvaluationProgress($student_id, $pdo, 8);
            
            echo json_encode([
                'success' => true,
                'student' => $student,
                'progress' => $progress
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

$studentStatus = getStudentEvaluationStatus($pdo);
$weeklyOverview = getWeeklyProgressOverview($pdo, 4);

$messages = getMessages();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Evaluation Monitoring - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .status-card {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
        }
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .status-badge.completed {
            background-color: #198754;
        }
        .status-badge.pending {
            background-color: #fd7e14;
        }
        .status-badge.overdue {
            background-color: #dc3545;
        }
        .progress-chart {
            height: 300px;
        }
        .student-row:hover {
            background-color: #f8f9fa;
        }
        .score-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
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
        .weekly-overview-chart {
            height: 200px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
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
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student Evaluation Status - Week <?php echo getCurrentWeekNumber(); ?>/<?php echo getCurrentYear(); ?></h5>
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
                                                        <small><?php echo formatDate($student['last_evaluation']); ?></small>
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
            </main>
        </div>
    </div>

    <!-- Student Detail Modal -->
    <div class="modal fade" id="studentDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentDetailModalTitle">
                        <i class="fas fa-user me-2"></i>Student Evaluation Detail
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="studentDetailModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchStudent').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#studentTable tbody tr');
            
            rows.forEach(row => {
                const studentName = row.cells[0].textContent.toLowerCase();
                const studentEmail = row.cells[1].textContent.toLowerCase();
                
                if (studentName.includes(searchTerm) || studentEmail.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function viewStudentDetail(studentId) {
            // Show loading
            document.getElementById('studentDetailModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Loading student data...</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('studentDetailModal'));
            modal.show();
            
            // Load student data
            fetch('teacher-evaluation-monitoring.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=get_student_detail&student_id=${studentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadStudentDetail(data.student, data.progress);
                } else {
                    document.getElementById('studentDetailModalBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading student data: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('studentDetailModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Network error. Please try again.
                    </div>
                `;
            });
        }

        function loadStudentDetail(student, progress) {
            document.getElementById('studentDetailModalTitle').innerHTML = `
                <i class="fas fa-user me-2"></i>${student.name} - Evaluation History
            `;
            
            let html = `
                <div class="mb-3">
                    <h6>Student Information</h6>
                    <p><strong>Name:</strong> ${student.name}<br>
                    <strong>Email:</strong> ${student.email}</p>
                </div>
                <hr>
                <h6>Evaluation History (Last 8 Weeks)</h6>
            `;
            
            if (progress && progress.length > 0) {
                html += `
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Week</th>
                                    <th>Questionnaire</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                progress.forEach(p => {
                    const statusClass = {
                        'completed': 'success',
                        'pending': 'warning',
                        'overdue': 'danger'
                    };
                    
                    html += `
                        <tr>
                            <td>${p.week_number}/${p.year}</td>
                            <td>${p.questionnaire_name} <small class="text-muted">(${p.questionnaire_type.toUpperCase()})</small></td>
                            <td>
                                <span class="badge bg-${statusClass[p.status]}">
                                    ${p.status.charAt(0).toUpperCase() + p.status.slice(1)}
                                </span>
                            </td>
                            <td>
                                ${p.total_score !== null ? 
                                    `<strong>${parseFloat(p.total_score).toFixed(2)}</strong>` : 
                                    '<span class="text-muted">-</span>'
                                }
                            </td>
                            <td>
                                ${p.completed_at ? 
                                    `<small>${new Date(p.completed_at).toLocaleDateString('id-ID')}</small>` : 
                                    '<span class="text-muted">Not completed</span>'
                                }
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html += `
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No evaluation data available</h6>
                        <p class="text-muted">This student hasn't completed any evaluations yet.</p>
                    </div>
                `;
            }
            
            document.getElementById('studentDetailModalBody').innerHTML = html;
        }

        function exportData() {
            // Simple CSV export functionality
            const rows = [];
            const table = document.getElementById('studentTable');
            
            // Header
            const headerCells = table.querySelectorAll('thead th');
            const header = Array.from(headerCells).slice(0, -1).map(th => th.textContent.trim());
            rows.push(header.join(','));
            
            // Data rows
            const dataRows = table.querySelectorAll('tbody tr');
            dataRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const cells = row.querySelectorAll('td');
                    const rowData = Array.from(cells).slice(0, -1).map(td => {
                        return `"${td.textContent.trim().replace(/"/g, '""')}"`;
                    });
                    rows.push(rowData.join(','));
                }
            });
            
            // Download
            const csvContent = rows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `student_evaluation_status_week_${<?php echo getCurrentWeekNumber(); ?>}_${<?php echo getCurrentYear(); ?>}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
