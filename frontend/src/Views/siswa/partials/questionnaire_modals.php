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
                <div id="vark-questions-step"></div>
                <div id="nlp-input-step" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentQuestionnaireId = null;
    let varkAnswers = {}; // To store VARK answers temporarily

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
        fetch(API_BASE_URL + '/api/v1/questionnaires/' + questionnaireId, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + JWT_TOKEN
            },
        })
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
                    ${error}
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
        fetch(API_BASE_URL + '/api/v1/questionnaires/' + questionnaireId, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + JWT_TOKEN
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.data.questionnaire.type === 'VARK') {
                    loadVARKPracticeForm(data.data.questionnaire, data.data.questions);
                } else {
                    loadPracticeForm(data.data.questionnaire, data.data.questions);
                }
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
                    Error loading questionnaire: ${error.message}
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
                                <div class="scale-option js-scale-option" data-question-id="${question.id}" data-value="${value}"> <!-- Use question.id -->
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

    // Event delegation for dynamically loaded practice questionnaire
    document.getElementById('practiceQuestionnaireModalBody').addEventListener('click', function(event) {
        const clickedOption = event.target.closest('.js-scale-option');
        if (clickedOption) {
            const questionId = clickedOption.dataset.questionId;
            const value = clickedOption.dataset.value;
            selectPracticeAnswer(clickedOption, questionId, value);
        }
    });

    function selectPracticeAnswer(clickedOption, questionId, value) {
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
        fetch(API_BASE_URL + '/api/v1/questionnaires', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + JWT_TOKEN
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

    function loadVARKPracticeForm(questionnaire, questions) {
        document.getElementById('practiceQuestionnaireModalTitle').innerHTML = `
            <i class="fas fa-clipboard-list me-2"></i>${questionnaire.name} - Practice
        `;
        
        let html = `
            <div id="vark-questions-step">
                <form id="varkPracticeQuestionnaireForm">
                    <div style="max-height: 400px; overflow-y: auto;">
        `;

        questions.forEach((question, index) => {
            html += `
                <div class="question-card p-3 mb-3">
                    <div class="mb-2">
                        <strong>Question ${index + 1}:</strong>
                    </div>
                    <p class="mb-3">${question.question_text}</p>
                    <div class="vark-options">
                        ${question.options.map(option => `
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answers[${question.id}]" id="option_${option.id}" value="${option.option_letter}" required>
                                <label class="form-check-label" for="option_${option.id}">
                                    <strong>${option.option_letter.toUpperCase()}.</strong> ${option.option_text}
                                </label>
                            </div>
                        `).join('')}
                    </div>
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
                    <button type="button" class="btn btn-success" onclick="handleVARKStep1Submission()">
                        <i class="fas fa-arrow-right me-1"></i> Next
                    </button>
                </div>
            </div>
            <div id="nlp-input-step" style="display: none;">
                <!-- NLP input form will be loaded here -->
            </div>
        `;
        
        document.getElementById('practiceQuestionnaireModalBody').innerHTML = html;
    }

    function handleVARKStep1Submission() {
        const form = document.getElementById('varkPracticeQuestionnaireForm');
        const formData = new FormData(form);
        
        varkAnswers = {}; // Clear previous answers
        let questionCount = 0;

        for (let pair of formData.entries()) {
            const questionId = pair[0].match(/answers\[(\d+)\]/)[1];
            varkAnswers[questionId] = pair[1];
        }

        questionCount = form.querySelectorAll('.question-card').length;

        if (Object.keys(varkAnswers).length < questionCount) {
            alert('Silakan jawab semua pertanyaan sebelum melanjutkan.');
            return;
        }
        
        // Transition to NLP input step
        showNLPTextInputStep();
    }

    function showNLPTextInputStep() {
        document.getElementById('vark-questions-step').style.display = 'none';
        document.getElementById('nlp-input-step').style.display = 'block';
        document.getElementById('practiceQuestionnaireModalTitle').innerHTML = `
            <i class="fas fa-robot me-2"></i>NLP Analysis - Step 2
        `;

        let html = `
            <div class="alert alert-info">
                <h6><i class="fas fa-lightbulb me-2"></i>Enhance Your Profile</h6>
                <p class="mb-0">Please write a short essay (min. 100 words) about your learning process, challenges, or how you approach studying. This will help us further personalize your learning recommendations.</p>
            </div>
            <form id="nlpAnalysisForm">
                <div class="mb-3">
                    <label for="nlpTextInput" class="form-label">Your Text:</label>
                    <textarea class="form-control" id="nlpTextInput" rows="10" required minlength="100"></textarea>
                </div>
            </form>
            <div class="mt-4 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="goBackToVARKStep()">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-success" onclick="submitFusedVARKAndNLP()">
                    <i class="fas fa-check me-1"></i> Submit Analysis
                </button>
            </div>
        `;
        document.getElementById('nlp-input-step').innerHTML = html;
    }

    function goBackToVARKStep() {
        document.getElementById('nlp-input-step').style.display = 'none';
        document.getElementById('vark-questions-step').style.display = 'block';
        document.getElementById('practiceQuestionnaireModalTitle').innerHTML = `
            <i class="fas fa-clipboard-list me-2"></i>VARK Assessment - Practice
        `;
    }

    function submitFusedVARKAndNLP() {
        const nlpTextInput = document.getElementById('nlpTextInput');
        const nlpText = nlpTextInput.value;

        if (nlpText.length < 100) {
            alert('Please write at least 100 words for the NLP analysis.');
            return;
        }

        // Show loading
        document.getElementById('practiceQuestionnaireModalBody').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                <p>Submitting your VARK and NLP analysis...</p>
            </div>
        `;

        // Combine VARK answers and NLP text
        const payload = {
            questionnaire_id: currentQuestionnaireId, // This is the VARK questionnaire ID
            answers: varkAnswers,
            text: nlpText
        };
        
        // Submit data to backend API
        fetch(API_BASE_URL + '/api/v1/questionnaires/vark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + JWT_TOKEN
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const learningStyle = data.data.learning_style;
                let resultHtml = `
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <h5>Assessment Completed Successfully!</h5>
                        <p>Your dominant style (fused): <strong>${learningStyle.label}</strong></p>
                    </div>
                `;

                // Display VARK scores
                resultHtml += `
                    <div class="card mb-3">
                        <div class="card-header">VARK Scores</div>
                        <div class="card-body">
                            <p>Visual: ${learningStyle.scores.visual}</p>
                            <p>Auditory: ${learningStyle.scores.auditory}</p>
                            <p>Reading/Writing: ${learningStyle.scores.reading}</p>
                            <p>Kinesthetic: ${learningStyle.scores.kinesthetic}</p>
                        </div>
                    </div>
                `;

                const textStats = data.data.text_stats;
                const qualityMetrics = Object.keys(textStats);

                // Display NLP analysis if available
                resultHtml += `
                    <div class="card mb-3">
                        <div class="card-header">NLP Analysis</div>
                        <div class="card-body">
                            <h6>Learning Preference: ${learningStyle.label} (${learningStyle.type})</h6>
                                ${qualityMetrics.map(metric => {
                                    const value = textStats[metric] ?? 0;
                                    const label = metric.replace(/_/g, ' ');
                                    return `<p>${label}: ${value}</p>`;
                                 }).join('')}
                            <h6>Keywords:</h6>
                            <ul>
                                ${data.data.keywords.map(kw => `<li>${kw}</li>`).join('')}
                            </ul>
                            <h6>Key Sentences:</h6>
                            <ul>
                                ${data.data.key_sentences.map(ks => `<li>${ks}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                `;

                resultHtml += `
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh Page
                        </button>
                    </div>
                `;

                document.getElementById('practiceQuestionnaireModalBody').innerHTML = resultHtml;
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

    // Original submitPracticeQuestionnaire for non-VARK questionnaires
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
        fetch(API_BASE_URL + '/api/v1/questionnaires', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + JWT_TOKEN
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
