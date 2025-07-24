<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing assignments page step by step...<br><br>";

try {
    echo "1. Loading config...<br>";
    require_once 'includes/config.php';
    echo "✅ Config loaded<br>";
    
    echo "2. Checking login...<br>";
    // Skip login check for testing
    // requireLogin();
    echo "⚠️ Skipped login check for testing<br>";
    
    echo "3. Getting user...<br>";
    // $user = getCurrentUser();
    // Create a fake user for testing
    $user = ['id' => 1, 'role' => 'siswa', 'name' => 'Test Student'];
    echo "✅ User created: " . $user['name'] . " (Role: " . $user['role'] . ")<br>";
    
    echo "4. Connecting to database...<br>";
    $database = new Database();
    $pdo = $database->getConnection();
    echo "✅ Database connected<br>";
    
    echo "5. Testing getStudentAssignments function...<br>";
    $assignments = getStudentAssignments($user['id'], $pdo, 'all');
    echo "✅ Found " . count($assignments) . " assignments<br>";
    
    echo "6. Testing getAssignmentStats function...<br>";
    $stats = getAssignmentStats($user['id'], $pdo);
    echo "✅ Stats loaded: " . json_encode($stats) . "<br>";
    
    echo "7. Testing getSubjectsWithCounts function...<br>";
    $subjects = getSubjectsWithCounts($user['id'], $pdo);
    echo "✅ Found " . count($subjects) . " subjects<br>";
    
    echo "8. Testing getPendingWeeklyEvaluations function...<br>";
    $pendingEvaluations = getPendingWeeklyEvaluations($user['id'], $pdo);
    echo "✅ Found " . count($pendingEvaluations) . " pending evaluations<br>";
    
    echo "<br>🎉 All functions work! The assignments page should load now.<br>";
    echo "<a href='assignments.php' target='_blank'>Test Assignments Page</a>";
    
} catch (Exception $e) {
    echo "❌ Error at step: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
