<?php
session_start();
$quizzes = $data['quizzes'] ?? [];
$user = $data['user'] ?? null;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-question-circle me-2"></i>Kuis Saya</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/weekly-evaluations" class="btn btn-outline-primary">
                <i class="fas fa-calendar-check"></i> Evaluasi Mingguan
            </a>
            <a href="/progress" class="btn btn-outline-info">
                <i class="fas fa-chart-line"></i> Progress Saya
            </a>
        </div>
    </div>
</div>

<!-- Quizzes List -->
<div class="row">
    <div class="col-12">
        <h4><i class="fas fa-list me-2"></i>Daftar Kuis 
            <small class="text-muted">(<?php echo count($quizzes); ?> kuis)</small>
        </h4>
    </div>
    
    <?php if (!empty($quizzes)): ?>
        <?php foreach ($quizzes as $quiz): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card quiz-card h-100">
                <div class="card-body position-relative">
                    
                    <!-- Status Badge -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge status-badge bg-info text-white">
                            <?php echo htmlspecialchars($quiz['status']); ?>
                        </span>
                        <small class="text-muted"><?php echo htmlspecialchars($quiz['subject']); ?></small>
                    </div>

                    <!-- Quiz Title -->
                    <h5 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                    
                    <!-- Description -->
                    <p class="card-text text-muted">
                        <?php 
                        $description = htmlspecialchars($quiz['description'] ?? 'N/A');
                        echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                        ?>
                    </p>

                    <!-- Quiz Details -->
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <small class="text-muted">Poin</small>
                            <div class="fw-bold text-primary"><?php echo $quiz['points']; ?></div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Durasi</small>
                            <div class="fw-bold text-info">
                                <?php echo $quiz['duration'] ? htmlspecialchars($quiz['duration']) . ' menit' : 'N/A'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Due Date -->
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Tenggat: <?php echo htmlspecialchars($quiz['due_date'] ? date('d M Y', strtotime($quiz['due_date'])) : 'N/A'); ?>
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-user me-1"></i>
                            ID Guru: <?php echo htmlspecialchars($quiz['teacher_id']); ?>
                        </small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" onclick="startQuiz(<?php echo $quiz['id']; ?>)">
                            <i class="fas fa-play me-1"></i> Mulai Kuis
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="viewQuizDetails(<?php echo $quiz['id']; ?>)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada kuis</h5>
                <p class="text-muted">Kuis baru akan muncul di sini saat guru membuatnya.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
