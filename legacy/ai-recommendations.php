<?php
require_once 'includes/config.php';
requireLogin();

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Simulate AI scores and recommendations
$aiMetrics = [
    'nlp' => [
        'accuracy' => 89,
        'samples_processed' => 1247,
        'avg_score' => 78.5,
        'improvement_rate' => 12.3
    ],
    'rl' => [
        'accuracy' => 87,
        'decisions_made' => 892,
        'avg_reward' => 0.84,
        'learning_rate' => 0.95
    ],
    'cbf' => [
        'accuracy' => 92,
        'recommendations' => 567,
        'click_through_rate' => 34.2,
        'user_satisfaction' => 4.6
    ]
];

// Get sample recommendations for current user
$sampleRecommendations = [
    [
        'type' => 'NLP',
        'title' => 'Tingkatkan Kualitas Essay Anda',
        'description' => 'Berdasarkan analisis teks terakhir, fokus pada struktur kalimat dan variasi kata.',
        'action' => 'Pelajari Teknik Penulisan',
        'confidence' => 89
    ],
    [
        'type' => 'RL',
        'title' => 'Waktu Belajar Optimal',
        'description' => 'Performa terbaik Anda di pagi hari (08:00-10:00). Atur jadwal belajar di waktu ini.',
        'action' => 'Atur Reminder',
        'confidence' => 87
    ],
    [
        'type' => 'CBF',
        'title' => 'Materi Rekomendasi',
        'description' => 'Siswa dengan profil serupa sukses dengan video "Matematika Visual".',
        'action' => 'Tonton Video',
        'confidence' => 92
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Recommendations - POINTMARKET</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .recommendation-card {
            border-left: 5px solid;
            transition: all 0.3s ease;
        }
        .recommendation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .nlp-border { border-left-color: #17a2b8; }
        .rl-border { border-left-color: #007bff; }
        .cbf-border { border-left-color: #28a745; }
        
        .confidence-bar {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
        }
        
        .real-time-demo {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border: 2px dashed #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-robot me-2"></i>
                        AI Recommendations
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshRecommendations()">
                                <i class="fas fa-sync-alt me-1"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- AI Metrics Overview -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card metric-card">
                            <div class="card-body text-center">
                                <i class="fas fa-language fa-3x mb-3 opacity-75"></i>
                                <h5>Natural Language Processing</h5>
                                <div class="d-flex justify-content-between">
                                    <small>Accuracy:</small>
                                    <strong><?= $aiMetrics['nlp']['accuracy'] ?>%</strong>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: <?= $aiMetrics['nlp']['accuracy'] ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card metric-card">
                            <div class="card-body text-center">
                                <i class="fas fa-brain fa-3x mb-3 opacity-75"></i>
                                <h5>Reinforcement Learning</h5>
                                <div class="d-flex justify-content-between">
                                    <small>Accuracy:</small>
                                    <strong><?= $aiMetrics['rl']['accuracy'] ?>%</strong>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $aiMetrics['rl']['accuracy'] ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card metric-card">
                            <div class="card-body text-center">
                                <i class="fas fa-filter fa-3x mb-3 opacity-75"></i>
                                <h5>Collaborative Filtering</h5>
                                <div class="d-flex justify-content-between">
                                    <small>Accuracy:</small>
                                    <strong><?= $aiMetrics['cbf']['accuracy'] ?>%</strong>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: <?= $aiMetrics['cbf']['accuracy'] ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personalized Recommendations -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h3><i class="fas fa-user-cog me-2"></i>Rekomendasi Personal untuk <?= htmlspecialchars($user['name']) ?></h3>
                        <p class="text-muted">Berdasarkan analisis AI terhadap pola belajar dan performa Anda</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <?php foreach ($sampleRecommendations as $index => $rec): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card recommendation-card h-100 <?= strtolower($rec['type']) ?>-border">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-<?= $rec['type'] == 'NLP' ? 'info' : ($rec['type'] == 'RL' ? 'primary' : 'success') ?>">
                                        <?= $rec['type'] ?>
                                    </span>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= rand(5, 60) ?> menit lalu
                                    </small>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title"><?= $rec['title'] ?></h6>
                                <p class="card-text"><?= $rec['description'] ?></p>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Confidence Level:</small>
                                    <div class="confidence-bar bg-light">
                                        <div class="bg-<?= $rec['type'] == 'NLP' ? 'info' : ($rec['type'] == 'RL' ? 'primary' : 'success') ?>" 
                                             style="width: <?= $rec['confidence'] ?>%; height: 100%; transition: width 2s ease;"></div>
                                    </div>
                                    <small class="text-muted"><?= $rec['confidence'] ?>%</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-<?= $rec['type'] == 'NLP' ? 'info' : ($rec['type'] == 'RL' ? 'primary' : 'success') ?> btn-sm w-100" 
                                        onclick="implementRecommendation('<?= $rec['type'] ?>', <?= $index ?>)">
                                    <i class="fas fa-check me-1"></i>
                                    <?= $rec['action'] ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Real-time AI Demo -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-flask me-2"></i>Demo Real-time AI Processing</h5>
                            </div>
                            <div class="card-body">
                                <div class="real-time-demo" id="aiDemo">
                                    <div class="text-center">
                                        <i class="fas fa-play-circle fa-3x text-primary mb-3"></i>
                                        <h6>Klik tombol di bawah untuk melihat AI bekerja secara real-time</h6>
                                        <button class="btn btn-primary" onclick="startAIDemo()">
                                            <i class="fas fa-play me-2"></i>
                                            Mulai Demo AI
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Statistics -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6><i class="fas fa-language me-2"></i>NLP Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <h4 class="text-info"><?= number_format($aiMetrics['nlp']['samples_processed']) ?></h4>
                                            <small>Texts Analyzed</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <h4 class="text-info"><?= $aiMetrics['nlp']['avg_score'] ?></h4>
                                            <small>Average Score</small>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-arrow-up text-success me-1"></i>
                                    Improvement: <?= $aiMetrics['nlp']['improvement_rate'] ?>% this month
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6><i class="fas fa-brain me-2"></i>RL Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <h4 class="text-primary"><?= number_format($aiMetrics['rl']['decisions_made']) ?></h4>
                                            <small>Decisions Made</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <h4 class="text-primary"><?= $aiMetrics['rl']['avg_reward'] ?></h4>
                                            <small>Average Reward</small>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-brain text-primary me-1"></i>
                                    Learning Rate: <?= $aiMetrics['rl']['learning_rate'] ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6><i class="fas fa-filter me-2"></i>CBF Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <h4 class="text-success"><?= number_format($aiMetrics['cbf']['recommendations']) ?></h4>
                                            <small>Recommendations</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <h4 class="text-success"><?= $aiMetrics['cbf']['click_through_rate'] ?>%</h4>
                                            <small>Click Rate</small>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-star text-warning me-1"></i>
                                    User Satisfaction: <?= $aiMetrics['cbf']['user_satisfaction'] ?>/5.0
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function refreshRecommendations() {
            // Simulate refresh
            const button = event.target;
            const icon = button.querySelector('i');
            
            icon.classList.add('fa-spin');
            button.disabled = true;
            
            setTimeout(() => {
                icon.classList.remove('fa-spin');
                button.disabled = false;
                
                // Show success message
                showAlert('Recommendations updated successfully!', 'success');
                
                // Animate confidence bars
                document.querySelectorAll('.confidence-bar > div').forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
            }, 2000);
        }
        
        function implementRecommendation(type, index) {
            const actions = {
                'NLP': 'Membuka panduan penulisan...',
                'RL': 'Mengatur reminder belajar...',
                'CBF': 'Mengarahkan ke materi rekomendasi...'
            };
            
            showAlert(actions[type], 'info');
            
            // Simulate implementation
            setTimeout(() => {
                showAlert(`${type} recommendation implemented successfully!`, 'success');
            }, 1500);
        }
        
        function startAIDemo() {
            const demoContainer = document.getElementById('aiDemo');
            
            // Clear container
            demoContainer.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6>AI sedang memproses data Anda...</h6>
                    <div class="progress mt-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             style="width: 0%" id="demoProgress"></div>
                    </div>
                </div>
            `;
            
            // Simulate processing stages
            const stages = [
                { progress: 20, text: 'Menganalisis pola belajar...' },
                { progress: 40, text: 'Menjalankan NLP processing...' },
                { progress: 60, text: 'Menghitung RL rewards...' },
                { progress: 80, text: 'Menyiapkan CBF recommendations...' },
                { progress: 100, text: 'Selesai!' }
            ];
            
            let currentStage = 0;
            const progressBar = document.getElementById('demoProgress');
            
            const interval = setInterval(() => {
                if (currentStage < stages.length) {
                    const stage = stages[currentStage];
                    progressBar.style.width = stage.progress + '%';
                    demoContainer.querySelector('h6').textContent = stage.text;
                    currentStage++;
                } else {
                    clearInterval(interval);
                    
                    // Show results
                    setTimeout(() => {
                        demoContainer.innerHTML = `
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <div class="p-3 border rounded bg-info text-white">
                                        <i class="fas fa-language fa-2x mb-2"></i>
                                        <h6>NLP Analysis</h6>
                                        <p class="mb-0">Essay quality: 78/100<br>
                                        Suggestion: Improve structure</p>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="p-3 border rounded bg-primary text-white">
                                        <i class="fas fa-brain fa-2x mb-2"></i>
                                        <h6>RL Recommendation</h6>
                                        <p class="mb-0">Best time: 08:00 AM<br>
                                        Subject: Mathematics</p>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="p-3 border rounded bg-success text-white">
                                        <i class="fas fa-filter fa-2x mb-2"></i>
                                        <h6>CBF Suggestion</h6>
                                        <p class="mb-0">Video: "Algebra Basics"<br>
                                        Match: 94% similarity</p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button class="btn btn-outline-primary" onclick="startAIDemo()">
                                    <i class="fas fa-redo me-2"></i>Run Demo Again
                                </button>
                            </div>
                        `;
                    }, 1000);
                }
            }, 800);
        }
        
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'info' ? 'info-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const container = document.querySelector('main');
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto-dismiss after 3 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 3000);
        }
        
        // Initialize animated counters on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Animate confidence bars
            setTimeout(() => {
                document.querySelectorAll('.confidence-bar > div').forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
            }, 500);
        });
    </script>
</body>
</html>
