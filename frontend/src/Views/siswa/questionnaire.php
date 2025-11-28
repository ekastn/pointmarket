<?php
// Data for this view will be passed from the QuestionnaireController
$user = $user ?? ['name' => 'Guest'];
$questionnaires = $questionnaires ?? [];
$history = $history ?? [];
$stats = $stats ?? [];
$pendingEvaluations = $pendingEvaluations ?? [];
$varkResult = $varkResult ?? null;
$messages = $messages ?? [];
?>

<?php 
$right = '<div class="btn-group">'
       . '<a href="/weekly-evaluations" class="btn btn-sm btn-outline-primary"><i class="fas fa-calendar-check"></i> Evaluasi Mingguan</a>'
       . '<a href="/ai-explanation" class="btn btn-sm btn-outline-info ms-2"><i class="fas fa-robot"></i> Penjelasan AI</a>'
       . '</div>';
$renderer->includePartial('components/partials/page_title', [
    'icon' => 'fas fa-clipboard-list',
    'title' => 'Kuesioner',
    'right' => $right,
]);
?>

<!-- Statistics Overview -->
<div class="row pm-section">
  <div class="col-12">
    <div class="card pm-card">
      <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Kamu</h5></div>
      <div class="card-body">
        <div class="row g-3">
          <?php if (!empty($stats)): ?>
            <?php foreach ($stats as $stat): ?>
              <?php 
                $type = strtoupper($stat['type'] ?? '');
                $icon = 'clipboard-list';
                $accent = 'primary';
                if ($type === 'MSLQ') { $icon = 'brain'; $accent = 'info'; }
                elseif ($type === 'AMS') { $icon = 'heart'; $accent = 'danger'; }
                elseif ($type === 'VARK') { $icon = 'graduation-cap'; $accent = 'success'; }
              ?>
              <div class="col-xl-4 col-md-6">
                <div class="card h-100">
                  <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold text-<?php echo $accent; ?>">
                      <i class="fas fa-<?php echo $icon; ?> me-2"></i><?php echo $type; ?>
                    </span>
                    <span class="badge bg-light text-muted">Selesai: <?php echo (int)($stat['total_completed'] ?? 0); ?></span>
                  </div>
                  <div class="card-body">
                    <?php if ($type === 'VARK'): ?>
                      <div class="d-flex align-items-center justify-content-between">
                        <div>
                          <div class="text-muted small">Gaya Belajar</div>
                          <div class="h6 mb-0">
                            <?php if (($stat['total_completed'] ?? 0) > 0 && $varkResult): ?>
                              <?php echo htmlspecialchars($varkResult['dominant_style']); ?>
                              <small class="text-muted d-block"><?php echo htmlspecialchars($varkResult['learning_preference']); ?></small>
                            <?php else: ?>
                              <span class="text-muted">Belum dinilai</span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <i class="fas fa-<?php echo $icon; ?> fa-2x text-<?php echo $accent; ?> opacity-25"></i>
                      </div>
                    <?php else: ?>
                      <div class="text-muted small">Rata-rata Skor</div>
                      <?php 
                        $avg = $stat['average_score'] ?? null;
                        $avgText = $avg !== null ? number_format($avg, 2) : '-';
                        $scoreClass = ($avg !== null && $avg >= 5.5) ? 'score-high' : (($avg !== null && $avg >= 4) ? 'score-medium' : 'score-low');
                      ?>
                      <div class="d-flex align-items-center justify-content-between">
                        <div class="h4 mb-0 <?php echo $avg !== null ? $scoreClass : 'text-muted'; ?>"><?php echo htmlspecialchars($avgText); ?></div>
                        <i class="fas fa-<?php echo $icon; ?> fa-2x text-<?php echo $accent; ?> opacity-25"></i>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($stat['last_completed'])): ?>
                      <div class="mt-2 text-muted small">
                        <i class="fas fa-clock me-1"></i>Terakhir: <?php echo htmlspecialchars(date('d M Y', strtotime($stat['last_completed']))); ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <?php $renderer->includePartial('components/partials/empty_state', [
                  'icon' => 'fas fa-chart-bar',
                  'title' => 'Belum ada statistik kuesioner',
                  'subtitle' => 'Isi kuesioner dulu untuk lihat progress di sini.',
              ]); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $renderer->includePartial('siswa/partials/available_questionnaires', ['questionnaires' => $questionnaires]); ?>

<!-- Recent History -->
<div class="row pm-section">
    <div class="col-12">
        <div class="card pm-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Kuesioner Terakhir</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($history)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($history as $item): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold">
                                        <?php
                                        $icon = 'clipboard-list';
                                        if (($item['questionnaire_type'] ?? '') === 'MSLQ') $icon = 'brain';
                                        elseif (($item['questionnaire_type'] ?? '') === 'AMS') $icon = 'heart';
                                        elseif (($item['questionnaire_type'] ?? '') === 'VARK') $icon = 'graduation-cap';
                                        ?>
                                        <i class="fas fa-<?php echo $icon; ?> me-2 text-muted"></i>
                                        <?php echo htmlspecialchars($item['questionnaire_name']); ?>
                                    </div>
                                    <div class="text-muted small mb-1"><?php echo htmlspecialchars($item['questionnaire_description'] ?? 'Belum ada deskripsi.'); ?></div>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo htmlspecialchars(date('d M Y', strtotime($item['completed_at']))); ?>
                                        <?php if (isset($item['week_number']) && isset($item['year'])): ?>
                                            | Minggu <?php echo htmlspecialchars($item['week_number']); ?>/<?php echo htmlspecialchars($item['year']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <?php if (($item['questionnaire_type'] ?? '') !== 'VARK'): ?>
                                        <?php 
                                        $score = $item['total_score'] ?? 0;
                                        $scoreClass = $score >= 5.5 ? 'score-high' : ($score >= 4 ? 'score-medium' : 'score-low');
                                        ?>
                                        <span class="score-badge <?php echo $scoreClass; ?>">
                                          Skor: <?php echo htmlspecialchars(number_format($score ?? 0, 2)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <?php $renderer->includePartial('components/partials/empty_state', [
                        'icon' => 'fas fa-clipboard-list',
                        'title' => 'Belum ada riwayat kuesioner',
                        'subtitle' => 'Selesaikan kuesioner pertama kamu untuk melihat progress.',
                        'cta_path' => '/weekly-evaluations',
                        'cta_label' => 'Mulai Evaluasi Mingguan',
                    ]); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
