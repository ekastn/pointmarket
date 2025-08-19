<?php
/**
 * @var array $questionnaires
 */
?>
<!-- Available Questionnaires -->
<div class="row mb-4">
    <div class="col-12">
        <h4><i class="fas fa-list me-2"></i>Available Questionnaires</h4>
        <p class="text-muted">Practice questionnaires untuk memahami format dan konten. Untuk evaluasi mingguan resmi, gunakan <a href="/weekly-evaluations">Weekly Evaluations</a>.</p>
    </div>
    <?php if (!empty($questionnaires)):
        foreach ($questionnaires as $questionnaire):
    ?>
        <div class="col-md-6 mb-3">
            <div class="card questionnaire-card <?php echo htmlspecialchars($questionnaire['type']); ?> h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-<?php echo $questionnaire['type'] === 'MSLQ' ? 'brain' : ($questionnaire['type'] === 'AMS' ? 'heart' : 'graduation-cap'); ?> me-2"></i>
                        <?php echo htmlspecialchars($questionnaire['name']); ?>
                    </h5>
                    <p class="card-text"><?php echo htmlspecialchars($questionnaire['description']); ?></p>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-question-circle me-1"></i>
                            <?php echo htmlspecialchars($questionnaire['total_questions']); ?> Questions
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Estimated time: <?php echo htmlspecialchars(ceil($questionnaire['total_questions'] * 0.5)); ?> minutes
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="startQuestionnaire(<?php echo htmlspecialchars($questionnaire['id']); ?>)">
                            <i class="fas fa-play me-1"></i> Practice
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="viewQuestionnaireInfo(<?php echo htmlspecialchars($questionnaire['id']); ?>)">
                            <i class="fas fa-info me-1"></i> Info
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="text-center py-4">
                <i class="fas fa-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No questionnaires available at this time.</h5>
                <p class="text-muted">Please check back later or contact support.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
