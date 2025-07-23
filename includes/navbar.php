<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-graduation-cap me-2"></i>
            POINTMARKET
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Dashboard
                    </a>
                </li>
                
                <?php if ($user['role'] === 'siswa'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="assignments.php">
                            <i class="fas fa-tasks me-1"></i>
                            Assignments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quiz.php">
                            <i class="fas fa-question-circle me-1"></i>
                            Quiz
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="questionnaire.php">
                            <i class="fas fa-clipboard-list me-1"></i>
                            Questionnaires
                        </a>
                    </li>
                <?php elseif ($user['role'] === 'guru'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chalkboard-teacher me-1"></i>
                            Teaching
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="assignments.php">Assignments</a></li>
                            <li><a class="dropdown-item" href="quiz.php">Quiz</a></li>
                            <li><a class="dropdown-item" href="materials.php">Materials</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">
                            <i class="fas fa-users me-1"></i>
                            Students
                        </a>
                    </li>
                <?php else: // Admin ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="users.php">Users</a></li>
                            <li><a class="dropdown-item" href="reports.php">Reports</a></li>
                            <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($user['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user-edit me-2"></i>Profile
                        </a></li>
                        <li><a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    body {
        padding-top: 56px; /* Height of navbar */
    }
</style>
