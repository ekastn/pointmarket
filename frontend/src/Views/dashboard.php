<?php
// Data for the view will be passed from the DashboardController
// $user, $studentStats, $questionnaireScores, $counts, $messages, $aiMetrics

// Ensure variables are defined to prevent PHP notices if not passed
$user = $user ?? ['name' => 'Guest', 'role' => 'guest'];
$studentStats = $studentStats ?? ['total_points' => 0, 'completed_assignments' => 0, 'mslq_score' => null, 'ams_score' => null, 'vark_dominant_style' => null, 'vark_learning_preference' => null];
$questionnaireScores = $questionnaireScores ?? ['mslq' => null, 'ams' => null, 'vark' => null];
$counts = $counts ?? [];
$messages = $messages ?? [];
$aiMetrics = $aiMetrics ?? [
    'nlp' => ['accuracy' => 0, 'samples_processed' => 0, 'avg_score' => 0, 'improvement_rate' => 0],
    'rl' => ['accuracy' => 0, 'decisions_made' => 0, 'avg_reward' => 0, 'learning_rate' => 0],
    'cbf' => ['accuracy' => 0, 'recommendations' => 0, 'click_through_rate' => 0, 'user_satisfaction' => 0]
];

// Helper functions that were previously global in config.php
// These should ideally be moved to a utility class or passed from the controller
if (!function_exists('formatPoints')) {
    function formatPoints($points) {
        return number_format($points, 0, ',', '.');
    }
}
require_once __DIR__ . '/../Helpers/VARKHelpers.php';
use App\Helpers\VARKHelpers;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2"></i>
        Dasbor
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download me-1"></i>
                Ekspor
            </button>
        </div>
    </div>
</div>

<!-- Messages -->
<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($type); ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="card-title mb-2">
                            Selamat datang, <?php echo htmlspecialchars($user['name']); ?>!
                        </h4>
                        <p class="card-text mb-0">
                            Peran: <?php echo htmlspecialchars(ucfirst($user['role'])); ?> | 
                            Login terakhir: <?php echo htmlspecialchars(date('d/m/Y H:i')); ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <i class="fas fa-user-circle fa-4x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($user['role'] === 'siswa'): ?>
    <!-- AI Features Proof of Concept Notice for Students -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible fade show demo-alert" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-robot fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Demo Sistem AI POINTMARKET
                        </h6>
                        <p class="mb-2">
                            <strong>Sistem ini adalah demonstrasi konsep (Proof of Concept)</strong> untuk menunjukkan cara kerja platform pembelajaran berbasis AI. 
                            Saat ini menggunakan data simulasi untuk keperluan demo.
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Fitur AI yang akan diimplementasi:</strong>
                                <ul class="small mb-0 mt-1">
                                    <li>Analisis pembelajaran mendalam</li>
                                    <li>Rekomendasi konten personal</li>
                                    <li>Prediksi performa akademik</li>
                                    <li>Adaptasi gaya belajar otomatis</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <strong>Status saat ini:</strong>
                                <ul class="small mb-0 mt-1">
                                    <li>✓ Interface dan workflow lengkap</li>
                                    <li>⚠️ Skor menggunakan simulasi random</li>
                                    <li>🔄 AI engine dalam pengembangan</li>
                                    <li>📊 Data untuk training AI dikumpulkan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    
    <!-- Student Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Poin
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo htmlspecialchars(formatPoints($studentStats['total_points'] ?? 0)); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Diselesaikan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo htmlspecialchars($studentStats['completed_assignments'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Skor MSLQ
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo htmlspecialchars($studentStats['mslq_score'] !== null ? number_format($studentStats['mslq_score'], 1) : 'T/A'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-brain fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Skor AMS
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo htmlspecialchars($studentStats['ams_score'] !== null ? number_format($studentStats['ams_score'], 1) : 'T/A'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- VARK Learning Style Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-primary shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-brain me-2"></i>
                        Profil Gaya Belajar Anda
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($studentStats && $studentStats['vark_dominant_style'] !== null): ?>
                        <?php 
                        $varkDominantStyle = $studentStats['vark_dominant_style'];
                        $varkLearningPreference = $studentStats['vark_learning_preference'];
                        $learningTips = getVARKLearningTips($varkDominantStyle); 
                        ?>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h5 class="text-primary mb-2">
                                            <i class="<?php echo htmlspecialchars($learningTips['icon']); ?> me-2"></i>
                                            <?php echo htmlspecialchars($varkLearningPreference); ?>
                                        </h5>
                                        <p class="text-muted mb-3">
                                            <?php echo htmlspecialchars($learningTips['description']); ?>
                                        </p>
                                        
                                        <div class="vark-scores">
                                            <small class="text-muted">VARK Scores:</small>
                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    <span class="badge bg-info">Visual: <?php echo htmlspecialchars($studentStats['visual_score'] ?? 'N/A'); ?></span>
                                                    <span class="badge bg-warning">Auditory: <?php echo htmlspecialchars($studentStats['auditory_score'] ?? 'N/A'); ?></span>
                                                </div>
                                                <div class="col-6">
                                                    <span class="badge bg-success">Reading: <?php echo htmlspecialchars($studentStats['reading_score'] ?? 'N/A'); ?></span>
                                                    <span class="badge bg-danger">Kinesthetic: <?php echo htmlspecialchars($studentStats['kinesthetic_score'] ?? 'N/A'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <h6 class="text-success">Study Tips for You:</h6>
                                        <ul class="small">
                                            <?php foreach (array_slice($learningTips['study_tips'], 0, 3) as $tip): ?>
                                                <li><?php echo htmlspecialchars($tip); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="<?php echo htmlspecialchars($learningTips['icon']); ?> fa-4x text-primary opacity-50 mb-3"></i>
                                <br>
                                <a href="/vark-assessment" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Retake Assessment
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <i class="fas fa-brain fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Learning Style Not Assessed</h6>
                            <p class="text-muted">Take the VARK assessment to discover your learning style preferences and get personalized study recommendations.</p>
                            <a href="/vark-assessment" class="btn btn-primary">
                                <i class="fas fa-brain me-1"></i>
                                Take VARK Assessment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif ($user['role'] === 'guru'): ?>
        <!-- Teacher Stats -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    My Assignments
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo htmlspecialchars($counts['my_assignments'] ?? 0); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tasks fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    My Materials
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo htmlspecialchars($counts['my_materials'] ?? 0); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-book fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Students
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo htmlspecialchars($counts['total_students'] ?? 0); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: // Admin ?>
        <!-- Admin Stats -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Users
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo htmlspecialchars($counts['total_users'] ?? 0); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Assignments
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo htmlspecialchars($counts['total_assignments'] ?? 0); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tasks fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Materials
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo htmlspecialchars($counts['total_materials'] ?? 0); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-book fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if ($user['role'] === 'siswa'): ?>
                            <div class="col-md-3 mb-2">
                                <a href="/assignments" class="btn btn-primary w-100">
                                    <i class="fas fa-tasks me-2"></i>
                                    View Assignments
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/quizzes" class="btn btn-success w-100">
                                    <i class="fas fa-question-circle me-2"></i>
                                    Take Quiz
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/questionnaires" class="btn btn-info w-100">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    Questionnaires
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/materials" class="btn btn-warning w-100">
                                    <i class="fas fa-book me-2"></i>
                                    Study Materials
                                </a>
                            </div>
                        <?php elseif ($user['role'] === 'guru'): ?>
                            <div class="col-md-3 mb-2">
                                <a href="/assignments/create" class="btn btn-primary w-100">
                                    <i class="fas fa-plus me-2"></i>
                                    Create Assignment
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/quizzes/create" class="btn btn-success w-100">
                                    <i class="fas fa-plus me-2"></i>
                                    Create Quiz
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/materials/create" class="btn btn-info w-100">
                                    <i class="fas fa-upload me-2"></i>
                                    Upload Material
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/students" class="btn btn-warning w-100">
                                    <i class="fas fa-users me-2"></i>
                                    Manage Students
                                </a>
                            </div>
                        <?php else: // Admin ?>
                            <div class="col-md-3 mb-2">
                                <a href="/users" class="btn btn-primary w-100">
                                    <i class="fas fa-users-cog me-2"></i>
                                    Manage Users
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/reports" class="btn btn-success w-100">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    View Reports
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/settings" class="btn btn-info w-100">
                                    <i class="fas fa-cog me-2"></i>
                                    System Settings
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/backup" class="btn btn-warning w-100">
                                    <i class="fas fa-database me-2"></i>
                                    Backup Data
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <!-- AI Simulation Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-robot me-2"></i>
                        AI Performance Simulation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                                <h6>Reinforcement Learning</h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-primary" style="width: <?php echo htmlspecialchars($aiMetrics['rl']['accuracy']); ?>%"></div>
                                </div>
                                <small class="text-muted">Accuracy: <?php echo htmlspecialchars($aiMetrics['rl']['accuracy']); ?>%</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-filter fa-3x text-success mb-3"></i>
                                <h6>Content-Based Filtering</h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" style="width: <?php echo htmlspecialchars($aiMetrics['cbf']['accuracy']); ?>%"></div>
                                </div>
                                <small class="text-muted">Accuracy: <?php echo htmlspecialchars($aiMetrics['cbf']['accuracy']); ?>%</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-language fa-3x text-info mb-3"></i>
                                <h6>Natural Language Processing</h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-info" style="width: <?php echo htmlspecialchars($aiMetrics['nlp']['accuracy']); ?>%"></div>
                                </div>
                                <small class="text-muted">Accuracy: <?php echo htmlspecialchars($aiMetrics['nlp']['accuracy']); ?>%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>