<?php
// Data for this view will be passed from the TeacherEvaluationMonitoringController
$user = $user ?? ['name' => 'Guest'];
$studentStatus = $studentStatus ?? [];
$weeklyOverview = $weeklyOverview ?? [];
$messages = $messages ?? [];

// Helper function to format date (ideally in a utility file or passed from controller)
if (!function_exists('formatDate')) {
    function formatDate($dateString) {
        if (empty($dateString)) {
            return 'N/A';
        }
        return date('d/m/Y H:i', strtotime($dateString));
    }
}

// Helper function to get current week and year (ideally in a utility file or passed from controller)
if (!function_exists('getCurrentWeekNumber')) {
    function getCurrentWeekNumber() {
        return date('W');
    }
}
if (!function_exists('getCurrentYear')) {
    function getCurrentYear() {
        return date('Y');
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Student Evaluation Monitoring</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="exportData()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo $type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Current Week Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card status-card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                </h5>
                <h3 class="text-success">
                    <?php 
                    $completed = array_sum(array_column($studentStatus, 'completed_this_week'));
                    echo htmlspecialchars($completed);
                    ?>
                </h3>
                <p class="card-text">Completed This Week</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card status-card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                </h5>
                <h3 class="text-warning">
                    <?php 
                    $pending = array_sum(array_column($studentStatus, 'pending_this_week'));
                    echo htmlspecialchars($pending);
                    ?>
                </h3>
                <p class="card-text">Pending This Week</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card status-card text-center">
            <div class="card-body">
                <h5 class="card-title text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                </h5>
                <h3 class="text-danger">
                    <?php 
                    $overdue = array_sum(array_column($studentStatus, 'overdue_this_week'));
                    echo htmlspecialchars($overdue);
                    ?>
                </h3>
                <p class="card-text">Overdue This Week</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card status-card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">
                    <i class="fas fa-users fa-2x mb-2"></i>
                </h5>
                <h3 class="text-primary"><?php echo htmlspecialchars(count($studentStatus)); ?></h3>
                <p class="card-text">Total Students</p>
            </div>
        </div>
    </div>
</div>

<!-- Student Status Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student Evaluation Status - Week <?php echo htmlspecialchars(getCurrentWeekNumber()); ?>/<?php echo htmlspecialchars(getCurrentYear()); ?></h5>
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" id="searchStudent" placeholder="Search students...">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="studentTable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>MSLQ Score</th>
                                <th>AMS Score</th>
                                <th>Last Evaluation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($studentStatus)): ?>
                                <?php foreach ($studentStatus as $student): ?>
                                <tr class="student-row">
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['student_name']); ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['student_email']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <?php if ($student['completed_this_week'] > 0): ?>
                                                <span class="badge status-badge completed">
                                                    <?php echo htmlspecialchars($student['completed_this_week']); ?> Completed
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($student['pending_this_week'] > 0): ?>
                                                <span class="badge status-badge pending">
                                                    <?php echo htmlspecialchars($student['pending_this_week']); ?> Pending
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($student['overdue_this_week'] > 0): ?>
                                                <span class="badge status-badge overdue">
                                                    <?php echo htmlspecialchars($student['overdue_this_week']); ?> Overdue
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($student['completed_this_week'] == 0 && $student['pending_this_week'] == 0 && $student['overdue_this_week'] == 0): ?>
                                                <span class="badge bg-secondary">No Evaluations</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($student['mslq_score_this_week'] !== null): ?>
                                            <?php 
                                            $score = $student['mslq_score_this_week'];
                                            $scoreClass = $score >= 5.5 ? 'score-high' : ($score >= 4 ? 'score-medium' : 'score-low');
                                            ?>
                                            <span class="score-badge <?php echo $scoreClass; ?>">
                                                <?php echo htmlspecialchars(number_format($score, 2)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($student['ams_score_this_week'] !== null): ?>
                                            <?php 
                                            $score = $student['ams_score_this_week'];
                                            $scoreClass = $score >= 5.5 ? 'score-high' : ($score >= 4 ? 'score-medium' : 'score-low');
                                            ?>
                                            <span class="score-badge <?php echo $scoreClass; ?>">
                                                <?php echo htmlspecialchars(number_format($score, 2)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($student['last_evaluation']): ?>
                                            <small><?php echo htmlspecialchars(formatDate($student['last_evaluation'])); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewStudentDetail(<?php echo htmlspecialchars($student['student_id']); ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No students found or no evaluation data for this week.</h5>
                                        <p class="text-muted">Ensure students are registered and have active weekly evaluations.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Progress Overview -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Weekly Progress Overview (Last 4 Weeks)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($weeklyOverview)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Week/Year</th>
                                        <th>Questionnaire</th>
                                        <th>Completion Rate</th>
                                        <th>Average Score</th>
                                        <th>Status Distribution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($weeklyOverview as $overview): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($overview['week_number']); ?>/<?php echo htmlspecialchars($overview['year']); ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars(strtoupper($overview['questionnaire_type'])); ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $completion_rate = $overview['total_count'] > 0 ? 
                                                ($overview['completed_count'] / $overview['total_count']) * 100 : 0;
                                            ?>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" style="width: <?php echo htmlspecialchars($completion_rate); ?>%">
                                                    <?php echo htmlspecialchars(number_format($completion_rate, 1)); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($overview['average_score'] !== null): ?>
                                                <strong><?php echo htmlspecialchars($overview['average_score']); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <span class="text-success"><?php echo htmlspecialchars($overview['completed_count']); ?> completed</span> |
                                                <span class="text-warning"><?php echo htmlspecialchars($overview['pending_count']); ?> pending</span> |
                                                <span class="text-danger"><?php echo htmlspecialchars($overview['overdue_count']); ?> overdue</span>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No aggregated evaluation data available.</h5>
                            <p class="text-muted">Weekly evaluation data will appear here once students start completing evaluations.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Detail Modal -->
    <div class="modal fade" id="studentDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentDetailModalTitle">
                        <i class="fas fa-user me-2"></i>Student Evaluation Detail
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="studentDetailModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchStudent').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#studentTable tbody tr');
            
            rows.forEach(row => {
                const studentName = row.cells[0].textContent.toLowerCase();
                const studentEmail = row.cells[1].textContent.toLowerCase();
                
                if (studentName.includes(searchTerm) || studentEmail.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function viewStudentDetail(studentId) {
            // Show loading
            document.getElementById('studentDetailModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Loading student data...</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('studentDetailModal'));
            modal.show();
            
            // Load student data
            fetch('/api/v1/teacher/evaluations/status?student_id=' + studentId) // This endpoint is not for single student detail, will need adjustment
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Filter for the specific student if the API returns all students
                    const studentDetail = data.data.find(s => s.student_id == studentId);
                    if (studentDetail) {
                        loadStudentDetail(studentDetail, data.data); // Pass all data for now, refine later
                    } else {
                        document.getElementById('studentDetailModalBody').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Student detail not found.
                            </div>
                        `;
                    }
                } else {
                    document.getElementById('studentDetailModalBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading student data: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('studentDetailModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Network error. Please try again.
                    </div>
                `;
            });
        }

        function loadStudentDetail(student, allStudentsData) {
            document.getElementById('studentDetailModalTitle').innerHTML = `
                <i class="fas fa-user me-2"></i>${student.student_name} - Evaluation History
            `;
            
            let html = `
                <div class="mb-3">
                    <h6>Student Information</h6>
                    <p><strong>Name:</strong> ${student.student_name}<br>
                    <strong>Email:</strong> ${student.student_email}</p>
                </div>
                <hr>
                <h6>Evaluation History (Last 8 Weeks)</h6>
            `;
            
            // This part needs to fetch actual history for the specific student
            // For now, it will display a placeholder or general info
            html += `
                <div class="text-center py-4">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Detailed history coming soon.</h6>
                    <p class="text-muted">This section will show a student's full evaluation history and progress trends.</p>
                </div>
            `;
            
            document.getElementById('studentDetailModalBody').innerHTML = html;
        }

        function exportData() {
            // Simple CSV export functionality
            const rows = [];
            const table = document.getElementById('studentTable');
            
            // Header
            const headerCells = table.querySelectorAll('thead th');
            const header = Array.from(headerCells).slice(0, -1).map(th => th.textContent.trim());
            rows.push(header.join(','));
            
            // Data rows
            const dataRows = table.querySelectorAll('tbody tr');
            dataRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const cells = row.querySelectorAll('td');
                    const rowData = Array.from(cells).slice(0, -1).map(td => {
                        return `"${td.textContent.trim().replace(/"/g, '""')}"`;
                    });
                    rows.push(rowData.join(','));
                }
            });
            
            // Download
            const csvContent = rows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `student_evaluation_status_week_${<?php echo htmlspecialchars(getCurrentWeekNumber()); ?>}_${<?php echo htmlspecialchars(getCurrentYear()); ?>}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>

