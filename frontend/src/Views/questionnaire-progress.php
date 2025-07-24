<?php
// Data for this view will be passed from the QuestionnaireProgressController
$user = $user ?? ['name' => 'Guest'];
$questionnaires = $questionnaires ?? [];
$history = $history ?? [];
$stats = $stats ?? [];
$messages = $messages ?? [];
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-chart-line me-2"></i>Questionnaire Progress</h2>
        <p class="text-muted">Track your progress on the MSLQ and AMS questionnaires. Fill them out at any time to track your motivation and learning strategies.</p>
        
        <!-- AI Implementation Notice -->
        <div class="alert alert-primary mb-3">
            <h6><i class="fas fa-robot me-2"></i>AI-Powered Learning Personalization</h6>
            <p class="mb-2">These questionnaires help the POINTMARKET AI understand your learning profile:</p>
            <div class="row">
                <div class="col-md-4">
                    <strong>🧠 NLP Integration:</strong> Questionnaire results determine the AI's feedback style for assignments.
                </div>
                <div class="col-md-4">
                    <strong>🎯 RL Optimization:</strong> Learning recommendations are based on your motivation and strategies.
                </div>
                <div class="col-md-4">
                    <strong>📚 CBF Matching:</strong> Learning materials are personalized to your motivation profile.
                </div>
            </div>
            <hr>
            <p class="mb-2"><strong>💡 Flexibility:</strong> Fill out the questionnaires at any time to measure changes in your motivation and learning strategies.</p>
            <div class="text-center">
                <a href="/vark-correlation-analysis" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-chart-network me-1"></i>View VARK-MSLQ-AMS Correlation Analysis
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-12">
        <h4><i class="fas fa-chart-bar me-2"></i>Your Questionnaire Statistics</h4>
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
                        <p class="mb-1"><strong>Completed:</strong></p>
                        <h4 class="text-primary"><?php echo $stat['total_completed'] ?? 0; ?>x</h4>
                    </div>
                    <div class="col-6">
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
                    </div>
                </div>
                <?php if ($stat['last_completed']): ?>
                    <small class="text-muted">
                        Last completed: <?php echo date('d M Y', strtotime($stat['last_completed'])); ?>
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
                <h5 class="mb-0"><i class="fas fa-chart-network me-2"></i>Learning Style Correlation Analysis</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6>📊 VARK Correlation with MSLQ & AMS</h6>
                        <p class="mb-2">See how your VARK learning style correlates with your motivation (AMS) and learning strategies (MSLQ). This analysis helps you understand your personal learning patterns.</p>
                        <ul class="small mb-0">
                            <li><strong>Visual:</strong> High correlation with Organization & Elaboration strategies</li>
                            <li><strong>Auditory:</strong> Strong correlation with Help Seeking & Social Learning</li>
                            <li><strong>Reading/Writing:</strong> Highest correlation with Metacognitive strategies</li>
                            <li><strong>Kinesthetic:</strong> High correlation with Effort Regulation & practical application</li>
                        </ul>
                    </div>
                    <div class="col-md-4 text-center">
                        <a href="/vark-correlation-analysis" class="btn btn-info">
                            <i class="fas fa-analytics me-2"></i>View Correlation Analysis
                        </a>
                        <br><small class="text-muted mt-2 d-block">Requires VARK, MSLQ, and AMS data</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Available Questionnaires -->
<div class="row mb-4">
    <div class="col-12">
        <h4><i class="fas fa-list me-2"></i>Available Questionnaires</h4>
        <p class="text-muted">Fill out a questionnaire at any time to help the AI understand your motivation and learning strategies.</p>
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
                        Estimated: <?php echo ceil($questionnaire['total_questions'] * 0.5); ?> minutes
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="startQuestionnaire(<?php echo $questionnaire['id']; ?>)">
                        <i class="fas fa-play me-1"></i> Start
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
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Questionnaire History</h5>
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
                                        <?php echo date('d M Y', strtotime($item['completed_at'])); ?>
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
                        <p class="text-muted">Start your first questionnaire to see your progress here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>