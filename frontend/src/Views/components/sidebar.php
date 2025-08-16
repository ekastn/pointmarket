<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$user = $_SESSION['user_data'] ?? null;

$studentsMenu = [
    ['path' => '/my-missions', 'label' => 'Misi Saya', 'icon' => 'fas fa-trophy'],
    ['path' => '/my-courses', 'label' => 'Kursus Saya', 'icon' => 'fas fa-book'],
    ['path' => '/my-badges', 'label' => 'Lencana Saya', 'icon' => 'fas fa-id-badge'],
    ['path' => '/products', 'label' => 'Marketplace', 'icon' => 'fas fa-store'],
    ['path' => '/assignments', 'label' => 'Tugas', 'icon' => 'fas fa-tasks'],
    ['path' => '/quiz', 'label' => 'Kuis', 'icon' => 'fas fa-question-circle'],
    ['path' => '/questionnaire', 'label' => 'Kuesioner', 'icon' => 'fas fa-clipboard-list'],
    ['path' => '/vark-correlation-analysis', 'label' => 'Analisis Korelasi VARK', 'icon' => 'fas fa-chart-pie'],
];

$teachersMenu = [
    ['path' => '/missions', 'label' => 'Misi', 'icon' => 'fas fa-trophy'],
    ['path' => '/courses', 'label' => 'Kursus', 'icon' => 'fas fa-book-open'],
    ['path' => '/assignments', 'label' => 'Tugas', 'icon' => 'fas fa-tasks'],
    ['path' => '/teacher-evaluation-monitoring', 'label' => 'Monitoring Evaluasi', 'icon' => 'fas fa-chart-line'],
];

$adminsMenu = [
    ['path' => '/users', 'label' => 'Pengguna', 'icon' => 'fas fa-users-cog'],
    ['path' => '/missions', 'label' => 'Misi', 'icon' => 'fas fa-trophy'],
    ['path' => '/courses', 'label' => 'Kursus', 'icon' => 'fas fa-book-open'],
    ['path' => '/badges', 'label' => 'Lencana', 'icon' => 'fas fa-id-badge'],
    ['path' => '/products', 'label' => 'Kelola Produk', 'icon' => 'fas fa-box-open'],
    ['path' => '/product-categories', 'label' => 'Kelola Kategori Produk', 'icon' => 'fas fa-tags'],
    ['path' => '/reports', 'label' => 'Laporan', 'icon' => 'fas fa-chart-bar'],
];

$aiMenu = [
    ['path' => '/ai-explanation', 'label' => 'Cara Kerja AI', 'icon' => 'fas fa-graduation-cap'],
    ['path' => '/ai-recommendations', 'label' => 'Rekomendasi AI', 'icon' => 'fas fa-robot'],
    ['path' => '/nlp-demo', 'label' => 'Demo NLP', 'icon' => 'fas fa-brain'],
];
?>

<div class="sidebar h-100">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <?php $renderer->includePartial('components/partials/sidebar_link', ['path' => '/dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt']); ?>
            </li>
            <br>

            <?php if (isset($user) && $user['role'] === 'siswa'): ?>
                <li class="nav-item">
                    <?php foreach ($studentsMenu as $menu): ?>
                        <?php $renderer->includePartial('components/partials/sidebar_link', $menu); ?>
                    <?php endforeach; ?>
                </li>
            <?php elseif (isset($user) && $user['role'] === 'guru'): ?>
                <li class="nav-item">
                    <?php foreach ($teachersMenu as $menu): ?>
                        <?php $renderer->includePartial('components/partials/sidebar_link', $menu); ?>
                    <?php endforeach; ?>
                </li>
            <?php elseif (isset($user) && $user['role'] === 'admin'): ?>
                <li class="nav-item">
                    <?php foreach ($adminsMenu as $menu): ?>
                        <?php $renderer->includePartial('components/partials/sidebar_link', $menu); ?>
                    <?php endforeach; ?>
                </li
            <?php endif; ?>
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Fitur AI</span>
            </h6>
            <li class="nav-item">
                <?php foreach ($aiMenu as $menu): ?>
                    <?php $renderer->includePartial('components/partials/sidebar_link', $menu); ?>
                <?php endforeach; ?>
            </li>
        </ul>
        
        <ul class="nav flex-column mb-2">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Support</span>
            </h6>
            <li class="nav-item">
                <?php $renderer->includePartial('components/partials/sidebar_link', ['path' => '/help', 'label' => 'Bantuan', 'icon' => 'fas fa-info-circle']); ?>
            </li>
        </ul>
    </div>
</div>

