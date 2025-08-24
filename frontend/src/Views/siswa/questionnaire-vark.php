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
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-clipboard-list me-2"></i><?= htmlspecialchars($questionnaire['name'] ?? 'VARK Questionnaire') ?></h1>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Questionnaire Information</h5>
    </div>
    <div class="card-body">
        <p><?= htmlspecialchars($questionnaire['description'] ?? 'No description available.'); ?></p>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="text-center p-3 bg-light rounded">
                    <h5 class="text-primary"><?= htmlspecialchars($questionnaire['total_questions'] ?? 0); ?></h5>
                    <small class="text-muted">Total Questions</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-center p-3 bg-light rounded">
                    <h5 class="text-success"><?= htmlspecialchars(ceil(($questionnaire['total_questions'] ?? 0) * 0.5)); ?></h5>
                    <small class="text-muted">Est. Minutes</small>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Instructions</h5>
            </div>
            <div class="card-body">
                <p>Choose the answer which best explains your preference and circle the letter next to it. Please circle one answer for each question. If a single answer does not match your perception, please leave it blank and move to the next question.</p>
            </div>
        </div>
    </div>
</div>

<form id="varkQuestionnaireForm">
    <input type="hidden" name="questionnaire_id" value="<?= htmlspecialchars($questionnaire['id'] ?? ''); ?>">
    
    <div id="vark-questions-step">
        <div class="question-list mb-4">
            <?php foreach ($questions as $index => $question) { ?>
                <div class="card question-card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Question <?= $index + 1; ?>:</h6>
                        <p class="card-text"><?= htmlspecialchars($question['question_text']); ?></p>
                        <div class="vark-options">
                            <?php foreach ($question['options'] as $option) { ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answers[<?= htmlspecialchars($question['id']); ?>]" id="q<?= htmlspecialchars($question['id']); ?>_opt<?= htmlspecialchars($option['option_letter']); ?>" value="<?= htmlspecialchars($option['option_letter']); ?>" required>
                                    <label class="form-check-label" for="q<?= htmlspecialchars($question['id']); ?>_opt<?= htmlspecialchars($option['option_letter']); ?>">
                                        <strong><?= htmlspecialchars(strtoupper($option['option_letter'])); ?>.</strong> <?= htmlspecialchars($option['option_text']); ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="d-flex justify-content-between">
            <a href="/questionnaires" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <button type="button" class="btn btn-primary" id="nextToNlpStep">Next <i class="fas fa-arrow-right me-1"></i ></button>
        </div>
    </div>

    <div id="nlp-input-step" style="display: none;">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">NLP Analysis: Enhance Your Profile</h5>
            </div>
            <div class="card-body">
                <p class="alert alert-info"><i class="fas fa-lightbulb me-2"></i>Please write a short essay (min. 100 words) about your learning process, challenges, or how you approach studying. This will help us further personalize your learning recommendations.</p>
                <div class="mb-3">
                    <label for="nlpTextInput" class="form-label">Your Text:</label>
                    <textarea class="form-control" id="nlpTextInput" rows="10" required minlength="100"></textarea>
                    <small class="form-text text-muted" id="wordCountDisplay">Word count: 0</small>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" id="backToVarkStep"><i class="fas fa-arrow-left me-1"></i> Back>
            <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Submit</button>
        </div>
    </div>
</form>

<?php $renderer->includePartial('components/partials/vark_result'); ?>

<script>
    let varkAnswers = {}; // To store VARK answers temporarily

    document.getElementById('nextToNlpStep').addEventListener('click', function() {
        const form = document.getElementById('varkQuestionnaireForm');
        
        varkAnswers = {}; // Clear previous answers
        let questionCount = 0;

        // Collect answers and count questions
        form.querySelectorAll('.question-card').forEach(card => {
            questionCount++;
            const questionId = card.querySelector('input[type="radio"]').name.match(/answers\[(\d+)\]/)[1];
            const selectedOption = card.querySelector('input[type="radio"]:checked');
            if (selectedOption) {
                varkAnswers[questionId] = selectedOption.value;
            }
        });

        if (Object.keys(varkAnswers).length < questionCount) {
            alert('Please answer all VARK questions before proceeding to NLP analysis.');
            return;
        }
        
        // Transition to NLP input step
        document.getElementById('vark-questions-step').style.display = 'none';
        document.getElementById('nlp-input-step').style.display = 'block';
    });

    document.getElementById('backToVarkStep').addEventListener('click', function() {
        document.getElementById('nlp-input-step').style.display = 'none';
        document.getElementById('vark-questions-step').style.display = 'block';
    });

    document.getElementById('nlpTextInput').addEventListener('input', function() {
        const text = this.value;
        const wordCount = text.trim().split(/\s+/).filter(word => word.length > 0).length;
        document.getElementById('wordCountDisplay').textContent = `Word count: ${wordCount}`;
    });

    document.getElementById('varkQuestionnaireForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        const nlpTextInput = document.getElementById('nlpTextInput');
        const nlpText = nlpTextInput.value;

        if (nlpText.trim().split(/\s+/).filter(word => word.length > 0).length < 100) {
            alert('Please write at least 100 words for the NLP analysis.');
            return;
        }

        // Show loading indicator
        const form = event.target;
        const formContainer = form.parentNode; // Get the parent of the form to replace its content
        formContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                <h3>Processing answers...</h3>
                <p>Please do not close this page.</p>
            </div>
        `;

        // Prepare payload for API
        const payload = {
            questionnaire_id: form.querySelector('input[name="questionnaire_id"]').value,
            answers: varkAnswers,
            text: nlpText
        };

        // Submit data to backend API
        fetch('/questionnaires/vark', {
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
                resultHtml += `
                    <div id="vark-results-view" class="col">
                        ${renderVarkResults(data.data)}
                    </div>
                `;
            } else {
                resultHtml = `
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                        <h5>Error: ${data.message || 'Failed to submit VARK/NLP analysis.'}</h5>
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
                            <h5>Error: ${error.message || 'Failed to submit'}</h5>
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
</script>
