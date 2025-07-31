<div class="sidebar h-100">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="/dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dasbor
                </a>
            </li>
            
            <?php if (isset($user) && $user['role'] === 'siswa'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/assignments">
                        <i class="fas fa-tasks me-2"></i>
                        Tugas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/quiz">
                        <i class="fas fa-question-circle me-2"></i>
                        Kuis
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/questionnaire">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Kuesioner
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/vark-assessment">
                        <i class="fas fa-brain me-2"></i>
                        Gaya Belajar VARK
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/vark-correlation-analysis">
                        <i class="fas fa-chart-network me-2"></i>
                        Analisis Korelasi VARK
                    </a>
                </li>
                
            <?php elseif (isset($user) && $user['role'] === 'guru'): ?>
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Alat Mengajar</span>
                </h6>
                <li class="nav-item">
                    <a class="nav-link" href="/assignments">
                        <i class="fas fa-tasks me-2"></i>
                        Tugas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/teacher-evaluation-monitoring">
                        <i class="fas fa-chart-line me-2"></i>
                        Monitoring Evaluasi
                    </a>
                </li>
                
            <?php elseif (isset($user) && $user['role'] === 'admin'): ?>
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Administrasi</span>
                </h6>
                <li class="nav-item">
                    <a class="nav-link" href="/users">
                        <i class="fas fa-users-cog me-2"></i>
                        Pengguna
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/reports">
                        <i class="fas fa-chart-bar me-2"></i>
                        Laporan
                    </a>
                </li>
            <?php endif; ?>
            
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Fitur AI</span>
            </h6>
            <li class="nav-item">
                <a class="nav-link" href="/ai-explanation">
                    <i class="fas fa-graduation-cap me-2"></i>
                    Cara Kerja AI
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/ai-recommendations">
                    <i class="fas fa-robot me-2"></i>
                    Rekomendasi AI
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/nlp-demo">
                    <i class="fas fa-brain me-2"></i>
                    Demo NLP
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Support</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="/help">
                    <i class="fas fa-question-circle me-2"></i>
                    Bantuan
                </a>
            </li>
        </ul>
    </div>
</div>
