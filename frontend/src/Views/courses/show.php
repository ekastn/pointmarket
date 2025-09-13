<?php /** @var array $course */ ?>
<div class="container-fluid">
  <?php 
    $renderer->includePartial('components/partials/page_title', [
      'icon' => 'fas fa-book-open',
      'title' => htmlspecialchars($course['title'] ?? 'Course'),
      'right' => ''
    ]);
  ?>

  <div class="row">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-2">Deskripsi</h5>
          <p class="card-text"><?= htmlspecialchars($course['description'] ?? '-') ?></p>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-2">Info</h5>
          <dl class="row mb-0 small">
            <dt class="col-sm-3">Slug</dt><dd class="col-sm-9"><?= htmlspecialchars($course['slug'] ?? '') ?></dd>
            <dt class="col-sm-3">Owner ID</dt><dd class="col-sm-9"><?= htmlspecialchars((string)($course['owner_id'] ?? '')) ?></dd>
            <dt class="col-sm-3">Dibuat</dt><dd class="col-sm-9"><?= isset($course['created_at']) ? date('d-m-Y H:i', strtotime($course['created_at'])) : '-' ?></dd>
            <dt class="col-sm-3">Diubah</dt><dd class="col-sm-9"><?= isset($course['updated_at']) ? date('d-m-Y H:i', strtotime($course['updated_at'])) : '-' ?></dd>
          </dl>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Tindakan</h5>
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary btn-enroll" data-course-id="<?= (int)($course['id'] ?? 0) ?>">Daftar</button>
            <button type="button" class="btn btn-outline-danger btn-unenroll" data-course-id="<?= (int)($course['id'] ?? 0) ?>">Batalkan</button>
          </div>
          <small class="text-muted d-block mt-2">Catatan: tombol akan memberi tahu jika sudah terdaftar.</small>
        </div>
      </div>
    </div>
  </div>
</div>

