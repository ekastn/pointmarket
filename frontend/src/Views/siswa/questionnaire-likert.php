<?php
/**
 * @var array $user
 * @var array $questionnaire
 * @var array $questions
 * @var array $messages
 */
$user = $user ?? ['name' => 'Guest'];
$questionnaire = $questionnaire ?? [];
$questions = $questions ?? [];
$messages = $messages ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-clipboard-list me-2"></i><?= htmlspecialchars($questionnaire['name'] ?? 'Likert Questionnaire') ?></h1>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <?= htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
    .scale-option {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 50px; /* Adjust as needed */
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        font-weight: bold;
        color: #495057;
        background-color: #f8f9fa;
    }

    .scale-option:hover {
        background-color: #e2e6ea;
        border-color: #007bff;
    }

    .scale-option.selected {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
    }

    /* Hide the actual radio button */
    .scale-option input[type="radio"] {
        display: none;
    }
</style>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Questionnaire Information</h5>
    </div>
    <div class="card-body">
        <p><?= htmlspecialchars($questionnaire['description'] ?? 'No description available.'); ?></p>
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="text-center p-3 bg-light rounded">
                    <h5 class="text-primary"><?= htmlspecialchars($questionnaire['total_questions'] ?? 0); ?></h5>
                    <small class="text-muted">Total Questions</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-3 bg-light rounded">
                    <h5 class="text-success"><?= htmlspecialchars(ceil(($questionnaire['total_questions'] ?? 0) * 0.5)); ?></h5>
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Instructions</h5>
            </div>
            <div class="card-body">
                <p>Please rate each statement on a scale of 1-7 based on how true it is of you.</p>
                <div class="row text-center mb-3">
                    <div class="col">1 = Not at all true of me</div>
                    <div class="col">4 = Somewhat true of me</div>
                    <div class="col">7 = Very true of me</div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="likertQuestionnaireForm">
    <input type="hidden" name="questionnaire_id" value="<?= htmlspecialchars($questionnaire['id'] ?? ''); ?>">
    <div class="question-list mb-4">
        <?php foreach ($questions as $index => $question): ?>
            <div class="card question-card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Question <?= $index + 1; ?>: <small class="text-muted">(<?= htmlspecialchars($question['subscale'] ?? 'General'); ?>)</small></h6>
                    <p class="card-text"><?= htmlspecialchars($question['question_text']); ?></p>
                    <div class="row text-center likert-scale-options">
                        <?php for ($i = 1; $i <= 7; $i++): ?>
                            <div class="col">
                                <div class="scale-option" data-question-id="<?= htmlspecialchars($question['id']); ?>" data-value="<?= $i; ?>">
                                    <input type="radio" name="answers[<?= htmlspecialchars($question['id']); ?>]" id="q<?= htmlspecialchars($question['id']); ?>_<?= $i; ?>" value="<?= $i; ?>" required style="display: none;">
                                    <span><?= $i; ?></span>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between">
        <a href="/questionnaires" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
        <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Submit Answers</button>
    </div>
</form>

<script>
document.getElementById('likertQuestionnaireForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    const form = event.target;
    const formData = new FormData(form);
    const answers = {};
    let allAnswered = true;

    // Collect answers and check if all are answered
    form.querySelectorAll('input[type="radio"][name^="answers["]').forEach(input => {
        const questionId = input.name.match(/answers\[(\d+)\]/)[1];
        if (!answers[questionId] && input.checked) {
            answers[questionId] = input.value;
        }
    });

    // Verify all questions have an answer
    form.querySelectorAll('.question-card').forEach(card => {
        const questionId = card.querySelector('input[type="radio"][name^="answers["]').name.match(/answers\[(\d+)\]/)[1];
        if (!answers[questionId]) {
            allAnswered = false;
            card.style.border = '1px solid red'; // Highlight unanswered card
        } else {
            card.style.border = ''; // Reset border
        }
    });

    if (!allAnswered) {
        alert('Please answer all questions before submitting.');
        return;
    }

    // Prepare payload for API
    const payload = {
        questionnaire_id: formData.get('questionnaire_id'),
        answers: answers
    };

    // Show loading indicator
    const formContainer = form.parentNode; // Get the parent of the form to replace its content
    formContainer.innerHTML = `
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
            <h3>Submitting your answers...</h3>
            <p>Please do not close this page.</p>
        </div>
    `;

    // Submit data to backend API
    fetch('/questionnaires/likert', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + JWT_TOKEN // Assuming JWT_TOKEN is globally available
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        let resultHtml = '';
        if (data.success) {
            resultHtml = `
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                    <h5>${data.message || 'Questionnaire submitted successfully!'}</h5>
                    <p>Your score: <strong>${data.data.total_score.toFixed(2)}</strong></p>
                </div>
            `;
        } else {
            resultHtml = `
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                    <h5>Error: ${data.message || 'Failed to submit questionnaire.'}</h5>
                </div>
            `;
        }

        formContainer.innerHTML = `
            <div class="card mb-4">
                <div class="card-body">
                    ${resultHtml}
                    <div class="text-center mt-4">
                        <a href="/questionnaires" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Questionnaires
                        </a>
                    </div>
                </div>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error:', error);
        formContainer.innerHTML = `
            <div class="card mb-4">
                <div class="card-body">
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                        <h5>Network error. Please try again.</h5>
                    </div>
                    <div class="text-center mt-4">
                        <a href="/questionnaires" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Questionnaires
                        </a>
                    </div>
                </div>
            </div>
        `;
    });
});

    // Event delegation for dynamically loaded practice questionnaire
    document.querySelector('.question-list').addEventListener('click', function(event) {
        const clickedOption = event.target.closest('.scale-option');
        if (clickedOption) {
            const questionId = clickedOption.dataset.questionId;
            const value = clickedOption.dataset.value;
            selectLikertAnswer(clickedOption, questionId, value);
        }
    });

    function selectLikertAnswer(clickedOption, questionId, value) {
        // Find the parent question-card
        const questionCard = clickedOption.closest('.question-card');
        if (questionCard) {
            // Remove 'selected' from all scale-options within this question-card
            questionCard.querySelectorAll('.scale-option').forEach(option => {
                option.classList.remove('selected');
            });
        }
        
        // Add selection to clicked option
        clickedOption.classList.add('selected');
        
        // Set hidden input value
        document.getElementById(`q${questionId}_${value}`).checked = true;
    }
</script>