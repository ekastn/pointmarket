<?php 
$a = $assignment['data'] ?? $assignment ?? [];
$assignId = (int)($a['id'] ?? $a['assignment_id'] ?? 0);
$title = htmlspecialchars($a['title'] ?? $a['assignment_title'] ?? '');
$desc = htmlspecialchars($a['description'] ?? $a['assignment_description'] ?? '');
$points = htmlspecialchars($a['reward_points'] ?? $a['assignment_reward_points'] ?? 0);
$due = $a['due_date'] ?? ($a['assignment_due_date'] ?? null);
$dueFmt = !empty($due) ? htmlspecialchars(date('Y-m-d H:i', strtotime($due))) : '-';
?>

<?php 
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-file-alt',
  'title' => 'Detail Tugas',
]);
?>

<style>
  .pm-meta-chip{display:inline-flex;align-items:center;gap:.4rem;background:#f8f9fa;color:#495057;border:1px solid #e9ecef;border-radius:999px;padding:.25rem .6rem;font-size:.875rem}
  .pm-actions{border-top:1px dashed #e9ecef;margin-top:1rem;padding-top:1rem}
  .pm-status.badge{ text-transform: capitalize; }
  .pm-status.status-available{background:#e7f5ff;color:#1971c2}
  .pm-status.status-in_progress{background:#fff9db;color:#a07900}
  .pm-status.status-completed{background:#e6fcf5;color:#087f5b}
  .pm-desc{white-space:pre-wrap}
</style>

<div class="container pm-section">
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h4 class="mb-1"><?php echo $title ?: 'Tanpa Judul'; ?></h4>
          <div class="d-flex gap-2">
            <span class="pm-meta-chip"><i class="fas fa-coins"></i> <strong><?php echo $points; ?></strong> poin</span>
            <span class="pm-meta-chip"><i class="fas fa-calendar"></i> <?php echo $dueFmt; ?></span>
          </div>
        </div>
        <?php 
          $statusKey = $status ?: 'available';
          $statusText = $status === 'in_progress' ? 'Sedang dikerjakan' : ($status === 'completed' ? 'Selesai' : 'Tersedia');
        ?>
        <span class="badge pm-status status-<?php echo htmlspecialchars($statusKey); ?>">
          <?php echo $statusText; ?>
        </span>
      </div>

      <?php if ($desc): ?>
        <div class="pm-desc text-muted mb-3"><?php echo $desc; ?></div>
      <?php else: ?>
        <div class="alert alert-light border mb-3"><i class="far fa-sticky-note me-1"></i> Tidak ada deskripsi.</div>
      <?php endif; ?>

      <div class="pm-actions d-flex gap-2">
        <?php if (empty($status)): ?>
          <button class="btn btn-primary" onclick="startAssignment(<?php echo $assignId; ?>)"><i class="fas fa-play me-1"></i> Mulai Tugas</button>
          <button class="btn btn-outline-success" disabled title="Mulai dulu tugasnya"><i class="fas fa-upload me-1"></i> Submit</button>
        <?php elseif ($status === 'in_progress'): ?>
          <button class="btn btn-outline-secondary" disabled title="Sudah dimulai"><i class="fas fa-check me-1"></i> Dimulai</button>
          <button class="btn btn-success" data-title="<?php echo htmlspecialchars($title, ENT_QUOTES); ?>" onclick="openSubmission(<?php echo $assignId; ?>, this.dataset.title)"><i class="fas fa-upload me-1"></i> Submit Jawaban</button>
        <?php elseif ($status === 'completed'): ?>
          <button class="btn btn-outline-secondary" disabled title="Sudah selesai"><i class="fas fa-check-double me-1"></i> Selesai</button>
          <button class="btn btn-outline-success" disabled title="Sudah di-submit"><i class="fas fa-upload me-1"></i> Submit</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php // reuse modal from assignments page when present ?>
<div class="modal fade submission-modal" id="submissionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="submissionModalTitle">
          <i class="fas fa-upload me-2"></i>Submit Tugas
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="submissionModalBody"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function startAssignment(assignmentId) {
  if (!confirm('Yakin mulai tugas ini?')) return;
  const base = (typeof API_BASE_URL !== 'undefined') ? API_BASE_URL : '';
  try {
    const response = await fetch(base + '/api/v1/assignments/' + assignmentId + '/start', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(typeof JWT_TOKEN !== 'undefined' ? { 'Authorization': 'Bearer ' + JWT_TOKEN } : {})
      },
      body: JSON.stringify({})
    });
    const data = await response.json().catch(() => ({ success: false, message: 'Invalid response' }));
    if (response.status === 409) {
      alert('Tugas ini sudah kamu mulai sebelumnya.');
      location.reload();
      return;
    }
    if (response.ok && data && (data.success === true || data.status === 'ok')) {
      alert(data.message || 'Assignment started successfully');
      location.reload();
    } else {
      alert('Error: ' + (data.message || ('HTTP ' + response.status)));
    }
  } catch (e) {
    alert('Network error saat mulai tugas. Coba lagi.');
  }
}

function openSubmission(assignmentId, assignmentTitle) {
  const titleEl = document.getElementById('submissionModalTitle');
  // Build title safely to avoid breaking template literals with backticks or special chars
  titleEl.innerHTML = '<i class="fas fa-upload me-2"></i>Submit: ';
  titleEl.appendChild(document.createTextNode(assignmentTitle || ''));
  document.getElementById('submissionModalBody').innerHTML = `
    <div class="mb-3">
      <label for="submissionText" class="form-label">Jawaban Kamu</label>
      <textarea class="form-control" id="submissionText" rows="6" placeholder="Tulis jawaban kamu di sini..." required></textarea>
      <div class="form-text">Tulis jawaban lengkap kamu. (Lampiran belum tersedia di demo)</div>
    </div>
    <div class="d-flex justify-content-end">
      <button class="btn btn-success" onclick="confirmSubmission(${assignmentId})"><i class="fas fa-upload me-1"></i> Submit Tugas</button>
    </div>
  `;
  const modal = new bootstrap.Modal(document.getElementById('submissionModal'));
  modal.show();
}

async function confirmSubmission(assignmentId) {
  const ta = document.getElementById('submissionText');
  const btns = document.querySelectorAll('#submissionModal .btn');
  const submissionText = ta ? ta.value.trim() : '';
  if (!submissionText) { alert('Isi jawaban dulu ya.'); return; }
  btns.forEach(b => b.disabled = true);
  const base = (typeof API_BASE_URL !== 'undefined') ? API_BASE_URL : '';
  try {
    const response = await fetch(base + '/api/v1/assignments/' + assignmentId + '/submit', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(typeof JWT_TOKEN !== 'undefined' ? { 'Authorization': 'Bearer ' + JWT_TOKEN } : {})
      },
      body: JSON.stringify({ submission: submissionText })
    });
    const data = await response.json().catch(() => ({ success: false, message: 'Invalid response' }));
    if (response.ok && (data.success === true || data.status === 'ok')) {
      const scoreVal = (data && data.data && data.data.score != null)
        ? data.data.score
        : (typeof data.score !== 'undefined' ? data.score : null);
      const scoreHtml = (scoreVal != null)
        ? `<p>Skor: <strong>${scoreVal}</strong></p>`
        : '';
      document.getElementById('submissionModalBody').innerHTML = `
        <div class="alert alert-success text-center">
          <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
          <h5>Tugas berhasil di-submit!</h5>
          ${scoreHtml}
          <p class="mb-0">${data.message || 'Tugas berhasil dikirim'}</p>
        </div>
        <div class="text-center mt-3">
          <button type="button" class="btn btn-primary" onclick="location.reload()">
            <i class="fas fa-sync-alt me-1"></i> Refresh Halaman
          </button>
        </div>`;
    } else {
      document.getElementById('submissionModalBody').innerHTML = `
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Error: ${data.message || ('HTTP ' + response.status)}
        </div>
        <div class="text-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>`;
    }
  } catch (e) {
    document.getElementById('submissionModalBody').innerHTML = `
      <div class="alert alert-danger">
        <i class="fas fa-plug me-2"></i>
        Koneksi bermasalah. Coba lagi.
      </div>
      <div class="text-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>`;
  } finally {
    btns.forEach(b => b.disabled = false);
  }
}
</script>