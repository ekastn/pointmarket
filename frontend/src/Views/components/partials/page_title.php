<?php
// Props: $icon (string), $title (string), optional $right (HTML)
$icon = $icon ?? 'fas fa-circle';
$title = $title ?? '';
$right = $right ?? '';
?>
<div class="pm-page-title pm-card" role="region" aria-label="Judul Halaman">
  <div class="pm-title-left">
    <i class="<?= htmlspecialchars($icon); ?>" aria-hidden="true"></i>
    <h1 class="h2 m-0"><?= htmlspecialchars($title); ?></h1>
  </div>
  <div class="pm-title-right">
    <div class="pm-title-actions">
      <?= $right; ?>
    </div>
  </div>
</div>
