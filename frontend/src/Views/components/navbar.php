<?php
$user = $_SESSION['user_data'] ?? ['name' => 'Guest', 'role' => 'guest'];
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top" role="navigation" aria-label="Global">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <button class="btn btn-primary d-lg-none me-2" type="button" id="mobileSidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-graduation-cap me-2"></i>
                POINTMARKET
            </a>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Intentionally empty: primary navigation lives in the Sidebar -->
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile">
                            <i class="fas fa-user-edit me-2"></i>Profile
                        </a></li>
                        <li><a class="dropdown-item" href="/settings">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    body { padding-top: 56px; /* Height of navbar */ }
</style>
