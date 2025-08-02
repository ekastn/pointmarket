<?php
// VARK Assessment - Version 2.0 - Fixed includes issue
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Get VARK questions first
$varkQuestions = getVARKQuestions($pdo);

// Check if student has completed VARK before
$existingResult = getStudentVARKResult($user['id'], $pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vark'])) {
    $answers = [];
    
    // Collect answers
    for ($i = 1; $i <= 16; $i++) {
        if (isset($_POST["question_$i"])) {
            $answers[$i] = $_POST["question_$i"];
        }
    }
    
    if (count($answers) == 16) {
        $varkResult = calculateVARKScore($answers, $pdo);
        
        if ($varkResult) {
            $resultId = saveVARKResult(
                $user['id'],
                $varkResult['scores'],
                $varkResult['dominant_style'],
                $varkResult['learning_preference'],
                $answers,
                $pdo
            );
            
            if ($resultId) {
                logActivity($user['id'], 'VARK_COMPLETED', 'Completed VARK Learning Style Assessment', $pdo);
                $success_message = "Asesmen VARK berhasil diselesaikan! Gaya belajar Anda: " . $varkResult['dominant_style'];
                $show_result = true;
                $result_data = $varkResult;
            } else {
                $error_message = "Gagal menyimpan hasil VARK. Silakan coba lagi.";
            }
        } else {
            $error_message = "Gagal menghitung skor VARK. Silakan coba lagi.";
        }
    } else {
        $error_message = "Harap jawab semua pertanyaan sebelum mengirim.";
    }
}

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asesmen Gaya Belajar VARK - POINTMARKET</title>
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
        
        .question-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .question-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn:disabled {
            opacity: 0.6;
        }
        .vark-scores .badge {
            font-size: 0.8em;
            margin-right: 5px;
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
                        <i class="fas fa-brain me-2 text-primary"></i>
                        Asesmen Gaya Belajar VARK
                    </h1>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($show_result) && $show_result): ?>
                    <!-- VARK Result Display -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-pie me-2"></i>
                                        Profil Gaya Belajar VARK Anda
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Skor Gaya Belajar:</h6>
                                            <div class="mb-3">
                                                <?php 
                                                $maxScore = max($result_data['scores']);
                                                foreach ($result_data['scores'] as $style => $score): 
                                                    $percentage = ($score / 16) * 100;
                                                    $isHighest = ($score == $maxScore);
                                                ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="<?php echo $isHighest ? 'fw-bold text-primary' : ''; ?>">
                                                            <?php echo $style; ?>:
                                                        </span>
                                                        <span class="<?php echo $isHighest ? 'fw-bold text-primary' : ''; ?>">
                                                            <?php echo $score; ?>/16 (<?php echo round($percentage); ?>%)
                                                        </span>
                                                    </div>
                                                    <div class="progress mb-2" style="height: 8px;">
                                                        <div class="progress-bar <?php echo $isHighest ? 'bg-primary' : 'bg-secondary'; ?>" 
                                                             style="width: <?php echo $percentage; ?>%"></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success">Preferensi Belajar Anda:</h6>
                                            <div class="p-3 bg-light rounded">
                                                <h5 class="text-success mb-2">
                                                    <i class="<?php echo getVARKLearningTips($result_data['dominant_style'])['icon']; ?> me-2"></i>
                                                    <?php echo $result_data['learning_preference']; ?>
                                                </h5>
                                                <p class="mb-0">
                                                    <?php echo getVARKLearningTips($result_data['dominant_style'])['description']; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <a href="questionnaire.php" class="btn btn-primary">
                                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Kuesioner
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Introduction -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Tentang Asesmen Gaya Belajar VARK
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>VARK adalah singkatan dari gaya belajar <strong>V</strong>isual, <strong>A</strong>uditory, <strong>R</strong>eading/Writing, dan <strong>K</strong>inesthetic. Asesmen ini membantu mengidentifikasi cara belajar yang Anda sukai.</p>
                                            
                                            <h6 class="mt-3">Empat Gaya Belajar:</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-eye text-info me-2"></i><strong>Visual:</strong> Belajar melalui melihat dan alat bantu visual</li>
                                                <li><i class="fas fa-volume-up text-warning me-2"></i><strong>Auditory:</strong> Belajar melalui mendengar dan diskusi</li>
                                                <li><i class="fas fa-book-open text-success me-2"></i><strong>Reading/Writing:</strong> Belajar melalui teks dan catatan</li>
                                                <li><i class="fas fa-hand-rock text-danger me-2"></i><strong>Kinesthetic:</strong> Belajar melalui pengalaman langsung</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light p-3 rounded">
                                                <h6 class="text-primary">Detail Asesmen:</h6>
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-clock me-2"></i><strong>Waktu:</strong> ~10-15 menit</li>
                                                    <li><i class="fas fa-list-ol me-2"></i><strong>Pertanyaan:</strong> 16 skenario</li>
                                                    <li><i class="fas fa-tasks me-2"></i><strong>Format:</strong> Pilihan ganda</li>
                                                    <li><i class="fas fa-chart-line me-2"></i><strong>Hasil:</strong> Profil gaya belajar</li>
                                                </ul>
                                            </div>
                                            
                                            <?php if ($existingResult): ?>
                                                <div class="alert alert-info mt-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>Hasil Sebelumnya:</strong> <?php echo htmlspecialchars($existingResult['learning_preference']); ?>
                                                    <br><small>Diselesaikan: <?php echo date('d M Y', strtotime($existingResult['completed_at'])); ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- VARK Questionnaire -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clipboard-list me-2"></i>
                                        Kuesioner Gaya Belajar VARK
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="varkForm">
                                        <div id="questionsContainer">
                                            <?php foreach ($varkQuestions as $index => $question): ?>
                                                <div class="question-card mb-4 p-3 border rounded">
                                                    <h6 class="text-primary mb-3">
                                                        Pertanyaan <?php echo $question['question_number']; ?> dari 16
                                                    </h6>
                                                    <p class="fw-bold mb-3">
                                                        <?php echo htmlspecialchars($question['question_text']); ?>
                                                    </p>
                                                    
                                                    <div class="options">
                                                        <?php foreach ($question['options'] as $option): ?>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" 
                                                                       type="radio" 
                                                                       name="question_<?php echo $question['question_number']; ?>" 
                                                                       id="q<?php echo $question['question_number']; ?>_<?php echo $option['letter']; ?>"
                                                                       value="<?php echo $option['letter']; ?>"
                                                                       required>
                                                                <label class="form-check-label" 
                                                                       for="q<?php echo $question['question_number']; ?>_<?php echo $option['letter']; ?>">
                                                                    <strong><?php echo strtoupper($option['letter']); ?>.</strong> 
                                                                    <?php echo htmlspecialchars($option['text']); ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <button type="submit" name="submit_vark" class="btn btn-primary btn-lg">
                                                <i class="fas fa-check me-2"></i>
                                                Kirim Asesmen VARK
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- VARK Assessment Proof of Concept Notice -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-primary" role="alert">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>
                                Asesmen Gaya Belajar VARK - Bukti Konsep
                            </h6>
                            <p class="mb-2">
                                <strong>Status:</strong> Sistem pembelajaran ini menggunakan algoritma VARK yang sudah teruji untuk mengidentifikasi gaya belajar Anda.
                            </p>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Gaya Belajar VARK:</strong>
                                    <ul class="small mb-0 mt-1">
                                        <li><strong>Visual:</strong> Belajar melalui grafik, diagram, peta</li>
                                        <li><strong>Auditory:</strong> Belajar melalui mendengar dan diskusi</li>
                                        <li><strong>Reading/Writing:</strong> Belajar melalui teks dan catatan</li>
                                        <li><strong>Kinesthetic:</strong> Belajar melalui praktik langsung</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <strong>Integrasi dengan AI POINTMARKET:</strong>
                                    <ul class="small mb-0 mt-1">
                                        <li>📊 Hasil akan mempengaruhi rekomendasi materi</li>
                                        <li>🎯 AI akan menyesuaikan metode pembelajaran</li>
                                        <li>📚 Konten disesuaikan dengan preferensi belajar</li>
                                        <li>🔄 Sistem akan terus belajar dari interaksi Anda</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('varkForm');
    const submitBtn = form?.querySelector('button[type="submit"]');
    
    if (form && submitBtn) {
        // Track progress
        const questions = form.querySelectorAll('input[type="radio"]');
        const totalQuestions = 16;
        
        function updateProgress() {
            const answeredQuestions = new Set();
            questions.forEach(input => {
                if (input.checked) {
                    const questionNum = input.name.replace('question_', '');
                    answeredQuestions.add(questionNum);
                }
            });
            
            const progress = (answeredQuestions.size / totalQuestions) * 100;
            
            // Update submit button state
            if (answeredQuestions.size === totalQuestions) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn-secondary');
                submitBtn.classList.add('btn-primary');
                submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Submit VARK Assessment';
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-secondary');
                submitBtn.innerHTML = `<i class="fas fa-hourglass-half me-2"></i>Answer All Questions (${answeredQuestions.size}/${totalQuestions})`;
            }
        }
        
        // Initialize
        updateProgress();
        
        // Add event listeners
        questions.forEach(input => {
            input.addEventListener('change', updateProgress);
        });
        
        // Confirm before submit
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to submit your VARK assessment? This will determine your learning style profile.')) {
                e.preventDefault();
            }
        });
    }
});
</script>

                </div> <!-- End container-fluid -->
            </main>
        </div> <!-- End content-wrapper -->
    </div> <!-- End main-wrapper -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
