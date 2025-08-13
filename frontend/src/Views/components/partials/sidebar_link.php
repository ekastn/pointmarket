<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = $path ?? '';
$label = $label ?? '';
$icon = $icon ?? '';
?>

<a class="nav-link <?php echo ($currentPath === $path) ? 'active' : ''; ?>" href="<?php echo $path; ?>">
    <i class="<?php echo $icon; ?> me-2"></i>
    <?php echo $label; ?>
</a>

