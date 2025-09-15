<?php
$assignment = $data['assignment'] ?? null;
$title = $assignment ? 'Edit Tugas' : 'Buat Tugas';
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-tasks',
  'title' => $title,
]);
?>
<?php if (!empty($data['error'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($data['error']) ?></div>
<?php endif; ?>
<form action="<?= $assignment ? '/guru/assignments/' . (int)$assignment['id'] : '/guru/assignments' ?>" method="post" class="card p-3">
  <div class="mb-2">
    <label class="form-label">Judul</label>
    <input class="form-control" name="title" value="<?= htmlspecialchars($assignment['title'] ?? '') ?>" required>
  </div>
  <div class="mb-2">
    <label class="form-label">Course ID</label>
    <input class="form-control" name="course_id" type="number" value="<?= htmlspecialchars($assignment['course_id'] ?? '') ?>" required>
  </div>
  <div class="mb-2">
    <label class="form-label">Deskripsi</label>
    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($assignment['description'] ?? '') ?></textarea>
  </div>
  <div class="row">
    <div class="col-md-4 mb-2">
      <label class="form-label">Poin</label>
      <input class="form-control" name="reward_points" type="number" min="0" value="<?= htmlspecialchars($assignment['reward_points'] ?? 0) ?>" required>
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label">Due Date</label>
      <input class="form-control" name="due_date" type="datetime-local" value="<?= isset($assignment['due_date']) ? date('Y-m-d\TH:i', strtotime($assignment['due_date'])) : '' ?>">
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label">Status</label>
      <select class="form-select" name="status">
        <?php $st = $assignment['status'] ?? 'draft'; ?>
        <option value="draft" <?= $st==='draft'?'selected':'' ?>>draft</option>
        <option value="published" <?= $st==='published'?'selected':'' ?>>published</option>
        <option value="archived" <?= $st==='archived'?'selected':'' ?>>archived</option>
      </select>
    </div>
  </div>
  <div class="d-flex justify-content-between mt-3">
    <a href="/guru/assignments" class="btn btn-secondary">Kembali</a>
    <button class="btn btn-primary">Simpan</button>
  </div>
</form>
