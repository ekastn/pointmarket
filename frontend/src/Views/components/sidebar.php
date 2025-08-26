<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$user = $_SESSION['user_data'] ?? null;

// Load centralized menu config (anchored at app root)
$menus = include_app('config/menu.php');
$role = $user['role'] ?? 'siswa';
$sections = $menus[$role] ?? [];
?>

<div id="sidebarOverlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark d-none" style="opacity: 0.5; z-index: 1029;"></div>

<div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 bg-light">
    <button id="sidebarToggle" class="btn rounded-circle" style="z-index: 1;">
        <i class="fas fa-chevron-left"></i>
    </button>
    <nav class="nav nav-pills flex-column mb-auto mb-8" role="navigation" aria-label="Sidebar">
        <div class="nav flex-column gap-2">
            <?php foreach ($sections as $index => $section): ?>
                <?php if ($index !== 0): ?>
                    <hr />
                <?php endif; ?>
                <?php if (!empty($section['label'])): ?>
                    <h6 class="px-3 mb-1 text-muted"><?= htmlspecialchars($section['label']); ?></h6>
                <?php endif; ?>
                <?php foreach ($section['items'] as $menu): ?>
                    <?php $renderer->includePartial('components/partials/sidebar_link', $menu); ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </nav>
</div>

<style>
    #sidebar .nav-link.active {
        color: #007bff;
        background-color: #e9ecef;
        border-left: 4px solid #007bff;
        padding-left: 11px;
    }

    #sidebar .nav-link:hover {
        color: #007bff;
        background-color: #e9ecef;
        border-left: 4px solid #007bff;
        padding-left: 11px;
    }

    #sidebar {
        width: 280px;
        transition: all 0.3s;
        position: sticky; /* Stick under the fixed navbar */
        top: 56px; /* navbar height offset */
        height: calc(100vh - 56px); /* full viewport minus navbar */
        overflow: hidden; /* contain inner scroll */
    }

    /* Make inner nav fill and scroll */
    #sidebar nav {
        flex: 1 1 auto;
        overflow-y: auto;
        overflow-x: hidden; /* prevent horizontal scroll */
    }

    /* When collapsed, ensure no horizontal overflow appears */
    #sidebar.collapsed,
    #sidebar.collapsed nav {
        overflow-x: hidden;
    }
    #sidebar.collapsed .nav-link {
        white-space: nowrap;
        overflow: hidden;
    }
    #sidebar.collapsed {
        width: 88px;
    }
    #sidebar.collapsed .nav-link span,
    #sidebar.collapsed .text-muted,
    #sidebar.collapsed hr {
        display: none;
    }
    #sidebar .nav-link .fas {
        width: 24px;
        text-align: center;
    }

    #sidebarToggle {
        position: absolute;
        top: 1rem;
        right: -1.25rem;
        z-index: 1031;
        width: 2.5rem;
        height: 2.5rem;
        border: 1px solid #dee2e6;
        background-color: #fff;
        transition: transform 0.3s;
    }

    #sidebar.collapsed #sidebarToggle {
        transform: translateX(-6px);
    }

    #sidebar.collapsed #sidebarToggle i {
        transform: rotate(180deg);
    }

    .custom-tooltip .tooltip-inner {
      background-color: #fff;
      color: #495057;
      font-size: 0.9rem;
      font-weight: 400;
      border-radius: 0.25rem;
      border: 1px solid #ced4da;
      box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
      padding: 0.5rem 1rem;
    }
    .custom-tooltip .tooltip-arrow::before {
      border-right-color: #fff;
    }

    @media (max-width: 768px) {
        #sidebar {
            position: fixed;
            top: 56px; /* keep below navbar */
            height: calc(100vh - 56px);
            z-index: 1030;
            transform: translateX(-100%);
            overflow: hidden;
        }
        #sidebar nav { overflow-y: auto; }
        #sidebar.show {
            transform: translateX(0);
        }
        #sidebar.collapsed {
            transform: translateX(-100%);
        }
        #sidebarToggle {
            display: none; /* Hide on mobile, use navbar toggle if needed */
        }
        .content-wrapper {
            margin-left: 0;
        }
        .content-wrapper.collapsed {
            margin-left: 0;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const contentWrapper = document.querySelector('.content-wrapper');
    const overlay = document.getElementById('sidebarOverlay');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle'); // Get the new button

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('#sidebar .sidebar-nav-link'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
            customClass: 'custom-tooltip'
        });
    });

    const handleTooltips = () => {
        if (sidebar.classList.contains('collapsed') && window.innerWidth > 768) {
            tooltipList.forEach(tooltip => tooltip.enable());
        } else {
            tooltipList.forEach(tooltip => tooltip.disable());
        }
    };

    const toggleDesktopSidebar = () => {
        sidebar.classList.toggle('collapsed');
        if(contentWrapper) {
            contentWrapper.classList.toggle('collapsed');
        }
        localStorage.setItem('sidebar.collapsed', sidebar.classList.contains('collapsed'));
        handleTooltips();
    };

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleDesktopSidebar);
    }

    const toggleMobileSidebar = () => {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('d-none');
    };

    if (mobileSidebarToggle) { // Listen to the new button
        mobileSidebarToggle.addEventListener('click', toggleMobileSidebar);
    }
    if (overlay) {
        overlay.addEventListener('click', toggleMobileSidebar);
    }

    const setupSidebar = () => {
        if (window.innerWidth > 768) {
            if (localStorage.getItem('sidebar.collapsed') === 'true') {
                sidebar.classList.add('collapsed');
                if(contentWrapper) {
                    contentWrapper.classList.add('collapsed');
                }
            }
        } else {
            sidebar.classList.remove('show');
            overlay.classList.add('d-none');
        }
        handleTooltips();
    };

    window.addEventListener('resize', setupSidebar);
    setupSidebar(); // Initial setup
});
</script>
