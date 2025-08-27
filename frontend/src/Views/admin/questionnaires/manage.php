<?php
/**
 * @var array $user
 * @var array $questionnaire
 * @var array $questions
 */
$user = $user ?? ['name' => 'Guest'];
$questionnaire = $questionnaire ?? [];
$questions = $questions ?? [];

$isEditMode = isset($questionnaire['id']);
$formTitle = $isEditMode ? 'Edit Questionnaire: ' . htmlspecialchars($questionnaire['name']) : 'Create New Questionnaire';

// Prepare data for JavaScript
$questionnaireData = [ // Use a different variable name to avoid conflict with PHP variable
    'id' => $questionnaire['id'] ?? null,
    'name' => $questionnaire['name'] ?? '',
    'description' => $questionnaire['description'] ?? '',
    'type' => $questionnaire['type'] ?? '',
    'status' => $questionnaire['status'] ?? 'active',
    'total_questions' => $questionnaire['total_questions'] ?? 0,
    'questions' => [],
];

if ($isEditMode && !empty($questions)) {
    foreach ($questions as $q) {
        $questionItem = [
            'id' => $q['id'],
            'question_number' => $q['question_number'],
            'question_text' => $q['question_text'],
            'subscale' => $q['subscale'] ?? null,
            'options' => [],
        ];
        if (isset($q['options'])) {
            foreach ($q['options'] as $o) {
                $questionItem['options'][] = [
                    'id' => $o['id'],
                    'option_text' => $o['option_text'],
                    'option_letter' => $o['option_letter'],
                    'learning_style' => $o['learning_style'],
                ];
            }
        }
        $questionnaireData['questions'][] = $questionItem;
    }
}

?>

<?php $renderer->includePartial('components/partials/page_title', [
    'icon' => 'fas fa-clipboard-list',
    'title' => htmlspecialchars($formTitle),
]); ?>

<form id="questionnaireAdminForm">
    <input type="hidden" name="id" value="<?= htmlspecialchars($questionnaireData['id'] ?? ''); ?>">

    <div class="card mb-4 pm-section">
        <div class="card-header">
            <h5 class="mb-0">Questionnaire Details</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="questionnaireName" class="form-label">Name</label>
                <input type="text" class="form-control" id="questionnaireName" name="name" value="<?= htmlspecialchars($questionnaireData['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="questionnaireDescription" class="form-label">Description</label>
                <textarea class="form-control" id="questionnaireDescription" name="description" rows="3"><?= htmlspecialchars($questionnaireData['description']); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="questionnaireType" class="form-label">Type</label>
                    <select class="form-select" id="questionnaireType" name="type" required>
                        <option value="">Select Type</option>
                        <option value="MSLQ" <?= ($questionnaireData['type'] === 'MSLQ') ? 'selected' : ''; ?>>MSLQ</option>
                        <option value="AMS" <?= ($questionnaireData['type'] === 'AMS') ? 'selected' : ''; ?>>AMS</option>
                        <option value="VARK" <?= ($questionnaireData['type'] === 'VARK') ? 'selected' : ''; ?>>VARK</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="questionnaireStatus" class="form-label">Status</label>
                    <select class="form-select" id="questionnaireStatus" name="status" required>
                        <option value="active" <?= ($questionnaireData['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?= ($questionnaireData['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 pm-section">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Questions</h5>
            <button type="button" class="btn btn-primary btn-sm" id="addQuestionBtn"><i class="fas fa-plus me-1"></i> Add Question</button>
        </div>
        <div class="card-body" id="questionsContainer">
            <!-- Questions will be dynamically added here -->
            
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="/questionnaires" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
        <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Save Questionnaire</button>
    </div>
</form>

<script>
    const questionnaireData = <?= json_encode($questionnaireData); ?>;
    const questionsContainer = document.getElementById('questionsContainer');
    const addQuestionBtn = document.getElementById('addQuestionBtn');
    const questionnaireTypeSelect = document.getElementById('questionnaireType');

    // Subscale options per questionnaire type
    const SUBSCALE_OPTIONS = {
        MSLQ: [
            'Intrinsic & Extrinsic Goal Orientation',
            'Task Value',
            'Self-Efficacy',
            'Metacognitive & Cognitive Strategy Use',
            'Resource Management'
        ],
        AMS: [
            'Intrinsic Motivation',
            'Extrinsic Motivation',
            'Achievement',
            'Amotivation'
        ]
    };

    function renderSubscaleOptions(selectedValue) {
        const type = questionnaireTypeSelect.value;
        const opts = SUBSCALE_OPTIONS[type] || [];
        let html = '<option value="">Select Subscale</option>';
        opts.forEach(o => {
            const sel = (o === selectedValue) ? 'selected' : '';
            html += `<option value="${htmlspecialchars(o)}" ${sel}>${htmlspecialchars(o)}</option>`;
        });
        return html;
    }

    let questionCounter = 0;

    function updateQuestionNumbers() {
        const questionCards = questionsContainer.querySelectorAll('.question-card');
        questionCards.forEach((card, index) => {
            card.querySelector('.question-number-display').textContent = index + 1;
        });
    }

    function updateOptionLetters(questionCard) {
        const optionItems = questionCard.querySelectorAll('.option-item');
        const letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        optionItems.forEach((item, index) => {
            item.querySelector('.option-letter-display').textContent = letters[index].toUpperCase();
            item.querySelector('.option-letter-input').value = letters[index];
        });
    }

    function addQuestion(q = null) {
        questionCounter++;
        const questionCard = document.createElement('div');
        questionCard.className = 'question-card card mb-3';
        questionCard.dataset.questionId = q ? q.id : '';

        const isVark = questionnaireTypeSelect.value === 'VARK';
        const subscaleDisplay = isVark ? 'd-none' : '';
        const optionsDisplay = isVark ? '' : 'd-none';

        questionCard.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Question <span class="question-number-display"></span></h6>
                    <button type="button" class="btn btn-danger btn-sm delete-question-btn"><i class="fas fa-trash"></i></button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Question Text</label>
                    <textarea class="form-control question-text-input" rows="2" required>${q ? htmlspecialchars(q.question_text) : ''}</textarea>
                </div>
                <div class="mb-3 subscale-group ${subscaleDisplay}">
                    <label class="form-label">Subscale</label>
                    <select class="form-select subscale-input">
                        ${renderSubscaleOptions(q && q.subscale ? q.subscale : '')}
                    </select>
                </div>
                <div class="options-group ${optionsDisplay}">
                    <h6 class="mt-3">Options <button type="button" class="btn btn-success btn-sm add-option-btn"><i class="fas fa-plus"></i></button></h6>
                    <div class="options-container">
                        <!-- Options will be dynamically added here -->
                    </div>
                </div>
            </div>
        `;

        questionsContainer.appendChild(questionCard);
        updateQuestionNumbers();

        const optionsContainer = questionCard.querySelector('.options-container');
        if (q && q.options) {
            q.options.forEach(option => addOption(optionsContainer, option));
        }
    }

    function addOption(optionsContainer, o = null) {
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item border p-2 mb-2';
        optionItem.dataset.optionId = o ? o.id : '';

        optionItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Option <span class="option-letter-display"></span></h6>
                <button type="button" class="btn btn-danger btn-sm delete-option-btn"><i class="fas fa-trash"></i></button>
            </div>
            <div class="mb-2">
                <label class="form-label">Option Text</label>
                <input type="text" class="form-control option-text-input" value="${o ? htmlspecialchars(o.option_text) : ''}" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Option Letter</label>
                    <input type="text" class="form-control option-letter-input" maxlength="1" value="${o ? htmlspecialchars(o.option_letter) : ''}" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Learning Style</label>
                    <select class="form-select learning-style-input" required>
                        <option value="">Select Style</option>
                        <option value="Visual" ${o && o.learning_style === 'Visual' ? 'selected' : ''}>Visual</option>
                        <option value="Auditory" ${o && o.learning_style === 'Auditory' ? 'selected' : ''}>Auditory</option>
                        <option value="Reading" ${o && o.learning_style === 'Reading' ? 'selected' : ''}>Reading/Writing</option>
                        <option value="Kinesthetic" ${o && o.learning_style === 'Kinesthetic' ? 'selected' : ''}>Kinesthetic</option>
                    </select>
                </div>
            </div>
        `;

        optionsContainer.appendChild(optionItem);
        updateOptionLetters(optionsContainer.closest('.question-card'));
    }

    // Initial load of questions if in edit mode
    if (questionnaireData.questions && questionnaireData.questions.length > 0) {
        questionnaireData.questions.forEach(q => addQuestion(q));
    }

    // Event delegation for dynamic elements
    questionsContainer.addEventListener('click', function(event) {
        if (event.target.closest('.delete-question-btn')) {
            event.target.closest('.question-card').remove();
            updateQuestionNumbers();
        }

        if (event.target.closest('.delete-option-btn')) {
            const questionCard = event.target.closest('.question-card');
            event.target.closest('.option-item').remove();
            updateOptionLetters(questionCard);
        }

        if (event.target.closest('.add-option-btn')) {
            const optionsContainer = event.target.closest('.options-group').querySelector('.options-container');
            addOption(optionsContainer);
        }
    });

    addQuestionBtn.addEventListener('click', function() {
        addQuestion();
    });

    // Handle questionnaire type change
    questionnaireTypeSelect.addEventListener('change', function() {
        const isVark = this.value === 'VARK';
        questionsContainer.querySelectorAll('.question-card').forEach(questionCard => {
            questionCard.querySelector('.subscale-group').classList.toggle('d-none', isVark);
            questionCard.querySelector('.options-group').classList.toggle('d-none', !isVark);
        });

        // Refresh subscale options when switching between MSLQ/AMS
        if (!isVark) {
            questionsContainer.querySelectorAll('.subscale-input').forEach(select => {
                const current = select.value || '';
                select.innerHTML = renderSubscaleOptions(current);
            });
        }
    });

    // Form submission
    document.getElementById('questionnaireAdminForm').addEventListener('submit', async function(event) {
        event.preventDefault();

        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        const payload = {
            id: questionnaireData.id || null,
            name: document.getElementById('questionnaireName').value,
            description: document.getElementById('questionnaireDescription').value,
            type: document.getElementById('questionnaireType').value,
            status: document.getElementById('questionnaireStatus').value,
            questions: []
        };

        const questionCards = questionsContainer.querySelectorAll('.question-card');
        payload.total_questions = questionCards.length;

        questionCards.forEach((qCard, qIndex) => {
            const questionId = parseInt(qCard.dataset.questionId, 10);
            const question = {
                id: questionId || null,
                question_number: qIndex + 1,
                question_text: qCard.querySelector('.question-text-input').value,
                subscale: null,
                options: []
            };

            if (questionnaireTypeSelect.value !== 'VARK') {
                question.subscale = qCard.querySelector('.subscale-input').value || null;
            } else {
                const optionItems = qCard.querySelectorAll('.option-item');
                optionItems.forEach(oItem => {
                    question.options.push({
                        id: oItem.dataset.optionId || null,
                        option_text: oItem.querySelector('.option-text-input').value,
                        option_letter: oItem.querySelector('.option-letter-input').value,
                        learning_style: oItem.querySelector('.learning-style-input').value
                    });
                });
            }
            payload.questions.push(question);
        });

        const url = payload.id 
            ? `/questionnaires/${payload.id}` 
            : '/questionnaires';
        const method = payload.id ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + JWT_TOKEN 
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                alert('Questionnaire saved successfully!');
                // Redirect to edit page if newly created, or just update URL
                if (!questionnaireData.id) {
                    window.location.href = `/questionnaires/${data.data.id}/edit`;
                } else {
                    // Optionally update questionnaireData with new IDs for newly created questions/options
                    // This is more complex and might require a full re-render or careful merging
                    // For simplicity, we'll just reload the page for now if IDs are critical for further inline edits
                    window.location.reload(); 
                }
            } else {
                alert('Error saving questionnaire: ' + data.message);
            }
        } catch (error) {
            console.error('Network error:', error);
            alert('Network error: Unable to connect to the server.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Save Questionnaire';
        }
    });

    // Helper for HTML escaping
    function htmlspecialchars(str) {
        if (typeof str !== 'string') {
            return str;
        }
        return str.replace(/&/g, '&amp;')
                   .replace(/</g, '&lt;')
                   .replace(/>/g, '&gt;')
                   .replace(/"/g, '&quot;')
                   .replace(/'/g, '&#039;');
    }
</script>
