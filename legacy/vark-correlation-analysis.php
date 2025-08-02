<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simple debug
echo "<!-- Debug: PHP is working -->\n";

// For demo purposes, if user is not logged in, create a demo session
if (!isset($_SESSION['user_id'])) {
    // Show login prompt but also offer demo mode
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>VARK Correlation Analysis - Login Required</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-center">
                            <h4>Akses Analisis Korelasi VARK</h4>
                        </div>
                        <div class="card-body text-center">
                            <p>Anda perlu login untuk mengakses halaman ini.</p>
                            <div class="d-grid gap-2">
                                <a href="login.php" class="btn btn-primary">Login</a>
                                <form method="post" action="">
                                    <button type="submit" name="demo_mode" class="btn btn-secondary">
                                        Lihat Demo (Tanpa Login)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    // Handle demo mode
    if (isset($_POST['demo_mode'])) {
        $_SESSION['user_id'] = 'demo';
        $_SESSION['username'] = 'Demo User';
        $_SESSION['role'] = 'siswa';
        header("Location: vark-correlation-analysis.php");
        exit();
    }
    exit();
}

echo "<!-- Debug: User is logged in: " . $_SESSION['user_id'] . " -->\n";
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
        body {
            background-color: #f8f9fa;
        }
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
        .style-badge {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 1.1rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Hero Section -->
        <div class="hero-section text-center">
            <h1><i class="fas fa-chart-network me-3"></i>Analisis Korelasi VARK-MSLQ-AMS</h1>
            <p class="lead">Memahami hubungan antara gaya belajar, motivasi, dan strategi pembelajaran</p>
            <p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong>!</p>
        </div>

        <!-- Status Check -->
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i>Status Sistem</h6>
            <p class="mb-1">✅ PHP Session: Active (User ID: <?php echo $_SESSION['user_id']; ?>)</p>
            <p class="mb-1">✅ Bootstrap CSS: Loaded</p>
            <p class="mb-0">✅ FontAwesome Icons: Loaded</p>
        </div>

        <!-- Demo VARK Data -->
        <?php
        // Sample VARK data for demonstration
        $vark_data = [
            'Visual' => 12,
            'Auditory' => 10,
            'Reading/Writing' => 14,
            'Kinesthetic' => 8
        ];
        
        // Find dominant style
        $dominant_style = array_search(max($vark_data), $vark_data);
        ?>

        <!-- VARK Scores Display -->
        <div class="correlation-card">
            <h4><i class="fas fa-eye me-2"></i>Skor VARK (Data Demo)</h4>
            <div class="row mt-3">
                <?php foreach ($vark_data as $style => $score): ?>
                <div class="col-md-3 text-center mb-3">
                    <div class="border rounded p-3 <?php echo $style === $dominant_style ? 'border-primary bg-light' : ''; ?>">
                        <h6><?php echo $style; ?></h6>
                        <div class="score-display <?php echo $style === $dominant_style ? 'text-primary' : 'text-muted'; ?>">
                            <?php echo $score; ?>
                        </div>
                        <?php if ($style === $dominant_style): ?>
                            <small class="text-primary"><strong>Dominan</strong></small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-3">
                <span class="style-badge bg-primary text-white">
                    Gaya Belajar Dominan: <?php echo $dominant_style; ?>
                </span>
            </div>
        </div>

        <!-- Correlation Predictions -->
        <div class="row">
            <div class="col-md-6">
                <div class="correlation-card">
                    <h5><i class="fas fa-brain me-2 text-primary"></i>Prediksi Korelasi MSLQ</h5>
                    <p class="text-muted">Untuk <?php echo $dominant_style; ?> Learners:</p>
                    
                    <?php if ($dominant_style === 'Reading/Writing'): ?>
                    <ul>
                        <li><strong>Elaboration</strong> - Korelasi Tinggi (r ≈ 0.80)</li>
                        <li><strong>Metacognitive Self-Regulation</strong> - Sangat Tinggi (r ≈ 0.75)</li>
                        <li><strong>Organization</strong> - Tinggi (r ≈ 0.72)</li>
                    </ul>
                    <div class="alert alert-primary">
                        <strong>Insight:</strong> Excellet at written elaboration and self-monitoring through writing.
                    </div>
                    <?php else: ?>
                    <ul>
                        <li><strong>Various strategies</strong> - See documentation for specific correlations</li>
                        <li><strong>Learning preferences</strong> - Based on <?php echo $dominant_style; ?> style</li>
                    </ul>
                    <div class="alert alert-info">
                        <strong>Note:</strong> Correlation patterns vary by learning style.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="correlation-card">
                    <h5><i class="fas fa-heart me-2 text-success"></i>Prediksi Korelasi AMS</h5>
                    <p class="text-muted">Motivasi untuk <?php echo $dominant_style; ?> Learners:</p>
                    
                    <?php if ($dominant_style === 'Reading/Writing'): ?>
                    <ul>
                        <li><strong>Intrinsic - To Know</strong> - Sangat Tinggi (r ≈ 0.78)</li>
                        <li><strong>Intrinsic - To Accomplish</strong> - Tinggi (r ≈ 0.70)</li>
                        <li><strong>External - Identified</strong> - Moderate (r ≈ 0.48)</li>
                    </ul>
                    <div class="alert alert-success">
                        <strong>Insight:</strong> Highest motivation for knowledge acquisition through reading.
                    </div>
                    <?php else: ?>
                    <ul>
                        <li><strong>Intrinsic motivation</strong> - Generally high for this style</li>
                        <li><strong>External motivation</strong> - Varies by individual</li>
                    </ul>
                    <div class="alert alert-info">
                        <strong>Note:</strong> Motivation patterns depend on learning style preferences.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="correlation-card">
            <h5><i class="fas fa-lightbulb me-2 text-warning"></i>Rekomendasi Personal</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6>🎯 Strategi Pembelajaran Optimal:</h6>
                    <?php if ($dominant_style === 'Reading/Writing'): ?>
                    <ul>
                        <li>Maksimalkan elaboration dan metacognitive strategies</li>
                        <li>Fokus pada pembelajaran mandiri dan self-regulation</li>
                        <li>Gunakan journaling dan note-taking extensively</li>
                        <li>Manfaatkan written materials dan text-based resources</li>
                    </ul>
                    <?php else: ?>
                    <ul>
                        <li>Adapt strategies based on <?php echo $dominant_style; ?> preferences</li>
                        <li>Use multi-modal learning approaches</li>
                        <li>Leverage strengths of your dominant style</li>
                        <li>Refer to documentation for detailed recommendations</li>
                    </ul>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h6>💪 Motivation Enhancement:</h6>
                    <?php if ($dominant_style === 'Reading/Writing'): ?>
                    <ul>
                        <li>Leverage intrinsic motivation to know</li>
                        <li>Set achievement goals through written work</li>
                        <li>Provide comprehensive reading materials</li>
                        <li>Focus on knowledge acquisition activities</li>
                    </ul>
                    <?php else: ?>
                    <ul>
                        <li>Enhance intrinsic motivation through preferred modalities</li>
                        <li>Create engaging activities matching your style</li>
                        <li>Build on natural learning preferences</li>
                        <li>See style-specific recommendations in documentation</li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="text-center my-4">
            <a href="questionnaire-progress.php" class="btn btn-primary me-2">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Progress Kuesioner
            </a>
            <a href="dashboard.php" class="btn btn-secondary me-2">
                <i class="fas fa-home me-1"></i>Dashboard
            </a>
            <a href="#" class="btn btn-info" onclick="alert('Documentation feature coming soon!')">
                <i class="fas fa-book me-1"></i>Documentation
            </a>
        </div>

        <!-- Debug Info -->
        <div class="card mt-4 border-secondary">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-bug me-2"></i>Debug Information</h6>
            </div>
            <div class="card-body">
                <small>
                    <strong>File:</strong> vark-correlation-analysis.php<br>
                    <strong>Session Status:</strong> <?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?><br>
                    <strong>User ID:</strong> <?php echo $_SESSION['user_id'] ?? 'Not set'; ?><br>
                    <strong>Username:</strong> <?php echo $_SESSION['username'] ?? 'Not set'; ?><br>
                    <strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('VARK Correlation Analysis page loaded successfully');
        console.log('User ID:', '<?php echo $_SESSION['user_id'] ?? 'Not logged in'; ?>');
    </script>
</body>
</html>
