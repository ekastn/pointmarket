<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = $path ?? '';
$label = $label ?? '';
$icon = $icon ?? '';
// Active if exact match or current is under the path
$isActive = ($currentPath === $path) || (strlen($path) > 1 && str_starts_with($currentPath, rtrim($path, '/').'/'));
?>

<div class="d-flex align-items-center justify-content-between">
    <a href="<?= $path; ?>" class="<?= $isActive ? 'active' : ''; ?> nav-link d-flex align-items-center text-dark text-decoration-none px-3 py-2 rounded flex-grow-1 sidebar-nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="<?= htmlspecialchars($label); ?>">
        <i class="<?= $icon; ?> text-center" style="width: 1.25rem; margin-right: 0.75rem;"></i>
        <span class="fw-medium"><?= htmlspecialchars($label); ?></span>
    </a>
    
</div>
