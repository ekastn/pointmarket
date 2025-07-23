<?php
include 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

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
                $success_message = "VARK Assessment completed successfully! Your learning style: " . $varkResult['dominant_style'];
                $show_result = true;
                $result_data = $varkResult;
            } else {
                $error_message = "Error saving VARK result. Please try again.";
            }
        } else {
            $error_message = "Error calculating VARK score. Please try again.";
        }
    } else {
        $error_message = "Please answer all questions before submitting.";
    }
}

// Get VARK questions
$varkQuestions = getVARKQuestions($pdo);

// Check if student has completed VARK before
$existingResult = getStudentVARKResult($user['id'], $pdo);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VARK Learning Style Assessment - POINTMARKET</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
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
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="container-fluid p-0">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-brain me-2 text-primary"></i>
                        VARK Learning Style Assessment
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
                                        Your VARK Learning Style Profile
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Learning Style Scores:</h6>
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
                                            <h6 class="text-success">Your Learning Preference:</h6>
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
                                            <i class="fas fa-arrow-left me-1"></i> Back to Questionnaires
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
                                        About VARK Learning Style Assessment
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>VARK stands for <strong>V</strong>isual, <strong>A</strong>uditory, <strong>R</strong>eading/Writing, and <strong>K</strong>inesthetic learning styles. This assessment helps identify your preferred learning mode.</p>
                                            
                                            <h6 class="mt-3">The Four Learning Styles:</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-eye text-info me-2"></i><strong>Visual:</strong> Learn through seeing and visual aids</li>
                                                <li><i class="fas fa-volume-up text-warning me-2"></i><strong>Auditory:</strong> Learn through hearing and discussion</li>
                                                <li><i class="fas fa-book-open text-success me-2"></i><strong>Reading/Writing:</strong> Learn through text and notes</li>
                                                <li><i class="fas fa-hand-rock text-danger me-2"></i><strong>Kinesthetic:</strong> Learn through hands-on experience</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light p-3 rounded">
                                                <h6 class="text-primary">Assessment Details:</h6>
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-clock me-2"></i><strong>Time:</strong> ~10-15 minutes</li>
                                                    <li><i class="fas fa-list-ol me-2"></i><strong>Questions:</strong> 16 scenarios</li>
                                                    <li><i class="fas fa-tasks me-2"></i><strong>Format:</strong> Multiple choice</li>
                                                    <li><i class="fas fa-chart-line me-2"></i><strong>Result:</strong> Learning style profile</li>
                                                </ul>
                                            </div>
                                            
                                            <?php if ($existingResult): ?>
                                                <div class="alert alert-info mt-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>Previous Result:</strong> <?php echo htmlspecialchars($existingResult['learning_preference']); ?>
                                                    <br><small>Completed: <?php echo date('d M Y', strtotime($existingResult['completed_at'])); ?></small>
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
                                        VARK Learning Style Questionnaire
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="varkForm">
                                        <div id="questionsContainer">
                                            <?php foreach ($varkQuestions as $index => $question): ?>
                                                <div class="question-card mb-4 p-3 border rounded">
                                                    <h6 class="text-primary mb-3">
                                                        Question <?php echo $question['question_number']; ?> of 16
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
                                                Submit VARK Assessment
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
