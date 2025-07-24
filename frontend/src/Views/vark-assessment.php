<?php
// Data for this view will be passed from the VarkAssessmentController
$user = $user ?? ['name' => 'Guest'];
$varkQuestions = $varkQuestions ?? [];
$existingResult = $existingResult ?? null;
$success_message = $success_message ?? null;
$error_message = $error_message ?? null;
$show_result = $show_result ?? false;
$result_data = $result_data ?? null;
?>

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
                        <a href="/questionnaire" class="btn btn-primary">
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
                        About the VARK Learning Style Assessment
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p>VARK is an acronym for <strong>V</strong>isual, <strong>A</strong>uditory, <strong>R</strong>eading/Writing, and <strong>K</strong>inesthetic learning styles. This assessment helps identify your preferred learning mode.</p>
                            
                            <h6 class="mt-3">The Four Learning Styles:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-eye text-info me-2"></i><strong>Visual:</strong> Learning through graphs, diagrams, and maps</li>
                                <li><i class="fas fa-volume-up text-warning me-2"></i><strong>Auditory:</strong> Learning through listening and discussion</li>
                                <li><i class="fas fa-book-open text-success me-2"></i><strong>Reading/Writing:</strong> Learning through text and notes</li>
                                <li><i class="fas fa-hand-rock text-danger me-2"></i><strong>Kinesthetic:</strong> Learning through hands-on practice</li>
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

<!-- VARK Assessment Proof of Concept Notice -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-primary" role="alert">
            <h6 class="alert-heading">
                <i class="fas fa-info-circle me-2"></i>
                VARK Learning Style Assessment - Proof of Concept
            </h6>
            <p class="mb-2">
                <strong>Status:</strong> This learning system uses the validated VARK algorithm to identify your learning style.
            </p>
            <div class="row">
                <div class="col-md-6">
                    <strong>VARK Learning Styles:</strong>
                    <ul class="small mb-0 mt-1">
                        <li><strong>Visual:</strong> Learning through graphs, diagrams, maps</li>
                        <li><strong>Auditory:</strong> Learning through listening and discussion</li>
                        <li><strong>Reading/Writing:</strong> Learning through text and notes</li>
                        <li><strong>Kinesthetic:</strong> Learning through hands-on practice</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <strong>Integration with POINTMARKET AI:</strong>
                    <ul class="small mb-0 mt-1">
                        <li>📊 Results will influence material recommendations</li>
                        <li>🎯 The AI will adapt learning methods</li>
                        <li>📚 Content will be tailored to your learning preference</li>
                        <li>🔄 The system will continuously learn from your interactions</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>