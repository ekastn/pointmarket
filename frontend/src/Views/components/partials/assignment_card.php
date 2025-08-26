<?php /** @var array $assignment */ ?>
<div class="col-md-6 col-lg-4 mb-4">
  <div class="card assignment-card <?= htmlspecialchars($assignment['student_status']); ?> <?= htmlspecialchars($assignment['urgency_status']); ?> h-100">
    <div class="card-body position-relative">
      <?php if ($assignment['urgency_status'] !== 'normal'): ?>
        <div class="urgency-indicator <?= htmlspecialchars($assignment['urgency_status']); ?>"></div>
      <?php endif; ?>

      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="badge status-badge <?= htmlspecialchars($assignment['student_status']); ?> text-white">
          <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $assignment['student_status']))); ?>
        </span>
        <small class="text-muted"><?= htmlspecialchars($assignment['subject']); ?></small>
      </div>

      <h5 class="card-title"><?= htmlspecialchars($assignment['title']); ?></h5>

      <p class="card-text text-muted">
        <?php 
          $description = htmlspecialchars($assignment['description']);
          echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
        ?>
      </p>

      <div class="row text-center mb-3">
        <div class="col-6">
          <small class="text-muted">Poin</small>
          <div class="fw-bold text-primary"><?= htmlspecialchars($assignment['points']); ?></div>
        </div>
        <div class="col-6">
          <small class="text-muted">Sisa Hari</small>
          <div class="fw-bold <?= $assignment['days_remaining'] < 0 ? 'text-danger' : ($assignment['days_remaining'] <= 2 ? 'text-warning' : 'text-success'); ?>">
            <?php 
              if ($assignment['days_remaining'] < 0) {
                echo abs($assignment['days_remaining']) . ' terlambat';
              } else {
                echo htmlspecialchars($assignment['days_remaining']);
              }
            ?>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <small class="text-muted">
          <i class="fas fa-calendar me-1"></i>
          Tenggat: <?= htmlspecialchars(date('d M Y', strtotime($assignment['due_date']))); ?>
        </small>
        <br>
        <small class="text-muted">
          <i class="fas fa-user me-1"></i>
          Guru: <?= htmlspecialchars($assignment['teacher_name']); ?>
        </small>
      </div>

      <?php if ($assignment['student_status'] === 'completed' && $assignment['score'] !== null): ?>
        <div class="alert alert-success py-2">
          <strong><i class="fas fa-star me-1"></i>Score: <?= htmlspecialchars(number_format($assignment['score'], 1)); ?></strong>
          <small class="text-muted d-block mt-1">
            <i class="fas fa-robot me-1"></i>AI-Simulated Score (Demo) |
            Submitted: <?= htmlspecialchars(date('d M Y', strtotime($assignment['submitted_at']))); ?>
          </small>
          <div class="mt-2">
            <small class="text-info">
              <i class="fas fa-lightbulb me-1"></i>
              <strong>Planned AI Features:</strong> Real NLP analysis will provide detailed feedback on grammar, structure, and content relevance.
            </small>
          </div>
        </div>
      <?php endif; ?>

      <div class="d-flex gap-2">
        <?php if ($assignment['student_status'] === 'not_started'): ?>
          <button class="btn btn-primary btn-sm flex-fill" onclick="startAssignment(<?= htmlspecialchars($assignment['id']); ?>)">
            <i class="fas fa-play me-1"></i> Mulai
          </button>
        <?php elseif ($assignment['student_status'] === 'in_progress'): ?>
          <button class="btn btn-success btn-sm flex-fill" onclick="submitAssignment(<?= htmlspecialchars($assignment['id']); ?>, '<?= htmlspecialchars($assignment['title']); ?>')">
            <i class="fas fa-upload me-1"></i> Submit
          </button>
        <?php else: ?>
          <button class="btn btn-outline-success btn-sm flex-fill" disabled>
            <i class="fas fa-check me-1"></i> Selesai
          </button>
        <?php endif; ?>

        <button class="btn btn-outline-info btn-sm" onclick="viewAssignmentDetails(<?= htmlspecialchars($assignment['id']); ?>)">
          <i class="fas fa-eye"></i>
        </button>
      </div>
    </div>
  </div>
</div>

