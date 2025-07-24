<div class="sidebar h-100">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dasbor
                </a>
            </li>
            
            <?php if ($user['role'] === 'siswa'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user-circle me-2"></i>
                        Profil Saya
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="assignments.php">
                        <i class="fas fa-tasks me-2"></i>
                        Tugas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="quiz.php">
                        <i class="fas fa-question-circle me-2"></i>
                        Kuis
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="questionnaire-progress.php">
                        <i class="fas fa-chart-line me-2"></i>
                        Progress Kuesioner
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="questionnaire.php">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Kuesioner
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vark-assessment.php">
                        <i class="fas fa-brain me-2"></i>
                        Gaya Belajar VARK
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vark-correlation-analysis.php">
                        <i class="fas fa-chart-network me-2"></i>
                        Analisis Korelasi VARK
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="materials.php">
                        <i class="fas fa-book me-2"></i>
                        Materi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="progress.php">
                        <i class="fas fa-chart-line me-2"></i>
                        Kemajuan
                    </a>
                </li>
                
            <?php elseif ($user['role'] === 'guru'): ?>
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Alat Mengajar</span>
                </h6>
                <li class="nav-item">
                    <a class="nav-link" href="assignments.php">
                        <i class="fas fa-tasks me-2"></i>
                        Tugas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="quiz.php">
                        <i class="fas fa-question-circle me-2"></i>
                        Kuis
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="materials.php">
                        <i class="fas fa-book me-2"></i>
                        Materi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="students.php">
                        <i class="fas fa-users me-2"></i>
                        Siswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="grading.php">
                        <i class="fas fa-star me-2"></i>
                        Penilaian
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="teacher-evaluation-monitoring.php">
                        <i class="fas fa-chart-line me-2"></i>
                        Monitoring Evaluasi
                    </a>
                </li>
                
            <?php else: // Admin ?>
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Administrasi</span>
                </h6>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users-cog me-2"></i>
                        Pengguna
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar me-2"></i>
                        Laporan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="system.php">
                        <i class="fas fa-server me-2"></i>
                        Sistem
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="backup.php">
                        <i class="fas fa-database me-2"></i>
                        Cadangan
                    </a>
                </li>
            <?php endif; ?>
            
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Fitur AI</span>
            </h6>
            <li class="nav-item">
                <a class="nav-link" href="ai-explanation.php">
                    <i class="fas fa-graduation-cap me-2"></i>
                    Cara Kerja AI
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="ai-recommendations.php">
                    <i class="fas fa-robot me-2"></i>
                    Rekomendasi AI
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="nlp-demo.php">
                    <i class="fas fa-brain me-2"></i>
                    Demo NLP Analysis
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="analytics.php">
                    <i class="fas fa-chart-line me-2"></i>
                    Analitik Pembelajaran
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Support</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="help.php">
                    <i class="fas fa-question-circle me-2"></i>
                    Help
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user me-2"></i>
                    Profile
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.sidebar {
    position: fixed;
    top: 56px; /* Height of navbar */
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar-heading {
    font-size: .75rem;
    text-transform: uppercase;
}

.sidebar .nav-link {
    font-weight: 500;
    color: #333;
}

.sidebar .nav-link.active {
    color: #007bff;
}

.sidebar .nav-link:hover {
    color: #007bff;
}

@media (max-width: 767.98px) {
    .sidebar {
        top: 0;
    }
}
</style>
