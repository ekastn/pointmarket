<?php /** @var array $course */ ?>
<div class="col-lg-4 col-md-6 mb-4">
  <div class="card card-hover h-100">
    <img src="/public/images/product_placeholder.png" class="card-img-top" alt="<?= htmlspecialchars($course['title']) ?>">
    <div class="card-body d-flex flex-column">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <h5 class="card-title mb-0"><?= htmlspecialchars($course['title']) ?></h5>
      </div>
      <p class="card-text text-muted small mb-3"><?= htmlspecialchars($course['description'] ?? '') ?></p>
      <div class="mt-auto">
        <a href="/courses/<?= htmlspecialchars($course['slug']) ?>" class="btn btn-primary w-100">Lihat kelas</a>
      </div>
    </div>
  </div>
  </div>

