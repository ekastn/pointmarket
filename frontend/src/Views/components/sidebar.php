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
    ['path' => '/questionnaires', 'label' => 'Kuesioner', 'icon' => 'fas fa-clipboard-list'],
    ['path' => '/vark-correlation-analysis', 'label' => 'Analisis Korelasi VARK', 'icon' => 'fas fa-chart-pie'],
    ['path' => '/weekly-evaluations', 'label' => 'Evaluasi Mingguan', 'icon' => 'fas fa-calendar-check'],
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
    ['path' => '/questionnaires', 'label' => 'Kuesioner', 'icon' => 'fas fa-clipboard-list'],
];

$aiMenu = [
    ['path' => '/ai-explanation', 'label' => 'Cara Kerja AI', 'icon' => 'fas fa-graduation-cap'],
    ['path' => '/ai-recommendations', 'label' => 'Rekomendasi AI', 'icon' => 'fas fa-robot'],
    ['path' => '/nlp-demo', 'label' => 'Demo NLP', 'icon' => 'fas fa-brain'],
];
?>

<!-- Mobile menu button -->
<button id="mobile-menu-btn" class="d-lg-none position-fixed btn btn-primary shadow" style="top: 1rem; left: 1rem; z-index: 1050;">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay for mobile -->
<div id="mobile-overlay" class="d-lg-none position-fixed w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index: 1040;"></div>

<div id="sidebar" class="position-fixed position-lg-static bg-light border-end h-100 d-none d-lg-block" style="width: 16rem; z-index: 1040; transition: transform 0.3s ease-in-out;">
    <div class="d-flex flex-column h-100">
        <nav class="flex-grow-1 p-3 overflow-auto">
            <div class="nav flex-column gap-2">
                <?php $renderer->includePartial('components/partials/sidebar_link', ['path' => '/dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt']); ?>

                <hr />

                <?php if (isset($user) && $user['role'] === 'siswa'): ?>
                    <?php foreach ($studentsMenu as $menu): ?>
                        <?php $renderer->includePartial('components/partials/sidebar_link', $menu); ?>
                    <?php endforeach; ?>
                <?php elseif (isset($user) && $user['role'] === 'guru'): ?>
                    <?php foreach ($teachersMenu as $menu): ?>
                        <?php $renderer->includePartial('components/partials/sidebar_link', $menu); ?>
                    <?php endforeach; ?>
                <?php elseif (isset($user) && $user['role'] === 'admin'): ?>
                    <?php foreach ($adminsMenu as $menu): ?>
                        <?php $renderer->includePartial('components/partials/sidebar_link', $menu); ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <hr />

                <h6 class="px-3 mb-1 text-muted">
                    Fitur AI
                </h6>
                <?php foreach ($aiMenu as $menu): ?>
                    <?php $renderer->includePartial('components/partials/sidebar_link', $menu); ?>
                <?php endforeach; ?>

                <hr />

                <h6 class="px-3 mb-1 text-muted">
                    <span>Support</span>
                </h6>
                <?php $renderer->includePartial('components/partials/sidebar_link', ['path' => '/help', 'label' => 'Bantuan', 'icon' => 'fas fa-info-circle']); ?>
            </div>
        </nav>
    </div>
</div>

<style>
.sidebar-nav-link.active {
    color: #007bff;
    background-color: #e9ecef;
    border-left: 4px solid #007bff;
    padding-left: 11px;
}

.sidebar-nav-link:hover {
    color: #007bff;
    background-color: #e9ecef;
    border-left: 4px solid #007bff;
    padding-left: 11px;
}

.submenu-link:hover {
    background-color: var(--bs-light) !important;
    color: var(--bs-dark) !important;
}

.user-info:hover {
    background-color: var(--bs-light) !important;
}

.section-chevron {
    transition: transform 0.2s ease;
}

.section-chevron.rotated {
    transform: rotate(180deg);
}

@media (max-width: 991.98px) {
    #sidebar.show {
        display: block !important;
        transform: translateX(0);
    }
    
    #sidebar {
        transform: translateX(-100%);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobile-overlay');
    const menuIcon = mobileMenuBtn.querySelector('i');
    
    let isOpen = false;
    
    function toggleMobileMenu() {
        isOpen = !isOpen;
        
        if (isOpen) {
            sidebar.classList.add('show');
            sidebar.classList.remove('d-none');
            overlay.classList.remove('d-none');
            menuIcon.className = 'fas fa-times';
        } else {
            sidebar.classList.remove('show');
            sidebar.classList.add('d-none');
            overlay.classList.add('d-none');
            menuIcon.className = 'fas fa-bars';
        }
    }
    
    mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    overlay.addEventListener('click', toggleMobileMenu);
    
    // Section toggle functionality
    const sectionToggles = document.querySelectorAll('.section-toggle');
    
    sectionToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const section = this.dataset.section;
            const submenu = document.querySelector(`.submenu[data-section="${section}"]`);
            const chevron = this.querySelector('.section-chevron');
            
            if (submenu.classList.contains('d-none')) {
                submenu.classList.remove('d-none');
                chevron.classList.add('rotated');
            } else {
                submenu.classList.add('d-none');
                chevron.classList.remove('rotated');
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('show', 'd-none');
            sidebar.classList.add('d-lg-block');
            overlay.classList.add('d-none');
            menuIcon.className = 'fas fa-bars';
            isOpen = false;
        } else if (!isOpen) {
            sidebar.classList.add('d-none');
            sidebar.classList.remove('d-lg-block');
        }
    });
});
</script>

