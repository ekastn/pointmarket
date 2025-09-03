<?php 
/** @var array $assignment */ 
?>
<div class="col-md-6 col-lg-4 mb-4">
  <div class="card assignment-card h-100">
    <div class="card-body position-relative">
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
          <div class="fw-bold text-primary"><?= htmlspecialchars($assignment['reward_points']); ?></div>
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

      <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm flex-fill" onclick="startAssignment(<?= htmlspecialchars($assignment['id']); ?>)">
          <i class="fas fa-play me-1"></i> Mulai
        </button>
        <button class="btn btn-outline-info btn-sm" onclick="viewAssignmentDetails(<?= htmlspecialchars($assignment['id']); ?>)">
          <i class="fas fa-eye"></i>
        </button>
      </div>
    </div>
  </div>
</div>

