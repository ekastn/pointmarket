<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Get available questionnaires (MSLQ, AMS - exclude VARK)
function getAvailableQuestionnaires($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT id, name, description, type, total_questions, status 
            FROM questionnaires 
            WHERE status = 'active' AND type IN ('mslq', 'ams')
            ORDER BY type
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting questionnaires: " . $e->getMessage());
        return [];
    }
}

// Get student's questionnaire history (flexible - not tied to weeks)
function getQuestionnaireHistory($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                qr.id,
                qr.total_score,
                qr.completed_at,
                q.name as questionnaire_name,
                q.type as questionnaire_type,
                q.description as questionnaire_description
            FROM questionnaire_results qr
            JOIN questionnaires q ON qr.questionnaire_id = q.id
            WHERE qr.student_id = ? AND q.type IN ('mslq', 'ams')
            ORDER BY qr.completed_at DESC
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
        $stmt = $pdo->prepare("
            SELECT 
                q.type,
                q.name,
                COUNT(qr.id) as total_completed,
                AVG(qr.total_score) as average_score,
                MAX(qr.total_score) as best_score,
                MIN(qr.total_score) as lowest_score,
                MAX(qr.completed_at) as last_completed
            FROM questionnaires q
            LEFT JOIN questionnaire_results qr ON (q.id = qr.questionnaire_id AND qr.student_id = ?)
            WHERE q.status = 'active' AND q.type IN ('mslq', 'ams')
            GROUP BY q.id, q.type, q.name
            ORDER BY q.type
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            
            // Insert questionnaire result (no week_number/year constraints)
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
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Questionnaire completed successfully!',
                'score' => round($average_score, 2)
            ]);
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Error submitting questionnaire: ' . $e->getMessage()]);
        }
        exit;
    }
}

$questionnaires = getAvailableQuestionnaires($pdo);
$history = getQuestionnaireHistory($user['id'], $pdo);
$stats = getQuestionnaireStats($user['id'], $pdo);

$messages = getMessages();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Kuesioner - POINTMARKET</title>
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
        .question-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .scale-option {
            cursor: pointer;
            text-align: center;
            padding: 0.5rem;
            border-radius: 4px;
            border: 2px solid #dee2e6;
            background: white;
            transition: all 0.2s ease;
        }
        .scale-option:hover {
            border-color: #0d6efd;
            background: #e7f3ff;
        }
        .scale-option.selected {
            border-color: #0d6efd;
            background: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="content-wrapper">
            <div class="sidebar-wrapper">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            
            <div class="main-content">
                <div class="container-fluid py-4">
                    <!-- Display Messages -->
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message['text']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h2><i class="fas fa-chart-line me-2"></i>Progress Kuesioner</h2>
                            <p class="text-muted">Pantau kemajuan pengisian kuesioner MSLQ dan AMS Anda. Isi kapan saja untuk tracking motivasi dan strategi belajar.</p>
                            
                            <!-- AI Implementation Notice -->
                            <div class="alert alert-primary mb-3">
                                <h6><i class="fas fa-robot me-2"></i>AI-Powered Learning Personalization</h6>
                                <p class="mb-2">Kuesioner ini membantu AI POINTMARKET memahami profil belajar Anda:</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>🧠 NLP Integration:</strong> Hasil kuesioner menentukan gaya feedback AI untuk assignments.
                                    </div>
                                    <div class="col-md-4">
                                        <strong>🎯 RL Optimization:</strong> Rekomendasi jadwal belajar berdasarkan motivasi dan strategi.
                                    </div>
                                    <div class="col-md-4">
                                        <strong>📚 CBF Matching:</strong> Materi pembelajaran dipersonalisasi sesuai profil motivasi Anda.
                                    </div>
                                </div>
                                <hr>
                                <p class="mb-2"><strong>💡 Fleksibilitas:</strong> Isi kuesioner kapan saja untuk mengukur perubahan motivasi dan strategi belajar Anda dari waktu ke waktu.</p>
                                <div class="text-center">
                                    <a href="vark-correlation-analysis.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-chart-network me-1"></i>Lihat Analisis Korelasi VARK-MSLQ-AMS
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Overview -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4><i class="fas fa-chart-bar me-2"></i>Statistik Kuesioner Anda</h4>
                        </div>
                        <?php foreach ($stats as $stat): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-<?php echo $stat['type'] === 'mslq' ? 'brain' : 'heart'; ?> me-2"></i>
                                        <?php echo htmlspecialchars($stat['name']); ?>
                                    </h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Selesai:</strong></p>
                                            <h4 class="text-primary"><?php echo $stat['total_completed'] ?? 0; ?>x</h4>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Rata-rata Skor:</strong></p>
                                            <?php if ($stat['average_score'] !== null): ?>
                                                <?php 
                                                $avg_score = $stat['average_score'];
                                                $scoreClass = $avg_score >= 5.5 ? 'score-high' : ($avg_score >= 4 ? 'score-medium' : 'score-low');
                                                ?>
                                                <h4 class="<?php echo $scoreClass; ?>"><?php echo number_format($avg_score, 2); ?></h4>
                                            <?php else: ?>
                                                <h4 class="text-muted">-</h4>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($stat['last_completed']): ?>
                                        <small class="text-muted">
                                            Terakhir: <?php echo formatDate($stat['last_completed']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- VARK Correlation Analysis -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-chart-network me-2"></i>Analisis Korelasi Learning Style</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6>📊 Korelasi VARK dengan MSLQ & AMS</h6>
                                            <p class="mb-2">Lihat bagaimana gaya belajar VARK Anda berkorelasi dengan motivasi (AMS) dan strategi pembelajaran (MSLQ). Analisis ini membantu memahami pola belajar personal Anda.</p>
                                            <ul class="small mb-0">
                                                <li><strong>Visual:</strong> Korelasi tinggi dengan Organization & Elaboration strategies</li>
                                                <li><strong>Auditory:</strong> Korelasi kuat dengan Help Seeking & Social Learning</li>
                                                <li><strong>Reading/Writing:</strong> Korelasi tertinggi dengan Metacognitive strategies</li>
                                                <li><strong>Kinesthetic:</strong> Korelasi tinggi dengan Effort Regulation & praktis</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <a href="vark-correlation-analysis.php" class="btn btn-info">
                                                <i class="fas fa-analytics me-2"></i>Lihat Analisis Korelasi
                                            </a>
                                            <br><small class="text-muted mt-2 d-block">Memerlukan data VARK, MSLQ, dan AMS</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Available Questionnaires -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4><i class="fas fa-list me-2"></i>Kuesioner Tersedia</h4>
                            <p class="text-muted">Isi kuesioner kapan saja untuk membantu AI memahami motivasi dan strategi belajar Anda.</p>
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
                                            <?php echo $questionnaire['total_questions']; ?> Pertanyaan
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Estimasi: <?php echo ceil($questionnaire['total_questions'] * 0.5); ?> menit
                                        </small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary btn-sm" onclick="startQuestionnaire(<?php echo $questionnaire['id']; ?>)">
                                            <i class="fas fa-play me-1"></i> Mulai Isi
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

                    <!-- Progress History -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Kuesioner</h5>
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
                                                        </small>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php 
                                                        $score = $item['total_score'];
                                                        $scoreClass = $score >= 5.5 ? 'score-high' : ($score >= 4 ? 'score-medium' : 'score-low');
                                                        ?>
                                                        <span class="score-badge <?php echo $scoreClass; ?>">
                                                            Skor: <?php echo number_format($score, 2); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Belum ada riwayat kuesioner</h5>
                                            <p class="text-muted">Mulai isi kuesioner pertama Anda untuk melihat progress di sini.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Questionnaire Modal -->
    <div class="modal fade" id="questionnaireModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionnaireModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="questionnaireModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Info Modal -->
    <div class="modal fade" id="questionnaireInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Informasi Kuesioner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="questionnaireInfoModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentQuestionnaireId = null;

        function viewQuestionnaireInfo(questionnaireId) {
            // Simple info display
            const questionnaires = {
                1: {
                    name: 'MSLQ (Motivated Strategies for Learning)',
                    description: 'Mengukur motivasi dan strategi belajar Anda',
                    details: 'Kuesioner ini terdiri dari 81 pertanyaan yang mengukur berbagai aspek motivasi dan strategi belajar, termasuk motivasi intrinsik, self-efficacy, dan strategi kognitif.'
                },
                2: {
                    name: 'AMS (Academic Motivation Scale)',
                    description: 'Mengukur tipe motivasi akademik Anda',
                    details: 'Kuesioner ini terdiri dari 28 pertanyaan yang mengukur berbagai tipe motivasi akademik, dari motivasi intrinsik hingga amotivation.'
                }
            };
            
            const info = questionnaires[questionnaireId];
            if (info) {
                document.getElementById('questionnaireInfoModalBody').innerHTML = `
                    <h6>${info.name}</h6>
                    <p><strong>Deskripsi:</strong> ${info.description}</p>
                    <p><strong>Detail:</strong> ${info.details}</p>
                    <div class="alert alert-info">
                        <strong>Tips:</strong> Jawab dengan jujur berdasarkan kondisi Anda saat ini. Tidak ada jawaban benar atau salah.
                    </div>
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('questionnaireInfoModal'));
                modal.show();
            }
        }

        function startQuestionnaire(questionnaireId) {
            currentQuestionnaireId = questionnaireId;
            
            // Show loading
            document.getElementById('questionnaireModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Memuat kuesioner...</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('questionnaireModal'));
            modal.show();
            
            // Load questionnaire data
            fetch('questionnaire-progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=get_questionnaire_detail&questionnaire_id=${questionnaireId}`
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
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Petunjuk Pengisian</h6>
                    <p class="mb-0">Beri nilai pada setiap pernyataan dengan skala 1-7:</p>
                    <div class="row text-center mt-2">
                        <div class="col">1 = Sangat tidak sesuai</div>
                        <div class="col">4 = Cukup sesuai</div>
                        <div class="col">7 = Sangat sesuai</div>
                    </div>
                </div>
                
                <form id="questionnaireForm">
                    <div style="max-height: 400px; overflow-y: auto;">
            `;
            
            questions.forEach((question, index) => {
                html += `
                    <div class="question-card p-3 mb-3">
                        <div class="mb-2">
                            <strong>Pertanyaan ${question.question_number}:</strong>
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
                    </div>
                </form>
                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-success" onclick="submitQuestionnaire()">
                        <i class="fas fa-check me-1"></i> Kirim Jawaban
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
            const formData = new FormData(document.getElementById('questionnaireForm'));
            const answers = {};
            
            // Collect answers
            formData.forEach((value, key) => {
                const match = key.match(/answers\[(\d+)\]/);
                if (match && value) {
                    answers[match[1]] = value;
                }
            });
            
            // Validate all questions answered
            const totalQuestions = document.querySelectorAll('input[name^="answers["]').length;
            if (Object.keys(answers).length < totalQuestions) {
                alert('Silakan jawab semua pertanyaan sebelum mengirim.');
                return;
            }
            
            // Submit data
            fetch('questionnaire-progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=submit_questionnaire&questionnaire_id=${currentQuestionnaireId}&${Object.entries(answers).map(([k,v]) => `answers[${k}]=${v}`).join('&')}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('questionnaireModalBody').innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>Kuesioner Berhasil Dikirim!</h5>
                            <p>Skor Anda: <strong>${data.score}</strong></p>
                            <button type="button" class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-refresh me-1"></i> Refresh Halaman
                            </button>
                        </div>
                    `;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Network error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>
