<?php
// Simple VARK Correlation Analysis - Debug Version
session_start();

// For testing, let's bypass authentication temporarily
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Test User';
    $_SESSION['role'] = 'siswa';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VARK Correlation Analysis - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .correlation-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .score-display {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="hero-section text-center">
            <h1><i class="fas fa-chart-network me-3"></i>Analisis Korelasi VARK-MSLQ-AMS</h1>
            <p class="lead">Memahami hubungan antara gaya belajar, motivasi, dan strategi pembelajaran</p>
            <p>Selamat datang! Halaman ini menampilkan data demo untuk demonstrasi.</p>
        </div>

        <div class="alert alert-success">
            <h5><i class="fas fa-check-circle me-2"></i>Status: Halaman Berhasil Dimuat!</h5>
            <p class="mb-0">Analisis korelasi VARK berhasil ditampilkan dengan data demonstrasi.</p>
        </div>

        <!-- Demo VARK Data -->
        <div class="correlation-card">
            <h4><i class="fas fa-eye me-2"></i>Hasil VARK Assessment (Demo Data)</h4>
            <div class="row mt-3">
                <div class="col-md-3 text-center mb-3">
                    <div class="border rounded p-3 border-primary bg-light">
                        <h6>Visual</h6>
                        <div class="score-display text-primary">12</div>
                        <small class="text-muted">Moderat</small>
                    </div>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <div class="border rounded p-3">
                        <h6>Auditory</h6>
                        <div class="score-display text-muted">10</div>
                        <small class="text-muted">Moderat</small>
                    </div>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <div class="border rounded p-3 border-success bg-light">
                        <h6>Reading/Writing</h6>
                        <div class="score-display text-success">16</div>
                        <small class="text-success"><strong>Dominan</strong></small>
                    </div>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <div class="border rounded p-3">
                        <h6>Kinesthetic</h6>
                        <div class="score-display text-muted">8</div>
                        <small class="text-muted">Rendah</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Correlation Insights -->
        <div class="row">
            <div class="col-md-6">
                <div class="correlation-card">
                    <h5><i class="fas fa-brain me-2 text-primary"></i>Korelasi dengan MSLQ</h5>
                    <p><strong>Gaya Reading/Writing</strong> berkorelasi dengan:</p>
                    <ul>
                        <li>Elaboration: <span class="badge bg-success">r = 0.75</span></li>
                        <li>Metacognitive Self-Regulation: <span class="badge bg-success">r = 0.72</span></li>
                        <li>Organization: <span class="badge bg-primary">r = 0.68</span></li>
                        <li>Critical Thinking: <span class="badge bg-primary">r = 0.64</span></li>
                    </ul>
                    <div class="alert alert-primary">
                        <small><strong>Insight:</strong> Reading/Writing learners excel at elaborative and metacognitive strategies.</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="correlation-card">
                    <h5><i class="fas fa-heart me-2 text-success"></i>Korelasi dengan AMS</h5>
                    <p><strong>Gaya Reading/Writing</strong> berkorelasi dengan:</p>
                    <ul>
                        <li>Intrinsic - To Know: <span class="badge bg-success">r = 0.78</span></li>
                        <li>Intrinsic - To Accomplish: <span class="badge bg-success">r = 0.70</span></li>
                        <li>Identified Regulation: <span class="badge bg-primary">r = 0.52</span></li>
                        <li>Introjected Regulation: <span class="badge bg-warning">r = 0.38</span></li>
                    </ul>
                    <div class="alert alert-success">
                        <small><strong>Insight:</strong> High intrinsic motivation for knowledge and achievement.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="correlation-card">
            <h5><i class="fas fa-lightbulb me-2 text-warning"></i>Rekomendasi Berdasarkan Analisis</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6>🎯 Strategi Pembelajaran Optimal:</h6>
                    <ul>
                        <li>Maksimalkan penggunaan teks dan catatan tertulis</li>
                        <li>Gunakan elaborative questioning techniques</li>
                        <li>Implementasikan self-monitoring melalui journaling</li>
                        <li>Manfaatkan mind mapping dan organizational tools</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>💪 Enhancement Motivasi:</h6>
                    <ul>
                        <li>Fokus pada aktivitas knowledge acquisition</li>
                        <li>Set clear achievement goals</li>
                        <li>Provide challenging reading materials</li>
                        <li>Encourage self-directed learning projects</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="text-center my-4">
            <a href="questionnaire-progress.php" class="btn btn-primary me-2">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Progress
            </a>
            <a href="dashboard.php" class="btn btn-secondary me-2">
                <i class="fas fa-home me-1"></i>Dashboard
            </a>
        </div>

        <!-- Technical Info -->
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Teknis</h6>
            </div>
            <div class="card-body">
                <p><strong>Status:</strong> ✅ Halaman berhasil dimuat dengan data demonstrasi</p>
                <p><strong>File:</strong> vark-correlation-simple.php</p>
                <p><strong>Session Active:</strong> <?php echo isset($_SESSION['user_id']) ? 'Yes' : 'No'; ?></p>
                <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
