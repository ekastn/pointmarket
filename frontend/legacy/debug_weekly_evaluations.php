<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

echo "<h2>Debug Weekly Evaluations</h2>";

// Check current week and year
$current_week = getCurrentWeekNumber();
$current_year = getCurrentYear();
echo "<h3>Current Week/Year</h3>";
echo "Current Week: $current_week<br>";
echo "Current Year: $current_year<br><br>";

// Check if weekly_evaluations table has data
echo "<h3>Weekly Evaluations Table Data</h3>";
$stmt = $pdo->prepare("SELECT * FROM weekly_evaluations WHERE student_id = ? ORDER BY year DESC, week_number DESC LIMIT 10");
$stmt->execute([$user['id']]);
$weeklyEvals = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($weeklyEvals)) {
    echo "❌ No weekly_evaluations found for student ID: {$user['id']}<br>";
    
    // Check if table exists and has any data
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM weekly_evaluations");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total records in weekly_evaluations table: {$total['total']}<br>";
    
    if ($total['total'] == 0) {
        echo "❌ weekly_evaluations table is completely empty<br>";
    }
} else {
    echo "✅ Found " . count($weeklyEvals) . " weekly evaluations:<br>";
    foreach ($weeklyEvals as $eval) {
        echo "- Week {$eval['week_number']}/{$eval['year']}: Questionnaire {$eval['questionnaire_id']}, Status: {$eval['status']}<br>";
    }
}

echo "<br>";

// Check questionnaires table
echo "<h3>Available Questionnaires</h3>";
$stmt = $pdo->query("SELECT id, name, type FROM questionnaires WHERE status = 'active'");
$questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($questionnaires as $q) {
    echo "- ID {$q['id']}: {$q['name']} (type: {$q['type']})<br>";
}

echo "<br>";

// Test getWeeklyEvaluationProgress function
echo "<h3>Testing getWeeklyEvaluationProgress Function</h3>";
$evaluationProgress = getWeeklyEvaluationProgress($user['id'], $pdo, 8);

if (empty($evaluationProgress)) {
    echo "❌ getWeeklyEvaluationProgress returned empty array<br>";
    
    // Debug the query
    echo "<h4>Debug Query</h4>";
    $start_week = max(1, $current_week - 8 + 1);
    $limit = 8 * 2;
    
    echo "Query parameters:<br>";
    echo "- student_id: {$user['id']}<br>";
    echo "- current_year: $current_year<br>";
    echo "- start_week: $start_week<br>";
    echo "- limit: $limit<br><br>";
    
    $stmt = $pdo->prepare("
        SELECT 
            we.week_number,
            we.year,
            q.type as questionnaire_type,
            q.name as questionnaire_name,
            we.status,
            we.due_date,
            we.completed_at,
            qr.total_score
        FROM weekly_evaluations we
        JOIN questionnaires q ON we.questionnaire_id = q.id
        LEFT JOIN questionnaire_results qr ON (
            qr.student_id = we.student_id 
            AND qr.questionnaire_id = we.questionnaire_id 
            AND qr.week_number = we.week_number 
            AND qr.year = we.year
        )
        WHERE we.student_id = ? 
        AND ((we.year = ? AND we.week_number >= ?) OR we.year > ?)
        ORDER BY we.year DESC, we.week_number DESC, q.type
        LIMIT ?
    ");
    
    $stmt->execute([$user['id'], $current_year, $start_week, $current_year, $limit]);
    $debugResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Direct query results: " . count($debugResults) . " rows<br>";
    if (!empty($debugResults)) {
        foreach ($debugResults as $result) {
            echo "- Week {$result['week_number']}/{$result['year']}: {$result['questionnaire_name']} ({$result['status']})<br>";
        }
    }
} else {
    echo "✅ getWeeklyEvaluationProgress returned " . count($evaluationProgress) . " items:<br>";
    foreach ($evaluationProgress as $progress) {
        echo "- Week {$progress['week_number']}/{$progress['year']}: {$progress['questionnaire_name']} ({$progress['status']})<br>";
    }
}

echo "<br>";

// Check if generateWeeklyEvaluations function was called
echo "<h3>Calling generateWeeklyEvaluations</h3>";
generateWeeklyEvaluations($pdo);
echo "✅ generateWeeklyEvaluations() called<br>";

// Check again after generation
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM weekly_evaluations WHERE student_id = ?");
$stmt->execute([$user['id']]);
$total = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Weekly evaluations after generation: {$total['total']}<br>";

?>
