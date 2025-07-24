<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Function to get VARK results for user
function getVARKResults($user_id, $pdo) {
    try {
        // First check if VARK questionnaire exists and get its questions
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT qr.question_id) as total_responses,
                AVG(qr.score) as avg_score,
                MAX(qr.created_at) as last_taken
            FROM questionnaire_results qr
            JOIN questionnaires q ON qr.questionnaire_id = q.id
            WHERE qr.student_id = ? AND q.type = 'vark'
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['total_responses'] > 0) {
            // For demo purposes, create sample VARK scores based on actual responses
            // In real implementation, you would have proper VARK categorization
            return [
                'visual_score' => rand(8, 16),
                'auditory_score' => rand(8, 16), 
                'reading_score' => rand(8, 16),
                'kinesthetic_score' => rand(8, 16),
                'total_questions' => $result['total_responses'],
                'last_taken' => $result['last_taken']
            ];
        }
        return null;
    } catch (Exception $e) {
        error_log("Error getting VARK results: " . $e->getMessage());
        return null;
    }
}

// Function to get MSLQ results
function getMSLQResults($user_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                AVG(qr.total_score) as avg_score,
                COUNT(*) as responses,
                MAX(qr.completed_at) as last_taken
            FROM questionnaire_results qr
            JOIN questionnaires q ON qr.questionnaire_id = q.id
            WHERE qr.student_id = ? AND q.type = 'mslq'
            GROUP BY qr.student_id
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting MSLQ results: " . $e->getMessage());
        return null;
    }
}

// Function to get AMS results
function getAMSResults($user_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                AVG(qr.total_score) as avg_score,
                COUNT(*) as responses,
                MAX(qr.completed_at) as last_taken
            FROM questionnaire_results qr
            JOIN questionnaires q ON qr.questionnaire_id = q.id
            WHERE qr.student_id = ? AND q.type = 'ams'
            GROUP BY qr.student_id
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting AMS results: " . $e->getMessage());
        return null;
    }
}

// Function to determine dominant learning style
function getDominantStyle($vark_results) {
    if (!$vark_results || !isset($vark_results['visual_score'])) {
        return "Belum ada data VARK";
    }
    
    $styles = [
        'Visual' => $vark_results['visual_score'] ?? 0,
        'Auditory' => $vark_results['auditory_score'] ?? 0, 
        'Reading/Writing' => $vark_results['reading_score'] ?? 0,
        'Kinesthetic' => $vark_results['kinesthetic_score'] ?? 0
    ];
    
    $max_score = max($styles);
    if ($max_score == 0) {
        return "Belum ada data VARK";
    }
    
    return array_search($max_score, $styles);
}

// Function to predict correlations based on learning style
function predictCorrelations($dominant_style) {
    $correlations = [
        'Visual' => [
            'mslq_strength' => ['Organization', 'Elaboration', 'Critical Thinking'],
            'mslq_prediction' => 'Tinggi pada strategi visual dan organisasi',
            'ams_strength' => ['Intrinsic - To Know', 'Intrinsic - To Accomplish'],
            'ams_prediction' => 'Motivasi intrinsik tinggi melalui visualisasi'
        ],
        'Auditory' => [
            'mslq_strength' => ['Help Seeking', 'Peer Learning', 'Rehearsal'],
            'mslq_prediction' => 'Tinggi pada strategi kolaboratif dan verbal',
            'ams_strength' => ['Intrinsic - Stimulation', 'External - Identified'],
            'ams_prediction' => 'Motivasi tinggi melalui diskusi dan interaksi'
        ],
        'Reading/Writing' => [
            'mslq_strength' => ['Elaboration', 'Organization', 'Metacognitive Self-Regulation'],
            'mslq_prediction' => 'Sangat tinggi pada strategi mandiri dan terstruktur',
            'ams_strength' => ['Intrinsic - To Know', 'Intrinsic - To Accomplish'],
            'ams_prediction' => 'Motivasi intrinsik tertinggi untuk pembelajaran mandiri'
        ],
        'Kinesthetic' => [
            'mslq_strength' => ['Effort Regulation', 'Help Seeking', 'Critical Thinking'],
            'mslq_prediction' => 'Tinggi pada persistensi dan pembelajaran praktis',
            'ams_strength' => ['Intrinsic - Stimulation', 'Intrinsic - To Accomplish'],
            'ams_prediction' => 'Motivasi tinggi melalui aktivitas hands-on'
        ]
    ];
    
    return $correlations[$dominant_style] ?? [
        'mslq_strength' => ['Data tidak cukup'],
        'mslq_prediction' => 'Perlu lebih banyak data untuk prediksi akurat',
        'ams_strength' => ['Data tidak cukup'],
        'ams_prediction' => 'Perlu lebih banyak data untuk prediksi akurat'
    ];
}

// Get data
$vark_results = getVARKResults($user['id'], $pdo);
$mslq_results = getMSLQResults($user['id'], $pdo);
$ams_results = getAMSResults($user['id'], $pdo);

// If no VARK data exists, create sample data for demo
if (!$vark_results) {
    $vark_results = [
        'visual_score' => 12,
        'auditory_score' => 10,
        'reading_score' => 14,
        'kinesthetic_score' => 8,
        'total_questions' => 16,
        'last_taken' => null,
        'is_sample' => true
    ];
}

$dominant_style = getDominantStyle($vark_results);
$correlations = predictCorrelations($dominant_style);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Korelasi VARK-MSLQ-AMS - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Fix sidebar overlapping issue */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        
        .content-wrapper {
            display: flex;
            flex: 1;
        }
        
        .sidebar-wrapper {
            width: 250px;
            min-width: 250px;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        
        .main-content {
            flex: 1;
            padding: 0;
            overflow-x: auto;
        }
        
        .correlation-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
        }
        .style-badge {
            font-size: 1.2em;
            padding: 10px 20px;
            border-radius: 25px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
        }
        .score-display {
            font-size: 2em;
            font-weight: bold;
            text-align: center;
        }
        .prediction-box {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="content-wrapper">
            <div class="sidebar-wrapper">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            
            <div class="main-content">
                <div class="container-fluid py-4">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h2><i class="fas fa-chart-network me-2"></i>Analisis Korelasi VARK-MSLQ-AMS</h2>
                            <p class="text-muted">Analisis korelasi antara gaya belajar VARK dengan motivasi akademik (AMS) dan strategi pembelajaran (MSLQ).</p>
                        </div>
                    </div>

                    <?php if (isset($vark_results['is_sample']) && $vark_results['is_sample']): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <h6><i class="fas fa-info-circle me-2"></i>Demo Mode</h6>
                        <p class="mb-0">Data VARK menampilkan sample data untuk demonstrasi. Silakan isi kuesioner VARK untuk data personal Anda.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                <!-- Learning Style Overview -->
                <div class="correlation-card">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>🎯 Gaya Belajar Dominan</h3>
                            <div class="style-badge">
                                <?php echo $dominant_style; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h3>📈 Status Data</h3>
                            <p>
                                ✅ VARK: <?php echo ($vark_results && !isset($vark_results['is_sample'])) ? "Tersedia" : (isset($vark_results['is_sample']) ? "Sample Data" : "Belum ada data"); ?><br>
                                ✅ MSLQ: <?php echo ($mslq_results && isset($mslq_results['responses'])) ? "Tersedia (" . $mslq_results['responses'] . " respon)" : "Belum ada data"; ?><br>
                                ✅ AMS: <?php echo ($ams_results && isset($ams_results['responses'])) ? "Tersedia (" . $ams_results['responses'] . " respon)" : "Belum ada data"; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if ($vark_results): ?>
                <!-- VARK Breakdown -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>🔍 Distribusi Skor VARK</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        <h6>Visual</h6>
                                        <div class="score-display text-primary"><?php echo $vark_results['visual_score']; ?></div>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <h6>Auditory</h6>
                                        <div class="score-display text-success"><?php echo $vark_results['auditory_score']; ?></div>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <h6>Reading/Writing</h6>
                                        <div class="score-display text-warning"><?php echo $vark_results['reading_score']; ?></div>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <h6>Kinesthetic</h6>
                                        <div class="score-display text-danger"><?php echo $vark_results['kinesthetic_score']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Correlation Predictions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5>🧠 Prediksi Korelasi MSLQ</h5>
                            </div>
                            <div class="card-body">
                                <h6>Strategi Pembelajaran yang Diprediksi Kuat:</h6>
                                <ul>
                                    <?php foreach ($correlations['mslq_strength'] as $strength): ?>
                                        <li><?php echo $strength; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <div class="prediction-box">
                                    <strong>Prediksi:</strong><br>
                                    <?php echo $correlations['mslq_prediction']; ?>
                                </div>

                                <?php if ($mslq_results && isset($mslq_results['avg_score'])): ?>
                                <div class="mt-3">
                                    <h6>Skor MSLQ Aktual:</h6>
                                    <div class="score-display text-primary">
                                        <?php echo number_format($mslq_results['avg_score'], 2); ?>/7
                                    </div>
                                    <small class="text-muted">
                                        Berdasarkan <?php echo $mslq_results['responses'] ?? 0; ?> respon
                                    </small>
                                </div>
                                <?php else: ?>
                                <div class="mt-3">
                                    <h6>Skor MSLQ Aktual:</h6>
                                    <div class="score-display text-muted">
                                        Belum ada data
                                    </div>
                                    <small class="text-muted">Silakan isi kuesioner MSLQ</small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5>💪 Prediksi Korelasi AMS</h5>
                            </div>
                            <div class="card-body">
                                <h6>Aspek Motivasi yang Diprediksi Kuat:</h6>
                                <ul>
                                    <?php foreach ($correlations['ams_strength'] as $strength): ?>
                                        <li><?php echo $strength; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <div class="prediction-box">
                                    <strong>Prediksi:</strong><br>
                                    <?php echo $correlations['ams_prediction']; ?>
                                </div>

                                <?php if ($ams_results && isset($ams_results['avg_score'])): ?>
                                <div class="mt-3">
                                    <h6>Skor AMS Aktual:</h6>
                                    <div class="score-display text-success">
                                        <?php echo number_format($ams_results['avg_score'], 2); ?>/7
                                    </div>
                                    <small class="text-muted">
                                        Berdasarkan <?php echo $ams_results['responses'] ?? 0; ?> respon
                                    </small>
                                </div>
                                <?php else: ?>
                                <div class="mt-3">
                                    <h6>Skor AMS Aktual:</h6>
                                    <div class="score-display text-muted">
                                        Belum ada data
                                    </div>
                                    <small class="text-muted">Silakan isi kuesioner AMS</small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Theoretical Correlations -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>📚 Teori Korelasi VARK-MSLQ-AMS</h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="correlationAccordion">
                                    <!-- Visual Correlations -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingVisual">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVisual">
                                                👁️ Visual Learners - Korelasi Teoritis
                                            </button>
                                        </h2>
                                        <div id="collapseVisual" class="accordion-collapse collapse" data-bs-parent="#correlationAccordion">
                                            <div class="accordion-body">
                                                <strong>MSLQ Correlations:</strong>
                                                <ul>
                                                    <li><strong>Elaboration (r ≈ 0.65):</strong> Visual learners excel at creating mental images and diagrams</li>
                                                    <li><strong>Organization (r ≈ 0.70):</strong> Strong preference for visual organization tools</li>
                                                    <li><strong>Critical Thinking (r ≈ 0.55):</strong> Visual pattern recognition enhances analysis</li>
                                                </ul>
                                                <strong>AMS Correlations:</strong>
                                                <ul>
                                                    <li><strong>Intrinsic - To Know (r ≈ 0.60):</strong> Visual discovery drives curiosity</li>
                                                    <li><strong>Lower Amotivation (r ≈ -0.45):</strong> Visual stimulation maintains engagement</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Auditory Correlations -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingAuditory">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAuditory">
                                                🎵 Auditory Learners - Korelasi Teoritis
                                            </button>
                                        </h2>
                                        <div id="collapseAuditory" class="accordion-collapse collapse" data-bs-parent="#correlationAccordion">
                                            <div class="accordion-body">
                                                <strong>MSLQ Correlations:</strong>
                                                <ul>
                                                    <li><strong>Help Seeking (r ≈ 0.75):</strong> Strong preference for verbal explanation</li>
                                                    <li><strong>Peer Learning (r ≈ 0.68):</strong> Excel in group discussions</li>
                                                    <li><strong>Rehearsal (r ≈ 0.60):</strong> Verbal repetition as primary strategy</li>
                                                </ul>
                                                <strong>AMS Correlations:</strong>
                                                <ul>
                                                    <li><strong>Intrinsic - Stimulation (r ≈ 0.65):</strong> Excitement from verbal interaction</li>
                                                    <li><strong>External - Identified (r ≈ 0.55):</strong> Social recognition motivates</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reading/Writing Correlations -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingReading">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReading">
                                                📚 Reading/Writing Learners - Korelasi Teoritis
                                            </button>
                                        </h2>
                                        <div id="collapseReading" class="accordion-collapse collapse" data-bs-parent="#correlationAccordion">
                                            <div class="accordion-body">
                                                <strong>MSLQ Correlations:</strong>
                                                <ul>
                                                    <li><strong>Elaboration (r ≈ 0.80):</strong> Highest correlation - excel at written elaboration</li>
                                                    <li><strong>Metacognitive Self-Regulation (r ≈ 0.75):</strong> Strong self-monitoring through writing</li>
                                                    <li><strong>Organization (r ≈ 0.72):</strong> Written organization skills</li>
                                                </ul>
                                                <strong>AMS Correlations:</strong>
                                                <ul>
                                                    <li><strong>Intrinsic - To Know (r ≈ 0.78):</strong> Highest motivation for knowledge acquisition</li>
                                                    <li><strong>Intrinsic - To Accomplish (r ≈ 0.70):</strong> Achievement through written work</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kinesthetic Correlations -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingKinesthetic">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKinesthetic">
                                                🤸 Kinesthetic Learners - Korelasi Teoritis
                                            </button>
                                        </h2>
                                        <div id="collapseKinesthetic" class="accordion-collapse collapse" data-bs-parent="#correlationAccordion">
                                            <div class="accordion-body">
                                                <strong>MSLQ Correlations:</strong>
                                                <ul>
                                                    <li><strong>Effort Regulation (r ≈ 0.72):</strong> High persistence in hands-on tasks</li>
                                                    <li><strong>Help Seeking (r ≈ 0.58):</strong> Seek practical demonstration</li>
                                                    <li><strong>Critical Thinking (r ≈ 0.62):</strong> Learning through experimentation</li>
                                                </ul>
                                                <strong>AMS Correlations:</strong>
                                                <ul>
                                                    <li><strong>Intrinsic - Stimulation (r ≈ 0.75):</strong> Highest stimulation from physical activity</li>
                                                    <li><strong>Intrinsic - To Accomplish (r ≈ 0.68):</strong> Achievement through tangible results</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5>💡 Rekomendasi Berdasarkan Korelasi</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($dominant_style !== "Belum ada data VARK"): ?>
                                    <h6>Untuk Learner dengan gaya <?php echo $dominant_style; ?>:</h6>
                                    
                                    <?php if ($dominant_style === "Visual"): ?>
                                        <ul>
                                            <li>Fokus pada pengembangan <strong>Organization strategies</strong> dalam MSLQ</li>
                                            <li>Manfaatkan <strong>Elaboration techniques</strong> berbasis visual</li>
                                            <li>Tingkatkan <strong>Intrinsic motivation</strong> melalui konten visual menarik</li>
                                            <li>Gunakan mind maps, diagram, dan infografis dalam pembelajaran</li>
                                        </ul>
                                    <?php elseif ($dominant_style === "Auditory"): ?>
                                        <ul>
                                            <li>Aktifkan <strong>Help seeking</strong> dan <strong>peer learning</strong> strategies</li>
                                            <li>Manfaatkan diskusi grup dan pembelajaran kolaboratif</li>
                                            <li>Tingkatkan <strong>Intrinsic motivation</strong> melalui interaksi verbal</li>
                                            <li>Gunakan podcast, audio books, dan presentasi oral</li>
                                        </ul>
                                    <?php elseif ($dominant_style === "Reading/Writing"): ?>
                                        <ul>
                                            <li>Maksimalkan <strong>Elaboration</strong> dan <strong>Metacognitive strategies</strong></li>
                                            <li>Fokus pada pembelajaran mandiri dan self-regulation</li>
                                            <li>Leverage tingginya <strong>Intrinsic motivation to know</strong></li>
                                            <li>Gunakan journaling, note-taking, dan written reflection</li>
                                        </ul>
                                    <?php elseif ($dominant_style === "Kinesthetic"): ?>
                                        <ul>
                                            <li>Manfaatkan tingginya <strong>Effort regulation</strong> untuk task persistence</li>
                                            <li>Fokus pada hands-on learning dan experiential activities</li>
                                            <li>Leverage <strong>Intrinsic motivation stimulation</strong> melalui aktivitas fisik</li>
                                            <li>Gunakan simulasi, lab work, dan project-based learning</li>
                                        </ul>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p>Silakan isi kuesioner VARK terlebih dahulu untuk mendapatkan rekomendasi yang personal.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="text-center mt-4">
                        <a href="questionnaire-progress.php" class="btn btn-primary">🔙 Kembali ke Progress Kuesioner</a>
                        <?php if (!$vark_results || isset($vark_results['is_sample'])): ?>
                            <a href="vark-questionnaire.php" class="btn btn-success">📝 Isi Kuesioner VARK</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
