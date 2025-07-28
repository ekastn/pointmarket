<?php
// Data for this view will be passed from the QuestionnaireController
$user = $user ?? ['name' => 'Guest'];
$questionnaires = $questionnaires ?? [];
$history = $history ?? [];
$stats = $stats ?? [];
$pendingEvaluations = $pendingEvaluations ?? [];
$varkResult = $varkResult ?? null;
$messages = $messages ?? [];

// Helper function (ideally in a separate utility file or passed from controller)
if (!function_exists('getVARKLearningTips')) {
    function getVARKLearningTips($dominantStyle) {
        $tips = [
            'Visual' => [
                'study_tips' => [
                    'Gunakan diagram, chart, dan mind maps',
                    'Highlight dengan warna-warna berbeda',
                    'Buat flashcards dengan gambar',
                    'Tonton video pembelajaran'
                ],
                'description' => 'Anda lebih mudah belajar melalui elemen visual seperti gambar, diagram, dan grafik.',
                'icon' => 'fas fa-eye'
            ],
            'Auditory' => [
                'study_tips' => [
                    'Diskusikan materi dengan teman',
                    'Rekam dan dengar kembali catatan',
                    'Gunakan musik atau rhythm untuk mengingat',
                    'Baca materi dengan suara keras'
                ],
                'description' => 'Anda lebih mudah belajar melalui mendengar dan berbicara.',
                'icon' => 'fas fa-volume-up'
            ],
            'Reading' => [
                'study_tips' => [
                    'Buat catatan lengkap saat belajar',
                    'Gunakan daftar dan bullet points',
                    'Baca buku teks dan artikel',
                    'Tulis ringkasan dengan kata-kata sendiri'
                ],
                'description' => 'Anda lebih mudah belajar melalui membaca dan menulis.',
                'icon' => 'fas fa-book-open'
            ],
            'Kinesthetic' => [
                'study_tips' => [
                    'Praktikkan langsung apa yang dipelajari',
                    'Gunakan model atau objek fisik',
                    'Bergerak sambil belajar (walking study)',
                    'Buat eksperimen dan simulasi'
                ],
                'description' => 'Anda lebih mudah belajar melalui pengalaman langsung dan praktik.',
                'icon' => 'fas fa-hand-rock'
            ],
            // Multi-modal styles (simplified descriptions)
            'Visual/Auditory' => [
                'study_tips' => ['Gunakan kombinasi visual dan audio.'],
                'description' => 'Anda belajar terbaik dengan melihat dan mendengar.',
                'icon' => 'fas fa-eye fas fa-volume-up'
            ],
            'Visual/Reading' => [
                'study_tips' => ['Kombinasikan visual dengan membaca/menulis.'],
                'description' => 'Anda belajar terbaik dengan melihat dan membaca/menulis.',
                'icon' => 'fas fa-eye fas fa-book-open'
            ],
            'Visual/Kinesthetic' => [
                'study_tips' => ['Gunakan visual dan praktik langsung.'],
                'description' => 'Anda belajar terbaik dengan melihat dan melakukan.',
                'icon' => 'fas fa-eye fas fa-hand-rock'
            ],
            'Auditory/Reading' => [
                'study_tips' => ['Kombinasikan mendengar dengan membaca/menulis.'],
                'description' => 'Anda belajar terbaik dengan mendengar dan membaca/menulis.',
                'icon' => 'fas fa-volume-up fas fa-book-open'
            ],
            'Auditory/Kinesthetic' => [
                'study_tips' => ['Kombinasikan mendengar dengan praktik langsung.'],
                'description' => 'Anda belajar terbaik dengan mendengar dan melakukan.',
                'icon' => 'fas fa-volume-up fas fa-hand-rock'
            ],
            'Reading/Kinesthetic' => [
                'study_tips' => ['Kombinasikan membaca/menulis dengan praktik langsung.'],
                'description' => 'Anda belajar terbaik dengan membaca/menulis dan melakukan.',
                'icon' => 'fas fa-book-open fas fa-hand-rock'
            ],
            'Visual/Auditory/Reading' => [
                'study_tips' => ['Gunakan visual, audio, dan membaca/menulis.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam melihat, mendengar, dan membaca/menulis.',
                'icon' => 'fas fa-eye fas fa-volume-up fas fa-book-open'
            ],
            'Visual/Auditory/Kinesthetic' => [
                'study_tips' => ['Gunakan visual, audio, dan praktik langsung.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam melihat, mendengar, dan melakukan.',
                'icon' => 'fas fa-eye fas fa-volume-up fas fa-hand-rock'
            ],
            'Visual/Reading/Kinesthetic' => [
                'study_tips' => ['Gunakan visual, membaca/menulis, dan praktik langsung.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam melihat, membaca/menulis, dan melakukan.',
                'icon' => 'fas fa-eye fas fa-book-open fas fa-hand-rock'
            ],
            'Auditory/Reading/Kinesthetic' => [
                'study_tips' => ['Gunakan audio, membaca/menulis, dan praktik langsung.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam mendengar, membaca/menulis, dan melakukan.',
                'icon' => 'fas fa-volume-up fas fa-book-open fas fa-hand-rock'
            ],
            'Visual/Auditory/Reading/Kinesthetic' => [
                'study_tips' => ['Gunakan semua modalitas pembelajaran.'],
                'description' => 'Anda adalah pembelajar multimodal yang efektif dengan semua gaya belajar.',
                'icon' => 'fas fa-brain'
            ],
        ];
        return $tips[$dominantStyle] ?? ['study_tips' => ['Tidak ada tips spesifik.'], 'description' => 'Gaya belajar tidak teridentifikasi.', 'icon' => 'fas fa-question-circle'];
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-clipboard-list me-2"></i>Questionnaires</h1>
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

<!-- Pending Weekly Evaluations Alert -->
<?php if (!empty($pendingEvaluations)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert pending-alert">
            <h5><i class="fas fa-bell me-2"></i>Weekly Evaluations Pending</h5>
            <p>You have <strong><?php echo count($pendingEvaluations); ?> weekly evaluation(s)</strong> that need to be completed.</p>
            <a href="/weekly-evaluations" class="btn btn-warning btn-sm">
                <i class="fas fa-calendar-check me-1"></i> Complete Weekly Evaluations
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Information Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="info-section p-4">
            <h4><i class="fas fa-info-circle me-2"></i>About Questionnaires & AI Integration</h4>
            
            <!-- AI Implementation Notice -->
            <div class="alert alert-primary mb-3">
                <h6><i class="fas fa-robot me-2"></i>AI-Powered Learning Personalization (Proof of Concept)</h6>
                <p class="mb-2">This demonstration showcases how psychological questionnaires will integrate with our AI systems:</p>
                <div class="row">
                    <div class="col-md-4">
                        <strong>🧠 NLP Integration:</strong> Questionnaire results will guide AI feedback style and complexity level for assignments.
                    </div>
                    <div class="col-md-4">
                        <strong>🎯 RL Optimization:</strong> Learning patterns will be analyzed to recommend optimal study schedules and content difficulty.
                    </div>
                    <div class="col-md-4">
                        <strong>📚 CBF Matching:</strong> Content recommendations will be personalized based on learning style and motivation profiles.
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-brain me-2 text-success"></i>MSLQ (Motivated Strategies for Learning)</h6>
                    <p class="mb-3">Mengukur motivasi dan strategi belajar Anda. Hasil MSLQ membantu AI POINTMARKET memahami gaya belajar Anda dan memberikan rekomendasi yang sesuai.</p>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-heart me-2 text-warning"></i>AMS (Academic Motivation Scale)</h6>
                    <p class="mb-3">Mengukur tipe motivasi akademik Anda. Data AMS digunakan AI untuk menyesuaikan sistem reward dan mendorong engagement belajar.</p>
                </div>
            </div>
            <div class="mt-3">
                <p class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Tip:</strong> Untuk hasil terbaik, jawab dengan jujur berdasarkan kondisi Anda saat ini. 
                    Data ini digunakan AI untuk personalisasi pembelajaran yang lebih efektif.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-12">
        <h4><i class="fas fa-chart-bar me-2"></i>Your Statistics</h4>
    </div>
    <?php if (!empty($stats)): ?>
        <?php foreach ($stats as $stat): ?>
        <div class="col-md-6 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-<?php echo $stat['type'] === 'mslq' ? 'brain' : ($stat['type'] === 'ams' ? 'heart' : 'graduation-cap'); ?> me-2"></i>
                        <?php echo strtoupper($stat['type']); ?>
                    </h5>
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1"><strong>Completed:</strong></p>
                            <h4 class="text-primary"><?php echo htmlspecialchars($stat['total_completed'] ?? 0); ?></h4>
                        </div>
                        <div class="col-6">
                            <?php if ($stat['type'] === 'vark'): ?>
                                <p class="mb-1"><strong>Learning Style:</strong></p>
                                <?php if ($stat['total_completed'] > 0 && $varkResult): ?>
                                    <h6 class="text-success"><?php echo htmlspecialchars($varkResult['dominant_style']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($varkResult['learning_preference']); ?></small>
                                <?php else: ?>
                                    <h6 class="text-muted">Not assessed</h6>
                                <?php endif; ?>
                            <?php else: ?>
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

<!-- Available Questionnaires -->
<div class="row mb-4">
    <div class="col-12">
        <h4><i class="fas fa-list me-2"></i>Available Questionnaires</h4>
        <p class="text-muted">Practice questionnaires untuk memahami format dan konten. Untuk evaluasi mingguan resmi, gunakan <a href="/weekly-evaluations">Weekly Evaluations</a>.</p>
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

<!-- VARK Learning Style Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-brain me-2"></i>
                    VARK Learning Style Assessment
                </h5>
            </div>
            <div class="card-body">
                <?php if ($varkResult): ?>
                    <?php 
                    $learningTips = getVARKLearningTips($varkResult['dominant_style']); 
                    ?>
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-primary">Your Learning Style Profile</h6>
                            <div class="d-flex align-items-center mb-3">
                                <i class="<?php echo htmlspecialchars($learningTips['icon']); ?> fa-2x text-primary me-3"></i>
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($varkResult['learning_preference']); ?></h5>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($learningTips['description']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <h6>VARK Scores:</h6>
                                    <div class="small">
                                        <span class="badge bg-info me-1">Visual: <?php echo htmlspecialchars($varkResult['visual_score']); ?></span>
                                        <span class="badge bg-warning me-1">Auditory: <?php echo htmlspecialchars($varkResult['auditory_score']); ?></span>
                                        <br class="d-sm-none">
                                        <span class="badge bg-success me-1 mt-1">Reading: <?php echo htmlspecialchars($varkResult['reading_score']); ?></span>
                                        <span class="badge bg-danger me-1 mt-1">Kinesthetic: <?php echo htmlspecialchars($varkResult['kinesthetic_score']); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <h6>Study Tips:</h6>
                                    <ul class="small mb-0">
                                        <?php foreach (array_slice($learningTips['study_tips'], 0, 3) as $tip): ?>
                                            <li><?php echo htmlspecialchars($tip); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Completed: <?php echo htmlspecialchars(date('d M Y H:i', strtotime($varkResult['completed_at']))); ?>
                            </small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-muted">Assessment Status</h6>
                                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                                <p class="text-success mb-2">Completed</p>
                                <a href="/vark-assessment" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Retake Assessment
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-primary">Discover Your Learning Style</h6>
                            <p>VARK stands for <strong>V</strong>isual, <strong>A</strong>uditory, <strong>R</strong>eading/Writing, and <strong>K</strong>inesthetic learning styles. 
                            This assessment helps identify your preferred learning mode and provides personalized study recommendations.</p>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <h6>The Four Learning Styles:</h6>
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-eye text-info me-2"></i><strong>Visual:</strong> Charts, diagrams, images</li>
                                        <li><i class="fas fa-volume-up text-warning me-2"></i><strong>Auditory:</strong> Discussion, listening</li>
                                        <li><i class="fas fa-book-open text-success me-2"></i><strong>Reading/Writing:</strong> Text, notes</li>
                                        <li><i class="fas fa-hand-rock text-danger me-2"></i><strong>Kinesthetic:</strong> Hands-on practice</li>
                                    </ul>
                                </div>
                                <div class="col-sm-6">
                                    <h6>Assessment Details:</h6>
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-clock me-2"></i>Time: ~10-15 minutes</li>
                                        <li><i class="fas fa-list-ol me-2"></i>Questions: 16 scenarios</li>
                                        <li><i class="fas fa-tasks me-2"></i>Format: Multiple choice</li>
                                        <li><i class="fas fa-chart-line me-2"></i>Result: Learning profile</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-muted">Take Assessment</h6>
                                <i class="fas fa-brain fa-3x text-muted mb-2"></i>
                                <p class="text-muted mb-2">Not completed</p>
                                <a href="/vark-assessment" class="btn btn-primary">
                                    <i class="fas fa-brain me-1"></i>
                                    Start VARK Assessment
                                </a>
                            </div>
                        </div>
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
        fetch(API_BASE_URL + '/api/v1/questionnaires/submit', {
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