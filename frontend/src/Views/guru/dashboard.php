<?php
// Data for the view will be passed from the DashboardController
// $userProfile, $teacherCounts, $messages

// Ensure variables are defined to prevent PHP notices if not passed
$userProfile = $userProfile ?? ['name' => 'Guest', 'role' => 'guest'];
$teacherCounts = $teacherCounts ?? [];
$messages = $messages ?? [];

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2"></i>
        Dasbor
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download me-1"></i>
                Ekspor
            </button>
        </div>
    </div>
</div>

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="mb-2">
            Selamat datang, <?php echo htmlspecialchars($userProfile['name']); ?>!
        </h4>
    </div>
</div>

<!-- Teacher Stats -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            My Assignments
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo htmlspecialchars($teacherCounts['my_assignments'] ?? 0); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tasks fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            My Materials
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo htmlspecialchars($teacherCounts['my_materials'] ?? 0); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Students
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo htmlspecialchars($teacherCounts['total_students'] ?? 0); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="/assignments/create" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>
                            Create Assignment
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/quizzes/create" class="btn btn-success w-100">
                            <i class="fas fa-plus me-2"></i>
                            Create Quiz
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/materials/create" class="btn btn-info w-100">
                            <i class="fas fa-upload me-2"></i>
                            Upload Material
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/students" class="btn btn-warning w-100">
                            <i class="fas fa-users me-2"></i>
                            Manage Students
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
