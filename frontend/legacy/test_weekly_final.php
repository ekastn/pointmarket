<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

echo "<h2>Final Test - Weekly Evaluations Data</h2>";

// Update overdue evaluations
updateOverdueEvaluations($pdo);

// Generate weekly evaluations if needed
generateWeeklyEvaluations($pdo);

// Get pending evaluations
$pendingEvaluations = getPendingWeeklyEvaluations($user['id'], $pdo);

// Get evaluation progress
$evaluationProgress = getWeeklyEvaluationProgress($user['id'], $pdo, 8);

echo "<h3>Pending Evaluations</h3>";
if (empty($pendingEvaluations)) {
    echo "❌ No pending evaluations found<br>";
} else {
    echo "✅ Found " . count($pendingEvaluations) . " pending evaluations:<br>";
    foreach ($pendingEvaluations as $eval) {
        echo "- Week {$eval['week_number']}/{$eval['year']}: {$eval['questionnaire_name']} ({$eval['questionnaire_type']}) - Due: {$eval['due_date']}<br>";
    }
}

echo "<br><h3>Evaluation Progress</h3>";
if (empty($evaluationProgress)) {
    echo "❌ No evaluation progress data found<br>";
} else {
    echo "✅ Found " . count($evaluationProgress) . " evaluation progress items:<br>";
    foreach ($evaluationProgress as $progress) {
        echo "- Week {$progress['week_number']}/{$progress['year']}: {$progress['questionnaire_name']} ({$progress['questionnaire_type']}) - Status: {$progress['status']}<br>";
    }
}

echo "<br><h3>Current State</h3>";
echo "Result for weekly-evaluations.php:<br>";
echo "- Pending evaluations: " . (empty($pendingEvaluations) ? "Empty (will show 'Get started' message)" : "Has data") . "<br>";
echo "- Evaluation progress: " . (empty($evaluationProgress) ? "Empty (will show 'No evaluation data available')" : "Has data") . "<br>";

echo "<br><a href='weekly-evaluations.php' class='btn btn-primary'>Go to Weekly Evaluations Page</a>";
?>
