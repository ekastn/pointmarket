<?php
// Data for this view will be passed from the QuestionnaireProgressController
$user = $user ?? ['name' => 'Guest'];
$questionnaires = $questionnaires ?? [];
$history = $history ?? [];
$stats = $stats ?? [];
$messages = $messages ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Questionnaire Progress</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/weekly-evaluations" class="btn btn-outline-primary">
                <i class="fas fa-calendar-check"></i> Weekly Evaluations
            </a>
            <a href="/ai-explanation" class="btn btn-outline-info">
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

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-12">
        <h4><i class="fas fa-chart-bar me-2"></i>Your Questionnaire Statistics</h4>
    </div>
    <?php if (!empty($stats)): ?>
        <?php foreach ($stats as $stat): ?>
        <div class="col-md-6 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-<?php echo $stat['type'] === 'mslq' ? 'brain' : 'heart'; ?> me-2"></i>
                        <?php echo htmlspecialchars(strtoupper($stat['name'])); ?>
                    </h5>
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1"><strong>Completed:</strong></p>
                            <h4 class="text-primary"><?php echo htmlspecialchars($stat['total_completed'] ?? 0); ?>x</h4>
                        </div>
                        <div class="col-6">
                            <p class="mb-1"><strong>Average Score:</strong></p>
                            <?php if ($stat['average_score'] !== null): ?>
                                <?php 
                                $avg_score = $stat['average_score'];
                                $scoreClass = $avg_score >= 5.5 ? 'score-high' : ($avg_score >= 4 ? 'score-medium' : 'score-low');
                                ?>
                                <h4 class="<?php echo $scoreClass; ?>"><?php echo htmlspecialchars(number_format($avg_score, 2)); ?></h4>
                            <?php else: ?>
                                <h4 class="text-muted">-</h4>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($stat['last_completed']): ?>
                        <small class="text-muted">
                            Last completed: <?php echo htmlspecialchars(date('d M Y', strtotime($stat['last_completed']))); ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="text-center py-4">
                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No questionnaire statistics available yet.</h5>
                <p class="text-muted">Complete a questionnaire to see your progress here.</p>
            </div>
        </div>
    <?php endif; ?>
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
    <?php if (!empty($questionnaires)): ?>
        <?php foreach ($questionnaires as $questionnaire): ?>
        <div class="col-md-6 mb-3">
            <div class="card questionnaire-card <?php echo htmlspecialchars($questionnaire['type']); ?> h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-<?php echo $questionnaire['type'] === 'mslq' ? 'brain' : 'heart'; ?> me-2"></i>
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
                            Estimated: <?php echo htmlspecialchars(ceil($questionnaire['total_questions'] * 0.5)); ?> minutes
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="startQuestionnaire(<?php echo htmlspecialchars($questionnaire['id']); ?>)">
                            <i class="fas fa-play me-1"></i> Start
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
                                        <?php echo htmlspecialchars(date('d M Y', strtotime($item['completed_at']))); ?>
                                        <?php if (isset($item['week_number']) && isset($item['year'])): ?>
                                            | Week <?php echo htmlspecialchars($item['week_number']); ?>/<?php echo htmlspecialchars($item['year']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <?php 
                                    $score = $item['total_score'];
                                    $scoreClass = $score >= 5.5 ? 'score-high' : ($score >= 4 ? 'score-medium' : 'score-low');
                                    ?>
                                    <span class="score-badge <?php echo $scoreClass; ?>">
                                        Score: <?php echo htmlspecialchars(number_format($score, 2)); ?>
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
                        <a href="/weekly-evaluations" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-1"></i> Start Weekly Evaluations
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
        fetch('/api/v1/questionnaires/' + questionnaireId) // Use API client for info
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadQuestionnaireInfo(data.data.questionnaire, data.data.questions, data.data.recent_result); // Adjusted data access
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
            const subscaleName = q.subscale || 'General'; // Handle null subscale
            if (!subscales[subscaleName]) {
                subscales[subscaleName] = [];
            }
            subscales[subscaleName].push(q);
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
                <button type="button" class="btn btn-primary" onclick="startQuestionnaire(${questionnaire.id})">
                    <i class="fas fa-play me-1"></i> Start Practice
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        `;
        
        document.getElementById('questionnaireInfoModalBody').innerHTML = html;
    }

    function startQuestionnaire(questionnaireId) {
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
        fetch('/api/v1/questionnaires/' + questionnaireId) // Use API client for questions
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadPracticeForm(data.data.questionnaire, data.data.questions); // Adjusted data access
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
                <a href="/weekly-evaluations" class="alert-link">Weekly Evaluations</a> page.</p>
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
                                <div class="scale-option" onclick="selectPracticeAnswer(${question.id}, ${value})"> <!-- Use question.id -->
                                    <strong>${value}</strong>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <input type="hidden" name="answers[${question.id}]" id="practice_answer_${question.id}"> <!-- Use question.id -->
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
                <button type="button" class="btn btn-success" onclick="submitPracticeQuestionnaire()">
                    <i class="fas fa-check me-1"></i> Kirim Jawaban
                </button>
            </div>
        `;
        
        document.getElementById('practiceQuestionnaireModalBody').innerHTML = html;
    }

    function selectPracticeAnswer(questionId, value) {
        // Remove previous selection
        document.querySelectorAll(`[onclick*="selectPracticeAnswer(${questionId}"]`).forEach(option => {
            option.classList.remove('selected');
        });
        
        // Add selection to clicked option
        event.target.classList.add('selected');
        
        // Set hidden input value
        document.getElementById(`practice_answer_${questionId}`).value = value;
    }

    function submitPracticeQuestionnaire() {
        const form = document.getElementById('practiceQuestionnaireForm');
        const formData = new FormData(form);
        
        const answers = {};
        let allAnswered = true;
        
        // Collect answers and check if all are answered
        form.querySelectorAll('input[name^="answers["]').forEach(input => {
            if (!input.value) {
                allAnswered = false;
            } else {
                const questionId = input.name.match(/answers\[(\d+)\]/)[1];
                answers[questionId] = input.value;
            }
        });
        
        if (!allAnswered) {
            alert('Silakan jawab semua pertanyaan sebelum mengirim.');
            return;
        }
        
        // Show loading
        document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                <p>Submitting your practice questionnaire...</p>
            </div>
        `;
        
        // Submit data to backend API
        fetch('/api/v1/questionnaires/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + sessionStorage.getItem('jwt_token') // Assuming JWT is stored in session storage
            },
            body: JSON.stringify({
                questionnaire_id: currentQuestionnaireId,
                answers: answers,
                week_number: new Date().getWeek(), // Placeholder for week number
                year: new Date().getFullYear() // Placeholder for year
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <h5>Practice Completed Successfully!</h5>
                        <p>Your score: <strong>${data.data.total_score.toFixed(2)}</strong></p>
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
            console.error('Network error:', error);
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

    // Add getWeek() to Date prototype for convenience
    Date.prototype.getWeek = function() {
        var date = new Date(this.getTime());
        date.setHours(0, 0, 0, 0);
        // Sunday in current week decides the year. 
        date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
        // January 4 is always in week 1. 
        var week1 = new Date(date.getFullYear(), 0, 4);
        // Adjust to Sunday in week 1 and count number of weeks from date to week1.
        return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000 -
            3 + (week1.getDay() + 6) % 7) / 7);
    };

</script>


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