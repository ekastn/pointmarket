<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Update overdue evaluations
updateOverdueEvaluations($pdo);

// Generate weekly evaluations if needed
generateWeeklyEvaluations($pdo);

// Get pending evaluations
$pendingEvaluations = getPendingWeeklyEvaluations($user['id'], $pdo);

// Get evaluation progress
$evaluationProgress = getWeeklyEvaluationProgress($user['id'], $pdo, 8);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if (isset($_POST['action']) && $_POST['action'] === 'get_questionnaire') {
        $questionnaire_id = (int)$_POST['questionnaire_id'];
        
        try {
            // Get questionnaire details
            $stmt = $pdo->prepare("SELECT * FROM questionnaires WHERE id = ?");
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
            
            echo json_encode([
                'success' => true,
                'questionnaire' => $questionnaire,
                'questions' => $questions
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'submit_questionnaire') {
        $questionnaire_id = (int)$_POST['questionnaire_id'];
        $week_number = (int)$_POST['week_number'];
        $year = (int)$_POST['year'];
        $answers = $_POST['answers'] ?? [];
        
        if (empty($answers)) {
            echo json_encode(['success' => false, 'message' => 'Please answer all questions']);
            exit;
        }
        
        try {
            $pdo->beginTransaction();
            
            // Calculate total score (simplified - in real implementation, use proper scoring algorithms)
            $total_score = 0;
            $answer_count = 0;
            foreach ($answers as $answer) {
                $total_score += (int)$answer;
                $answer_count++;
            }
            $average_score = $answer_count > 0 ? $total_score / $answer_count : 0;
            
            // Check if already submitted for this week
            $stmt = $pdo->prepare("
                SELECT id FROM questionnaire_results 
                WHERE student_id = ? AND questionnaire_id = ? AND week_number = ? AND year = ?
            ");
            $stmt->execute([$user['id'], $questionnaire_id, $week_number, $year]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'You have already submitted this questionnaire for this week']);
                $pdo->rollback();
                exit;
            }
            
            // Insert questionnaire result
            $stmt = $pdo->prepare("
                INSERT INTO questionnaire_results 
                (student_id, questionnaire_id, answers, total_score, week_number, year, completed_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user['id'],
                $questionnaire_id,
                json_encode($answers),
                $average_score,
                $week_number,
                $year
            ]);
            
            // Mark weekly evaluation as completed
            completeWeeklyEvaluation($user['id'], $questionnaire_id, $week_number, $year, $pdo);
            
            // Log activity
            $stmt = $pdo->prepare("SELECT name FROM questionnaires WHERE id = ?");
            $stmt->execute([$questionnaire_id]);
            $questionnaire_name = $stmt->fetchColumn();
            
            logActivity($user['id'], 'questionnaire_completed', "Completed $questionnaire_name for week $week_number/$year", $pdo);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Questionnaire submitted successfully!',
                'score' => round($average_score, 2)
            ]);
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Error submitting questionnaire: ' . $e->getMessage()]);
        }
        exit;
    }
}

$messages = getMessages();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Evaluations - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .evaluation-card {
            border-left: 4px solid #0d6efd;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .evaluation-card.pending {
            border-left-color: #fd7e14;
        }
        .evaluation-card.overdue {
            border-left-color: #dc3545;
        }
        .evaluation-card.completed {
            border-left-color: #198754;
        }
        .progress-chart {
            max-height: 400px;
        }
        .questionnaire-modal .modal-dialog {
            max-width: 800px;
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
        .weekly-calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        .week-day {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            background: white;
        }
        .week-day.completed {
            background: #d1edff;
            border-color: #0d6efd;
        }
        .week-day.pending {
            background: #fff3cd;
            border-color: #fd7e14;
        }
        .week-day.overdue {
            background: #f8d7da;
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="main-wrapper">
        <div class="content-wrapper">
            <div class="sidebar-wrapper">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            
            <main class="main-content">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-calendar-check me-2"></i>Weekly Evaluations</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="refreshProgress()">
                                <i class="fas fa-sync-alt"></i> Refresh
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

                <!-- Pending Evaluations -->
                <?php if (!empty($pendingEvaluations)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Pending Weekly Evaluations</h5>
                            <p class="mb-0">You have <?php echo count($pendingEvaluations); ?> evaluation(s) that need to be completed this week.</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <?php foreach ($pendingEvaluations as $eval): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card evaluation-card pending h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    <?php echo htmlspecialchars($eval['questionnaire_name']); ?>
                                </h5>
                                <p class="card-text">
                                    <strong>Type:</strong> <?php echo strtoupper($eval['questionnaire_type']); ?><br>
                                    <strong>Week:</strong> <?php echo $eval['week_number']; ?>/<?php echo $eval['year']; ?><br>
                                    <strong>Due:</strong> <?php echo formatDate($eval['due_date']); ?>
                                </p>
                                <button class="btn btn-primary btn-sm" onclick="startQuestionnaire(<?php echo $eval['questionnaire_id']; ?>, <?php echo $eval['week_number']; ?>, <?php echo $eval['year']; ?>)">
                                    <i class="fas fa-play me-1"></i> Start Evaluation
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Progress Overview -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Weekly Evaluation Progress</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($evaluationProgress)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Week</th>
                                                    <th>Year</th>
                                                    <th>Questionnaire</th>
                                                    <th>Status</th>
                                                    <th>Score</th>
                                                    <th>Completed</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($evaluationProgress as $progress): ?>
                                                <tr>
                                                    <td><?php echo $progress['week_number']; ?></td>
                                                    <td><?php echo $progress['year']; ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($progress['questionnaire_name']); ?>
                                                        <small class="text-muted d-block"><?php echo strtoupper($progress['questionnaire_type']); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusClass = [
                                                            'completed' => 'success',
                                                            'pending' => 'warning',
                                                            'overdue' => 'danger'
                                                        ];
                                                        $statusIcon = [
                                                            'completed' => 'check-circle',
                                                            'pending' => 'clock',
                                                            'overdue' => 'exclamation-triangle'
                                                        ];
                                                        ?>
                                                        <span class="badge bg-<?php echo $statusClass[$progress['status']]; ?>">
                                                            <i class="fas fa-<?php echo $statusIcon[$progress['status']]; ?> me-1"></i>
                                                            <?php echo ucfirst($progress['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($progress['total_score'] !== null): ?>
                                                            <strong><?php echo number_format($progress['total_score'], 2); ?></strong>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($progress['completed_at']): ?>
                                                            <?php echo formatDate($progress['completed_at']); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not completed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No evaluation data available</h5>
                                        <p class="text-muted">Complete your first weekly evaluation to see progress data.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>About Weekly Evaluations</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>MSLQ (Motivated Strategies for Learning Questionnaire)</strong> helps us understand your learning motivation and strategies.</p>
                                <p><strong>AMS (Academic Motivation Scale)</strong> measures your academic motivation levels and types.</p>
                                <p class="mb-0">These weekly evaluations help the POINTMARKET AI system provide you with personalized recommendations and track your learning progress over time.</p>
                                <hr>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    <strong>Tip:</strong> Be honest in your responses. The AI uses this data to create better learning experiences just for you!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Questionnaire Modal -->
    <div class="modal fade questionnaire-modal" id="questionnaireModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionnaireModalTitle">
                        <i class="fas fa-clipboard-list me-2"></i>Weekly Evaluation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="questionnaireModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentQuestionnaire = null;
        let currentWeek = null;
        let currentYear = null;

        function startQuestionnaire(questionnaireId, weekNumber, year) {
            currentQuestionnaire = questionnaireId;
            currentWeek = weekNumber;
            currentYear = year;
            
            // Show loading
            document.getElementById('questionnaireModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Loading questionnaire...</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('questionnaireModal'));
            modal.show();
            
            // Load questionnaire data
            fetch('weekly-evaluations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=get_questionnaire&questionnaire_id=${questionnaireId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadQuestionnaireForm(data.questionnaire, data.questions);
                } else {
                    document.getElementById('questionnaireModalBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading questionnaire: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('questionnaireModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Network error. Please try again.
                    </div>
                `;
            });
        }

        function loadQuestionnaireForm(questionnaire, questions) {
            document.getElementById('questionnaireModalTitle').innerHTML = `
                <i class="fas fa-clipboard-list me-2"></i>${questionnaire.name}
            `;
            
            let html = `
                <div class="mb-3">
                    <p class="text-muted">${questionnaire.description}</p>
                    <p><strong>Week:</strong> ${currentWeek}/${currentYear}</p>
                    <p><strong>Instructions:</strong> Please rate each statement on a scale of 1-7, where:</p>
                    <div class="row text-center mb-3">
                        <div class="col">1 = Not at all true of me</div>
                        <div class="col">4 = Somewhat true of me</div>
                        <div class="col">7 = Very true of me</div>
                    </div>
                </div>
                <form id="questionnaireForm">
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
                                    <div class="scale-option" onclick="selectAnswer(${question.question_number}, ${value})">
                                        <strong>${value}</strong>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <input type="hidden" name="answers[${question.question_number}]" id="answer_${question.question_number}">
                    </div>
                `;
            });
            
            html += `
                </form>
                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="submitQuestionnaire()">
                        <i class="fas fa-check me-1"></i> Submit Evaluation
                    </button>
                </div>
            `;
            
            document.getElementById('questionnaireModalBody').innerHTML = html;
        }

        function selectAnswer(questionNumber, value) {
            // Remove previous selection
            document.querySelectorAll(`[onclick*="selectAnswer(${questionNumber}"]`).forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selection to clicked option
            event.target.classList.add('selected');
            
            // Set hidden input value
            document.getElementById(`answer_${questionNumber}`).value = value;
        }

        function submitQuestionnaire() {
            const form = document.getElementById('questionnaireForm');
            const formData = new FormData(form);
            formData.append('ajax', '1');
            formData.append('action', 'submit_questionnaire');
            formData.append('questionnaire_id', currentQuestionnaire);
            formData.append('week_number', currentWeek);
            formData.append('year', currentYear);
            
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
            
            // Convert FormData to URLSearchParams for easier handling
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                params.append(key, value);
            }
            
            // Add answers
            for (const [questionNumber, answer] of Object.entries(answers)) {
                params.append(`answers[${questionNumber}]`, answer);
            }
            
            // Show loading
            document.getElementById('questionnaireModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Submitting your evaluation...</p>
                </div>
            `;
            
            fetch('weekly-evaluations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('questionnaireModalBody').innerHTML = `
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                            <h5>Evaluation Submitted Successfully!</h5>
                            <p>Your score: <strong>${data.score}</strong></p>
                            <p class="mb-0">${data.message}</p>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh Page
                            </button>
                        </div>
                    `;
                } else {
                    document.getElementById('questionnaireModalBody').innerHTML = `
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
                document.getElementById('questionnaireModalBody').innerHTML = `
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

        function refreshProgress() {
            location.reload();
        }

        // Auto-refresh pending evaluations every 5 minutes
        setInterval(() => {
            const pendingSection = document.querySelector('.alert-warning');
            if (pendingSection) {
                location.reload();
            }
        }, 300000); // 5 minutes
    </script>
</body>
</html>
