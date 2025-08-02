<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

echo "<h2>Diagnosis: Weekly Evaluation Data</h2>";
echo "<p>Student: {$user['name']} (ID: {$user['id']})</p>";

// Step 1: Check current week/year
$current_week = getCurrentWeekNumber();
$current_year = getCurrentYear();
echo "<h3>1. Current Week/Year</h3>";
echo "Current Week: $current_week<br>";
echo "Current Year: $current_year<br><br>";

// Step 2: Check if weekly_evaluations table has any data for this student
echo "<h3>2. Weekly Evaluations Table for This Student</h3>";
$stmt = $pdo->prepare("SELECT * FROM weekly_evaluations WHERE student_id = ? ORDER BY year DESC, week_number DESC");
$stmt->execute([$user['id']]);
$studentEvals = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($studentEvals)) {
    echo "❌ No weekly_evaluations found for student {$user['id']}<br>";
} else {
    echo "✅ Found " . count($studentEvals) . " weekly evaluations for student:<br>";
    foreach ($studentEvals as $eval) {
        echo "- Week {$eval['week_number']}/{$eval['year']}: Questionnaire {$eval['questionnaire_id']} - {$eval['status']}<br>";
    }
}
echo "<br>";

// Step 3: Check available questionnaires (should be MSLQ and AMS only)
echo "<h3>3. Available Questionnaires (for Weekly Evaluations)</h3>";
$stmt = $pdo->query("SELECT id, name, type FROM questionnaires WHERE status = 'active' AND type != 'vark'");
$questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($questionnaires)) {
    echo "❌ No questionnaires available for weekly evaluations<br>";
} else {
    echo "✅ Available questionnaires:<br>";
    foreach ($questionnaires as $q) {
        echo "- ID {$q['id']}: {$q['name']} (type: {$q['type']})<br>";
    }
}
echo "<br>";

// Step 4: Run generateWeeklyEvaluations manually
echo "<h3>4. Generating Weekly Evaluations</h3>";
echo "Calling generateWeeklyEvaluations()...<br>";
$result = generateWeeklyEvaluations($pdo);
echo "Result: " . ($result ? "Success" : "Failed") . "<br><br>";

// Step 5: Check weekly_evaluations again after generation
echo "<h3>5. Weekly Evaluations After Generation</h3>";
$stmt = $pdo->prepare("SELECT * FROM weekly_evaluations WHERE student_id = ? ORDER BY year DESC, week_number DESC");
$stmt->execute([$user['id']]);
$studentEvalsAfter = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($studentEvalsAfter)) {
    echo "❌ Still no weekly_evaluations found for student {$user['id']}<br>";
    
    // Debug: Check if there are any students in the system
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'siswa'");
    $studentCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total students in system: {$studentCount['count']}<br>";
    
} else {
    echo "✅ Found " . count($studentEvalsAfter) . " weekly evaluations after generation:<br>";
    foreach ($studentEvalsAfter as $eval) {
        echo "- Week {$eval['week_number']}/{$eval['year']}: Questionnaire {$eval['questionnaire_id']} - {$eval['status']} (Due: {$eval['due_date']})<br>";
    }
}
echo "<br>";

// Step 6: Test getPendingWeeklyEvaluations
echo "<h3>6. Pending Weekly Evaluations</h3>";
$pendingEvaluations = getPendingWeeklyEvaluations($user['id'], $pdo);

if (empty($pendingEvaluations)) {
    echo "❌ No pending evaluations found<br>";
    
    // Check if there are evaluations but not yet due
    $stmt = $pdo->prepare("
        SELECT we.*, q.name as questionnaire_name, q.type as questionnaire_type
        FROM weekly_evaluations we
        JOIN questionnaires q ON we.questionnaire_id = q.id
        WHERE we.student_id = ? AND we.status = 'pending'
        ORDER BY we.due_date ASC
    ");
    $stmt->execute([$user['id']]);
    $allPending = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($allPending)) {
        echo "Found " . count($allPending) . " pending evaluations (not yet due):<br>";
        foreach ($allPending as $eval) {
            echo "- Week {$eval['week_number']}/{$eval['year']}: {$eval['questionnaire_name']} - Due: {$eval['due_date']}<br>";
        }
    }
} else {
    echo "✅ Found " . count($pendingEvaluations) . " pending evaluations (due now):<br>";
    foreach ($pendingEvaluations as $eval) {
        echo "- Week {$eval['week_number']}/{$eval['year']}: {$eval['questionnaire_name']} - Due: {$eval['due_date']}<br>";
    }
}
echo "<br>";

// Step 7: Test getWeeklyEvaluationProgress
echo "<h3>7. Weekly Evaluation Progress</h3>";
$evaluationProgress = getWeeklyEvaluationProgress($user['id'], $pdo, 8);

if (empty($evaluationProgress)) {
    echo "❌ No evaluation progress found<br>";
    echo "This is why 'No evaluation data available' is shown<br>";
} else {
    echo "✅ Found " . count($evaluationProgress) . " evaluation progress items:<br>";
    foreach ($evaluationProgress as $progress) {
        echo "- Week {$progress['week_number']}/{$progress['year']}: {$progress['questionnaire_name']} - Status: {$progress['status']}<br>";
    }
}
echo "<br>";

echo "<h3>8. How to Fix This</h3>";
echo "<ol>";
echo "<li><strong>Wait for due date:</strong> Weekly evaluations are only shown as 'pending' when due_date <= today</li>";
echo "<li><strong>Force due date:</strong> Update due_date to today or earlier</li>";
echo "<li><strong>Complete evaluations:</strong> Submit questionnaires to get progress data</li>";
echo "</ol>";

echo "<br><h3>9. Quick Fix - Set Due Date to Today</h3>";
echo "<form method='post'>";
echo "<button type='submit' name='fix_due_date' class='btn btn-warning'>Set All Due Dates to Today</button>";
echo "</form>";

if (isset($_POST['fix_due_date'])) {
    $stmt = $pdo->prepare("UPDATE weekly_evaluations SET due_date = CURDATE() WHERE student_id = ? AND status = 'pending'");
    $result = $stmt->execute([$user['id']]);
    $updated = $stmt->rowCount();
    echo "<div class='alert alert-success'>Updated $updated evaluations to be due today. <a href='weekly-evaluations.php'>Check Weekly Evaluations Page</a></div>";
}
?>

<style>
.btn { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; text-decoration: none; display: inline-block; margin: 5px; }
.btn-warning { background: #ffc107; color: #000; }
.alert { padding: 15px; margin: 10px 0; border-radius: 4px; }
.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
</style>
