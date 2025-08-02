<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

echo "<h2>Fix Weekly Evaluations Data</h2>";

try {
    // 1. Generate weekly evaluations
    echo "<h3>Step 1: Generate Weekly Evaluations</h3>";
    generateWeeklyEvaluations($pdo);
    echo "✅ Weekly evaluations generated<br><br>";
    
    // 2. Set due dates to today (so they appear as pending)
    echo "<h3>Step 2: Set Due Dates to Today</h3>";
    $stmt = $pdo->prepare("
        UPDATE weekly_evaluations 
        SET due_date = CURDATE() 
        WHERE student_id = ? AND status = 'pending'
    ");
    $stmt->execute([$user['id']]);
    $updated = $stmt->rowCount();
    echo "✅ Updated $updated evaluations to be due today<br><br>";
    
    // 3. Check current state
    echo "<h3>Step 3: Current State</h3>";
    
    // Check pending evaluations
    $pendingEvaluations = getPendingWeeklyEvaluations($user['id'], $pdo);
    echo "Pending evaluations: " . count($pendingEvaluations) . "<br>";
    foreach ($pendingEvaluations as $eval) {
        echo "- {$eval['questionnaire_name']} (Week {$eval['week_number']}/{$eval['year']})<br>";
    }
    
    // Check evaluation progress  
    $evaluationProgress = getWeeklyEvaluationProgress($user['id'], $pdo, 8);
    echo "<br>Evaluation progress items: " . count($evaluationProgress) . "<br>";
    foreach ($evaluationProgress as $progress) {
        echo "- {$progress['questionnaire_name']} (Week {$progress['week_number']}/{$progress['year']}) - {$progress['status']}<br>";
    }
    
    echo "<br><h3>Step 4: Create Sample Completed Data (Optional)</h3>";
    echo "<form method='post'>";
    echo "<button type='submit' name='create_sample' class='btn btn-success'>Create Sample Completed Evaluations</button>";
    echo "</form>";
    
    if (isset($_POST['create_sample'])) {
        echo "<br><h4>Creating Sample Data...</h4>";
        
        // Get current week
        $current_week = getCurrentWeekNumber();
        $current_year = getCurrentYear();
        
        // Get MSLQ and AMS questionnaire IDs
        $stmt = $pdo->query("SELECT id, type FROM questionnaires WHERE type IN ('mslq', 'ams') AND status = 'active'");
        $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($questionnaires as $q) {
            // Create sample questionnaire result for previous week
            $prev_week = $current_week - 1;
            if ($prev_week < 1) {
                $prev_week = 52;
                $prev_year = $current_year - 1;
            } else {
                $prev_year = $current_year;
            }
            
            // Check if already exists
            $stmt = $pdo->prepare("
                SELECT id FROM questionnaire_results 
                WHERE student_id = ? AND questionnaire_id = ? AND week_number = ? AND year = ?
            ");
            $stmt->execute([$user['id'], $q['id'], $prev_week, $prev_year]);
            
            if (!$stmt->fetch()) {
                // Create sample answers (random scores between 4-7)
                $sample_answers = [];
                for ($i = 1; $i <= 20; $i++) { // Assume 20 questions
                    $sample_answers[$i] = rand(4, 7);
                }
                $total_score = array_sum($sample_answers) / count($sample_answers);
                
                // Insert sample result
                $stmt = $pdo->prepare("
                    INSERT INTO questionnaire_results 
                    (student_id, questionnaire_id, answers, total_score, week_number, year, completed_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $user['id'], 
                    $q['id'], 
                    json_encode($sample_answers), 
                    $total_score, 
                    $prev_week, 
                    $prev_year
                ]);
                
                // Update weekly_evaluation status
                $stmt = $pdo->prepare("
                    UPDATE weekly_evaluations 
                    SET status = 'completed', completed_at = NOW() 
                    WHERE student_id = ? AND questionnaire_id = ? AND week_number = ? AND year = ?
                ");
                $stmt->execute([$user['id'], $q['id'], $prev_week, $prev_year]);
                
                echo "✅ Created sample {$q['type']} result for week $prev_week/$prev_year (score: " . number_format($total_score, 2) . ")<br>";
            }
        }
        
        echo "<br>✅ Sample data created! <a href='weekly-evaluations.php' class='btn btn-primary'>Check Weekly Evaluations Page</a>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

<style>
.btn { 
    padding: 8px 16px; 
    background: #007bff; 
    color: white; 
    border: none; 
    border-radius: 4px; 
    text-decoration: none; 
    display: inline-block; 
    margin: 5px;
    cursor: pointer;
}
.btn-success { background: #28a745; }
.btn-primary { background: #007bff; }
</style>
