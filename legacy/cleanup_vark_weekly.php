<?php
require_once 'includes/config.php';

$database = new Database();
$pdo = $database->getConnection();

echo "<h2>Cleaning VARK from Weekly Evaluations</h2>";

try {
    // Check VARK entries before deletion
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM weekly_evaluations we 
        JOIN questionnaires q ON we.questionnaire_id = q.id 
        WHERE q.type = 'vark'
    ");
    $before = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "VARK entries in weekly_evaluations before cleanup: {$before['count']}<br>";
    
    // Delete VARK entries from weekly_evaluations
    $stmt = $pdo->prepare("
        DELETE we FROM weekly_evaluations we 
        JOIN questionnaires q ON we.questionnaire_id = q.id 
        WHERE q.type = 'vark'
    ");
    $result = $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    echo "Deleted $deletedCount VARK entries from weekly_evaluations<br>";
    
    // Check after deletion
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM weekly_evaluations we 
        JOIN questionnaires q ON we.questionnaire_id = q.id 
        WHERE q.type = 'vark'
    ");
    $after = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "VARK entries in weekly_evaluations after cleanup: {$after['count']}<br>";
    
    // Show remaining weekly evaluations
    echo "<br><h3>Remaining Weekly Evaluations</h3>";
    $stmt = $pdo->query("
        SELECT 
            we.student_id,
            we.week_number,
            we.year,
            q.name as questionnaire_name,
            q.type,
            we.status
        FROM weekly_evaluations we 
        JOIN questionnaires q ON we.questionnaire_id = q.id 
        ORDER BY we.student_id, we.year DESC, we.week_number DESC
        LIMIT 20
    ");
    $remaining = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($remaining)) {
        echo "❌ No weekly evaluations found<br>";
    } else {
        echo "✅ Found " . count($remaining) . " weekly evaluations:<br>";
        foreach ($remaining as $eval) {
            echo "- Student {$eval['student_id']}, Week {$eval['week_number']}/{$eval['year']}: {$eval['questionnaire_name']} ({$eval['type']}) - {$eval['status']}<br>";
        }
    }
    
    echo "<br>✅ Cleanup completed successfully!<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
