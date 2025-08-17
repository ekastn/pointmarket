<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = $path ?? '';
$label = $label ?? '';
$icon = $icon ?? '';
?>

<div class="d-flex align-items-center justify-content-between">
    <a href="<?= $path; ?>" class="<?= $currentPath === $path ? 'active' : ''; ?> nav-link d-flex align-items-center text-dark text-decoration-none px-3 py-2 rounded flex-grow-1 sidebar-nav-link">
        <i class="<?= $icon; ?> text-center" style="width: 1.25rem; margin-right: 0.75rem;"></i>
        <span class="fw-medium"><?= $label; ?></span>
    </a>
</div>
