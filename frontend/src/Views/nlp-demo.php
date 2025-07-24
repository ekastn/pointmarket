<?php
// Data for this view will be passed from the NLPDemoController
$user = $user ?? ['name' => 'Guest'];
$nlpStats = $nlpStats ?? null;
$nlpResult = $nlpResult ?? null;
$messages = $messages ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-brain me-2"></i>
        Demo NLP Analysis
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadExample('good')">
                <i class="fas fa-thumbs-up me-1"></i>Contoh Baik
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadExample('bad')">
                <i class="fas fa-thumbs-down me-1"></i>Contoh Buruk
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAll()">
                <i class="fas fa-eraser me-1"></i>Clear
            </button>
            <!-- <button type="button" class="btn btn-sm btn-outline-info" onclick="testAPI()">
                <i class="fas fa-stethoscope me-1"></i>Test API
            </button> -->
        </div>
    </div>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo $type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Info Alert -->
<div class="alert alert-info mb-4">
    <h6><i class="fas fa-info-circle me-2"></i>Tentang NLP Analysis</h6>
    <p class="mb-2">Sistem NLP (Natural Language Processing) POINTMARKET menganalisis teks Anda berdasarkan beberapa faktor:</p>
    <div class="row">
        <div class="col-md-6">
            <ul class="mb-0">
                <li><strong>Grammar:</strong> Tata bahasa dan ejaan</li>
                <li><strong>Keywords:</strong> Kata kunci yang relevan</li>
                <li><strong>Structure:</strong> Organisasi dan alur</li>
            </ul>
        </div>
        <div class="col-md-6">
            <ul class="mb-0">
                <li><strong>Readability:</strong> Keterbacaan teks</li>
                <li><strong>Sentiment:</strong> Tone positif/negatif</li>
                <li><strong>Complexity:</strong> Tingkat kompleksitas</li>
            </ul>
        </div>
    </div>
</div>

<!-- Demo Form -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-edit me-2"></i>Coba Analisis Teks Anda</h5>
    </div>
    <div class="card-body">
        <form id="nlpDemoForm" action="/nlp-demo/analyze" method="POST">
            <div class="mb-3">
                <label for="text_to_analyze" class="form-label">Tulis teks yang ingin dianalisis:</label>
                <textarea 
                    id="text_to_analyze" 
                    name="text_to_analyze" 
                    class="form-control" 
                    rows="8" 
                    placeholder="Contoh: Teknologi dalam pendidikan sangat penting karena dapat meningkatkan kualitas pembelajaran. Dengan adanya komputer dan internet, siswa dapat mengakses berbagai sumber belajar yang tidak terbatas..."
                    required
                ></textarea>
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Minimal 10 karakter untuk analisis.
                </div>
            </div>
            
            <div class="mb-3">
                <label for="context_type" class="form-label">Konteks:</label>
                <select id="context_type" name="context_type" class="form-select">
                    <option value="general">General</option>
                    <option value="assignment">Assignment (Tugas)</option>
                    <option value="quiz">Quiz</option>
                    <option value="matematik">Matematika</option>
                    <option value="fisika">Fisika</option>
                    <option value="kimia">Kimia</option>
                    <option value="biologi">Biologi</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="assignment_id" class="form-label">Assignment ID (Optional, if context is Assignment):</label>
                <input type="number" id="assignment_id" name="assignment_id" class="form-control" placeholder="e.g., 123">
            </div>
            <div class="mb-3">
                <label for="quiz_id" class="form-label">Quiz ID (Optional, if context is Quiz):</label>
                <input type="number" id="quiz_id" name="quiz_id" class="form-control" placeholder="e.g., 456">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-brain me-2"></i>Analisis Teks
            </button>
        </form>
    </div>
</div>

<?php if ($nlpResult): ?>
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Hasil Analisis AI</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4 <?php echo ($nlpResult['total_score'] >= 80) ? 'text-success' : (($nlpResult['total_score'] >= 60) ? 'text-warning' : 'text-danger'); ?>">
                            <?php echo htmlspecialchars(number_format($nlpResult['total_score'], 1)); ?>
                        </div>
                        <small class="text-muted">Total Score</small>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-6 col-md-4">
                            <div class="text-center">
                                <div class="h5 <?php echo ($nlpResult['grammar_score'] >= 80) ? 'text-success' : (($nlpResult['grammar_score'] >= 60) ? 'text-warning' : 'text-danger'); ?>">
                                    <?php echo htmlspecialchars(number_format($nlpResult['grammar_score'], 1)); ?>
                                </div>
                                <small>Grammar</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-center">
                                <div class="h5 <?php echo ($nlpResult['keyword_score'] >= 80) ? 'text-success' : (($nlpResult['keyword_score'] >= 60) ? 'text-warning' : 'text-danger'); ?>">
                                    <?php echo htmlspecialchars(number_format($nlpResult['keyword_score'], 1)); ?>
                                </div>
                                <small>Keywords</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-center">
                                <div class="h5 <?php echo ($nlpResult['structure_score'] >= 80) ? 'text-success' : (($nlpResult['structure_score'] >= 60) ? 'text-warning' : 'text-danger'); ?>">
                                    <?php echo htmlspecialchars(number_format($nlpResult['structure_score'], 1)); ?>
                                </div>
                                <small>Structure</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <?php
                $scores = [
                    'Grammar' => $nlpResult['grammar_score'],
                    'Keywords' => $nlpResult['keyword_score'],
                    'Structure' => $nlpResult['structure_score'],
                    'Readability' => $nlpResult['readability_score'],
                    'Sentiment' => $nlpResult['sentiment_score'],
                    'Complexity' => $nlpResult['complexity_score'],
                ];
                foreach ($scores as $label => $score): 
                    $progressColor = ($score >= 80) ? 'success' : (($score >= 60) ? 'warning' : 'danger');
                ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <small><?php echo htmlspecialchars($label); ?></small>
                            <small><?php echo htmlspecialchars(number_format($score, 1)); ?>%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-<?php echo $progressColor; ?>" role="progressbar" 
                                 style="width: <?php echo htmlspecialchars($score); ?>%" aria-valuenow="<?php echo htmlspecialchars($score); ?>" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-font me-1"></i>Jumlah Kata: <strong><?php echo htmlspecialchars($nlpResult['word_count']); ?></strong>
                    </small>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-paragraph me-1"></i>Jumlah Kalimat: <strong><?php echo htmlspecialchars($nlpResult['sentence_count']); ?></strong>
                    </small>
                </div>
            </div>
            
            <?php if (!empty($nlpResult['feedback'])): ?>
                <div class="mb-3">
                    <h6><i class="fas fa-comments me-2"></i>Feedback</h6>
                    <div class="alert alert-light">
                        <?php 
                        $feedbackArray = json_decode($nlpResult['feedback'], true); 
                        if (is_array($feedbackArray)) {
                            foreach ($feedbackArray as $f) {
                                echo '<div class="mb-1">' . htmlspecialchars($f) . '</div>';
                            }
                        } else {
                            echo htmlspecialchars($nlpResult['feedback']);
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($nlpResult['personalized_feedback'])): ?>
                <div class="mb-3">
                    <h6><i class="fas fa-user-cog me-2"></i>Feedback Personal</h6>
                    <div class="alert alert-info">
                        <?php 
                        $personalizedFeedbackArray = json_decode($nlpResult['personalized_feedback'], true); 
                        if (is_array($personalizedFeedbackArray)) {
                            foreach ($personalizedFeedbackArray as $f) {
                                echo '<div class="mb-1">' . htmlspecialchars($f) . '</div>';
                            }
                        } else {
                            echo htmlspecialchars($nlpResult['personalized_feedback']);
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div class="card mt-4">
    <div class="card-header">
        <h6><i class="fas fa-chart-bar me-2"></i>Statistik Analisis Anda</h6>
    </div>
    <div class="card-body">
        <?php if ($nlpStats): ?>
            <div id="user-stats">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <h4 class="text-primary"><?php echo htmlspecialchars($nlpStats['total_analyses']); ?></h4>
                        <small class="text-muted">Total Analisis</small>
                    </div>
                    <div class="col-md-4 text-center">
                        <h4 class="text-success"><?php echo htmlspecialchars(number_format($nlpStats['average_score'], 1)); ?></h4>
                        <small class="text-muted">Skor Rata-rata</small>
                    </div>
                    <div class="col-md-4 text-center">
                        <h4 class="text-info"><?php echo htmlspecialchars(number_format($nlpStats['best_score'], 1)); ?></h4>
                        <small class="text-muted">Skor Terbaik</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4 text-center">
                        <small class="text-muted">Grammar Improvement:</small>
                        <h5 class="text-<?php echo ($nlpStats['grammar_improvement'] >= 0) ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars(number_format($nlpStats['grammar_improvement'], 1)); ?>%
                        </h5>
                    </div>
                    <div class="col-md-4 text-center">
                        <small class="text-muted">Keyword Improvement:</small>
                        <h5 class="text-<?php echo ($nlpStats['keyword_improvement'] >= 0) ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars(number_format($nlpStats['keyword_improvement'], 1)); ?>%
                        </h5>
                    </div>
                    <div class="col-md-4 text-center">
                        <small class="text-muted">Structure Improvement:</small>
                        <h5 class="text-<?php echo ($nlpStats['structure_improvement'] >= 0) ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars(number_format($nlpStats['structure_improvement'], 1)); ?>%
                        </h5>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div id="user-stats">
                <div class="text-center text-muted">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <p>Lakukan analisis pertama untuk melihat statistik</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Examples -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6><i class="fas fa-star me-2"></i>Contoh Teks Berkualitas Tinggi</h6>
            </div>
            <div class="card-body">
                <div class="example-text">
                    <p><strong>Topik:</strong> Teknologi dalam Pendidikan</p>
                    <p>"Teknologi dalam pendidikan memainkan peran yang sangat penting dalam meningkatkan kualitas pembelajaran. Pertama, teknologi menyediakan akses ke sumber belajar yang tidak terbatas melalui internet. Kedua, aplikasi pembelajaran interaktif memungkinkan siswa untuk belajar dengan cara yang lebih menarik dan efektif. Ketiga, platform digital memfasilitasi komunikasi antara guru dan siswa di luar jam sekolah. Dengan demikian, integrasi teknologi dalam pendidikan bukan hanya trend, tetapi kebutuhan fundamental untuk menciptakan sistem pembelajaran yang adaptif dan berkelanjutan."</p>
                </div>
                <div class="mt-2">
                    <span class="score-badge score-high">Prediksi Score: 85-92</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Contoh Teks Perlu Perbaikan</h6>
            </div>
            <div class="card-body">
                <div class="example-text">
                    <p><strong>Topik:</strong> Teknologi dalam Pendidikan</p>
                    <p>"teknologi bagus untuk sekolah karena bisa belajar dengan komputer dan internet juga bisa cari materi di google terus bisa ngerjain tugas lebih gampang pokoknya teknologi sangat membantu"</p>
                </div>
                <div class="mt-2">
                    <span class="score-badge score-low">Prediksi Score: 35-45</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation -->
<div class="text-center mt-4">
    <a href="/assignments" class="btn btn-primary me-2">
        <i class="fas fa-tasks me-1"></i>Coba di Assignment
    </a>
    <a href="/ai-explanation" class="btn btn-secondary me-2">
        <i class="fas fa-book me-1"></i>Pelajari Lebih Lanjut
    </a>
    <a href="/dashboard" class="btn btn-outline-secondary">
        <i class="fas fa-home me-1"></i>Kembali ke Dashboard
    </a>
</div>