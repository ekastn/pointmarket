<?php
// Simplified assignments page for testing
session_start();

// Create a test session if none exists
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'test_student';
    $_SESSION['role'] = 'siswa';
}

require_once 'includes/config.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Assignments Test - POINTMARKET</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
</head>
<body>
    <div class='container mt-5'>
        <h1><i class='fas fa-tasks me-2'></i>Assignments Test Page</h1>
        <div class='alert alert-info'>
            This is a simplified version to test if the basic functionality works.
        </div>";

try {
    $user = getCurrentUser();
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<div class='alert alert-success'>✅ User and database loaded successfully</div>";
    
    // Test the assignment functions
    $assignments = getStudentAssignments($user['id'], $pdo, 'all');
    $stats = getAssignmentStats($user['id'], $pdo);
    
    echo "<h3>Assignment Statistics</h3>";
    echo "<div class='row'>";
    echo "<div class='col-md-3'><div class='card'><div class='card-body text-center'>";
    echo "<h4>" . ($stats['total_assignments'] ?? 0) . "</h4><p>Total</p></div></div></div>";
    echo "<div class='col-md-3'><div class='card'><div class='card-body text-center'>";
    echo "<h4>" . ($stats['completed'] ?? 0) . "</h4><p>Completed</p></div></div></div>";
    echo "<div class='col-md-3'><div class='card'><div class='card-body text-center'>";
    echo "<h4>" . ($stats['in_progress'] ?? 0) . "</h4><p>In Progress</p></div></div></div>";
    echo "<div class='col-md-3'><div class='card'><div class='card-body text-center'>";
    echo "<h4>" . ($stats['overdue'] ?? 0) . "</h4><p>Overdue</p></div></div></div>";
    echo "</div>";
    
    echo "<h3 class='mt-4'>Assignments List</h3>";
    if (!empty($assignments)) {
        echo "<div class='row'>";
        foreach ($assignments as $assignment) {
            echo "<div class='col-md-4 mb-3'>";
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<h5 class='card-title'>" . htmlspecialchars($assignment['title']) . "</h5>";
            echo "<p class='card-text'>" . htmlspecialchars(substr($assignment['description'], 0, 100)) . "...</p>";
            echo "<span class='badge bg-primary'>" . htmlspecialchars($assignment['subject']) . "</span> ";
            echo "<span class='badge bg-secondary'>" . ucfirst(str_replace('_', ' ', $assignment['student_status'])) . "</span>";
            echo "<div class='mt-2'>";
            echo "<small class='text-muted'>Due: " . formatDate($assignment['due_date']) . "</small><br>";
            echo "<small class='text-muted'>Points: " . $assignment['points'] . "</small>";
            echo "</div>";
            echo "</div></div></div>";
        }
        echo "</div>";
    } else {
        echo "<div class='alert alert-warning'>No assignments found. This might be because:</div>";
        echo "<ul>";
        echo "<li>No assignments have been created in the database</li>";
        echo "<li>The user ID doesn't match any student assignments</li>";
        echo "<li>All assignments are inactive</li>";
        echo "</ul>";
        echo "<a href='test-assignments.php' class='btn btn-primary'>Run Database Setup</a>";
    }
    
    echo "<div class='mt-4'>";
    echo "<a href='assignments.php' class='btn btn-success'>Go to Full Assignments Page</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>Error:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "    </div>
</body>
</html>";
?>
