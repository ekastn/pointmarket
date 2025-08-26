<?php /** @var array $quiz */ ?>
<div class="col-md-6 col-lg-4 mb-4">
  <div class="card quiz-card h-100">
    <div class="card-body position-relative">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="badge status-badge bg-info text-white">
          <?= htmlspecialchars($quiz['status']); ?>
        </span>
        <small class="text-muted"><?= htmlspecialchars($quiz['subject']); ?></small>
      </div>

      <h5 class="card-title"><?= htmlspecialchars($quiz['title']); ?></h5>

      <p class="card-text text-muted">
        <?php 
          $description = htmlspecialchars($quiz['description'] ?? 'N/A');
          echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
        ?>
      </p>

      <div class="row text-center mb-3">
        <div class="col-6">
          <small class="text-muted">Poin</small>
          <div class="fw-bold text-primary"><?= htmlspecialchars($quiz['points']); ?></div>
        </div>
        <div class="col-6">
          <small class="text-muted">Durasi</small>
          <div class="fw-bold text-info">
            <?= $quiz['duration'] ? htmlspecialchars($quiz['duration']) . ' menit' : 'N/A'; ?>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <small class="text-muted">
          <i class="fas fa-calendar me-1"></i>
          Tenggat: <?= htmlspecialchars($quiz['due_date'] ? date('d M Y', strtotime($quiz['due_date'])) : 'N/A'); ?>
        </small>
        <br>
        <small class="text-muted">
          <i class="fas fa-user me-1"></i>
          ID Guru: <?= htmlspecialchars($quiz['teacher_id']); ?>
        </small>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm flex-fill" onclick="startQuiz(<?= htmlspecialchars($quiz['id']); ?>)">
          <i class="fas fa-play me-1"></i> Mulai Kuis
        </button>
        <button class="btn btn-outline-info btn-sm" onclick="viewQuizDetails(<?= htmlspecialchars($quiz['id']); ?>)">
          <i class="fas fa-eye"></i>
        </button>
      </div>
    </div>
  </div>
</div>

