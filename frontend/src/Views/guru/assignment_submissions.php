<?php
$renderer->includePartial('components/partials/page_title', [
  'icon' => 'fas fa-list',
  'title' => 'Submissions Tugas',
  'right' => '<a href="/guru/assignments" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>'
]);

$assignmentId = (int)($data['assignment_id'] ?? 0);
$submissions = $data['submissions'] ?? [];
?>

<div class="card">
  <div class="card-body">
    <?php if (empty($submissions)): ?>
      <?php $renderer->includePartial('components/partials/empty_state', [
        'icon' => 'fas fa-inbox',
        'title' => 'Belum ada submission',
        'subtitle' => 'Submission siswa akan muncul di sini.'
      ]); ?>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Siswa</th>
              <th>Status</th>
              <th>Skor</th>
              <th>Submitted</th>
              <th>Jawaban</th>
              <th>Lampiran</th>
              <th>Feedback</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($submissions as $i => $s): ?>
              <tr>
                <td><?php echo $i+1; ?></td>
                <td>
                  <div class="fw-semibold"><?php echo htmlspecialchars($s['student_name'] ?? ($s['student_id'] ?? '')); ?></div>
                  <div class="text-muted small"><?php echo htmlspecialchars($s['student_email'] ?? ''); ?></div>
                </td>
                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($s['status'] ?? ''); ?></span></td>
                <td><?php echo isset($s['score']) ? htmlspecialchars((string)$s['score']) : '-'; ?></td>
                <td><?php echo !empty($s['submitted_at']) ? htmlspecialchars(date('Y-m-d H:i', strtotime($s['submitted_at']))) : '-'; ?></td>
                <td class="small" style="max-width:260px;">
                  <?php $submissionText = trim((string)($s['submission'] ?? '')); ?>
                  <?php if ($submissionText !== ''): ?>
                    <details>
                      <summary class="text-primary" style="cursor:pointer;">Lihat</summary>
                      <pre class="mb-0" style="white-space:pre-wrap;word-break:break-word;"><?php echo htmlspecialchars($submissionText); ?></pre>
                    </details>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="small" style="max-width:220px;">
                  <?php
                    $atts = $s['attachments'] ?? null;
                    // Normalize attachments into an array of displayable links
                    $links = [];
                    if (is_array($atts)) {
                      foreach ($atts as $att) {
                        if (is_string($att)) {
                          $links[] = ['name' => basename(parse_url($att, PHP_URL_PATH) ?: ''), 'url' => $att];
                        } elseif (is_array($att)) {
                          $name = isset($att['name']) ? (string)$att['name'] : (basename(parse_url((string)($att['url'] ?? ''), PHP_URL_PATH) ?: '') ?: 'lampiran');
                          $url  = isset($att['url']) ? (string)$att['url'] : '';
                          if ($url !== '') { $links[] = ['name' => $name, 'url' => $url]; }
                        }
                      }
                    }
                  ?>
                  <?php if (!empty($links)): ?>
                    <div class="d-flex flex-wrap gap-1">
                      <?php foreach ($links as $link): ?>
                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener" class="badge bg-light text-primary border">
                          <i class="fas fa-paperclip me-1"></i><?php echo htmlspecialchars($link['name']); ?>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="small"><?php echo htmlspecialchars($s['feedback'] ?? ''); ?></td>
                <td>
                  <form method="POST" action="/guru/assignments/<?php echo $assignmentId; ?>/submissions/<?php echo (int)($s['id'] ?? 0); ?>/grade" class="d-flex gap-2">
                    <input type="number" step="0.01" name="score" class="form-control form-control-sm" placeholder="Skor" value="<?php echo isset($s['score']) ? htmlspecialchars((string)$s['score']) : ''; ?>" style="width:100px" />
                    <input type="text" name="feedback" class="form-control form-control-sm" placeholder="Feedback" value="<?php echo htmlspecialchars($s['feedback'] ?? ''); ?>" style="width:220px" />
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <div class="card-footer text-muted small">
    Gunakan formulir untuk menilai dan memberi feedback. Tanggal penilaian dan pengajar tercatat otomatis.
  </div>
  </div>
<?php // end file: keep single rendering block only ?>
