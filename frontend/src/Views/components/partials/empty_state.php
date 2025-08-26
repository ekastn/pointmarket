<?php
// Props: $icon, $title, $subtitle, optional $cta_path, $cta_label
$icon = $icon ?? 'fas fa-info-circle';
$title = $title ?? '';
$subtitle = $subtitle ?? '';
$cta_path = $cta_path ?? null;
$cta_label = $cta_label ?? null;
?>
<div class="pm-empty">
  <div class="pm-empty-icon">
    <i class="<?= htmlspecialchars($icon); ?> fa-3x"></i>
  </div>
  <?php if ($title): ?><div class="pm-empty-title"><?= htmlspecialchars($title); ?></div><?php endif; ?>
  <?php if ($subtitle): ?><div class="pm-empty-subtitle"><?= htmlspecialchars($subtitle); ?></div><?php endif; ?>
  <?php if ($cta_path && $cta_label): ?>
    <div class="mt-3">
      <a href="<?= htmlspecialchars($cta_path); ?>" class="btn btn-primary btn-sm">
        <?= htmlspecialchars($cta_label); ?>
      </a>
    </div>
  <?php endif; ?>
</div>

