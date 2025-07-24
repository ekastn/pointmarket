<?php
// Data for this view will be passed from the AIRecommendationsController
// For now, we'll use the same simulated data structure.
$user = $user ?? ['name' => 'Guest'];
$aiMetrics = $aiMetrics ?? [
    'nlp' => ['accuracy' => 0],
    'rl' => ['accuracy' => 0],
    'cbf' => ['accuracy' => 0]
];
$sampleRecommendations = $sampleRecommendations ?? [];
?>

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
                            <h4 class="text-info"><?= number_format($aiMetrics['nlp']['samples_processed'] ?? 0) ?></h4>
                            <small>Texts Analyzed</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-info"><?= $aiMetrics['nlp']['avg_score'] ?? 0 ?></h4>
                            <small>Average Score</small>
                        </div>
                    </div>
                </div>
                <hr>
                <small class="text-muted">
                    <i class="fas fa-arrow-up text-success me-1"></i>
                    Improvement: <?= $aiMetrics['nlp']['improvement_rate'] ?? 0 ?>% this month
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
                            <h4 class="text-primary"><?= number_format($aiMetrics['rl']['decisions_made'] ?? 0) ?></h4>
                            <small>Decisions Made</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary"><?= $aiMetrics['rl']['avg_reward'] ?? 0 ?></h4>
                            <small>Average Reward</small>
                        </div>
                    </div>
                </div>
                <hr>
                <small class="text-muted">
                    <i class="fas fa-brain text-primary me-1"></i>
                    Learning Rate: <?= $aiMetrics['rl']['learning_rate'] ?? 0 ?>
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
                            <h4 class="text-success"><?= number_format($aiMetrics['cbf']['recommendations'] ?? 0) ?></h4>
                            <small>Recommendations</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success"><?= $aiMetrics['cbf']['click_through_rate'] ?? 0 ?>%</h4>
                            <small>Click Rate</small>
                        </div>
                    </div>
                </div>
                <hr>
                <small class="text-muted">
                    <i class="fas fa-star text-warning me-1"></i>
                    User Satisfaction: <?= $aiMetrics['cbf']['user_satisfaction'] ?? 0 ?>/5.0
                </small>
            </div>
        </div>
    </div>
</div>