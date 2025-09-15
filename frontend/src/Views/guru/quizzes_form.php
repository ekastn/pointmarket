<?php
$quiz = $data['quiz'] ?? null;
$title = $quiz ? 'Edit Kuis' : 'Buat Kuis';
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-question-circle',
  'title' => $title,
]);
?>
<?php if (!empty($data['error'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($data['error']) ?></div>
<?php endif; ?>
<form action="<?= $quiz ? '/guru/quizzes/' . (int)$quiz['id'] : '/guru/quizzes' ?>" method="post" class="card p-3">
  <div class="mb-2">
    <label class="form-label">Judul</label>
    <input class="form-control" name="title" value="<?= htmlspecialchars($quiz['title'] ?? '') ?>" required>
  </div>
  <div class="mb-2">
    <label class="form-label">Course ID</label>
    <input class="form-control" name="course_id" type="number" value="<?= htmlspecialchars($quiz['course_id'] ?? '') ?>" required>
  </div>
  <div class="mb-2">
    <label class="form-label">Deskripsi</label>
    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($quiz['description'] ?? '') ?></textarea>
  </div>
  <div class="row">
    <div class="col-md-4 mb-2">
      <label class="form-label">Poin</label>
      <input class="form-control" name="reward_points" type="number" min="0" value="<?= htmlspecialchars($quiz['reward_points'] ?? 0) ?>" required>
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label">Durasi (menit)</label>
      <input class="form-control" name="duration_minutes" type="number" min="0" value="<?= htmlspecialchars($quiz['duration_minutes'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label">Status</label>
      <select class="form-select" name="status">
        <?php $st = $quiz['status'] ?? 'draft'; ?>
        <option value="draft" <?= $st==='draft'?'selected':'' ?>>draft</option>
        <option value="published" <?= $st==='published'?'selected':'' ?>>published</option>
        <option value="archived" <?= $st==='archived'?'selected':'' ?>>archived</option>
      </select>
    </div>
  </div>
  <div class="d-flex justify-content-between mt-3">
    <a href="/guru/quizzes" class="btn btn-secondary">Kembali</a>
    <button class="btn btn-primary">Simpan</button>
  </div>
</form>
