<?php
// Data for the view will be passed from the DashboardController
// $userProfile, $adminCounts, $messages

// Ensure variables are defined to prevent PHP notices if not passed
$userProfile = $_SESSION['user_data'] ?? ['name' => 'Guest', 'role' => 'guest'];

// // AdminDashboardStatsDTO holds counts for the admin dashboard.
// type AdminDashboardStatsDTO struct {
// 	TotalUsers              int64 `json:"total_users"`
// 	TotalTeachers           int64 `json:"total_teachers"`
// 	TotalStudents           int64 `json:"total_students"`
// 	TotalCourses            int64 `json:"total_courses"`
// 	TotalPointsTransactions int64 `json:"total_points_transaction"`
// 	TotalProducts           int64 `json:"total_products"`
// 	TotalMissions           int64 `json:"total_missions"`
// 	TotalBadges             int64 `json:"total_badges"`
// }
//
$adminStats = $adminStats ?? [
    'total_users' => 0,
    'total_teachers' => 0,
    'total_students' => 0,
    'total_courses' => 0,
    'total_points_transaction' => 0,
    'total_products' => 0,
    'total_missions' => 0,
    'total_badges' => 0
];

$statsItems = [
    [ 'title' => 'Users', 'value' => $adminStats['total_users'], 'icon' => 'fas fa-users' ],
    [ 'title' => 'Teachers', 'value' => $adminStats['total_teachers'], 'icon' => 'fas fa-chalkboard-teacher' ],
    [ 'title' => 'Students', 'value' => $adminStats['total_students'], 'icon' => 'fas fa-user-graduate' ],
    [ 'title' => 'Courses', 'value' => $adminStats['total_courses'], 'icon' => 'fas fa-graduation-cap' ],
    [ 'title' => 'Points Transactions', 'value' => $adminStats['total_points_transaction'], 'icon' => 'fas fa-coins' ],
    [ 'title' => 'Products', 'value' => $adminStats['total_products'], 'icon' => 'fas fa-box-open' ],
    [ 'title' => 'Missions', 'value' => $adminStats['total_missions'], 'icon' => 'fas fa-trophy' ],
    [ 'title' => 'Badges', 'value' => $adminStats['total_badges'], 'icon' => 'fas fa-medal' ],
]

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2"></i>
        Dashboard
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

<!-- Messages -->
<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($type); ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="mb-2">
            Selamat datang, <?php echo htmlspecialchars($userProfile['name']); ?>!
        </h4>
    </div>
</div>

<!-- Admin Stats -->
<div class="row mb-4">
    <?php foreach ($statsItems as $item): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <?php $renderer->includePartial('components/partials/card_stats', $item); ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class_="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="/users" class="btn btn-primary w-100">
                            <i class="fas fa-users-cog me-2"></i>
                            Manage Users
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/reports" class="btn btn-success w-100">
                            <i class="fas fa-chart-bar me-2"></i>
                            View Reports
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/settings" class="btn btn-info w-100">
                            <i class="fas fa-cog me-2"></i>
                            System Settings
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/backup" class="btn btn-warning w-100">
                            <i class="fas fa-database me-2"></i>
                            Backup Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
