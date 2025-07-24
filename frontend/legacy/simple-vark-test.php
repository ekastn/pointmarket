<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple VARK Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>🧪 Simple VARK Correlation Test</h1>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="alert alert-warning">
                <h5>Silakan Login Terlebih Dahulu</h5>
                <p>Anda perlu login untuk mengakses analisis korelasi VARK.</p>
                <a href="login.php" class="btn btn-primary">Login</a>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <h5>✅ Logged In Successfully</h5>
                <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
                <p>Role: <?php echo $_SESSION['role'] ?? 'unknown'; ?></p>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>📊 VARK-MSLQ-AMS Correlation Analysis</h5>
                </div>
                <div class="card-body">
                    <p>This page will show correlation analysis between:</p>
                    <ul>
                        <li><strong>VARK</strong> - Learning Styles (Visual, Auditory, Reading/Writing, Kinesthetic)</li>
                        <li><strong>MSLQ</strong> - Motivated Strategies for Learning Questionnaire</li>
                        <li><strong>AMS</strong> - Academic Motivation Scale</li>
                    </ul>
                    
                    <div class="mt-4">
                        <h6>Demo Data:</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h6>Visual</h6>
                                        <div class="display-6 text-primary">12</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h6>Auditory</h6>
                                        <div class="display-6 text-success">10</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h6>Reading/Writing</h6>
                                        <div class="display-6 text-warning">14</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h6>Kinesthetic</h6>
                                        <div class="display-6 text-danger">8</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <p><strong>Dominant Style:</strong> <span class="badge bg-warning">Reading/Writing</span></p>
                            <p><strong>Prediction:</strong> Sangat tinggi pada strategi mandiri dan terstruktur dalam pembelajaran.</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="questionnaire-progress.php" class="btn btn-primary">🔙 Kembali ke Progress Kuesioner</a>
                        <a href="vark-correlation-analysis.php" class="btn btn-info">🔄 Full Analysis Version</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
