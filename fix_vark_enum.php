<?php
require_once 'includes/config.php';

$database = new Database();
$pdo = $database->getConnection();

try {
    echo "Altering questionnaires table to add 'vark' to type enum...\n";
    
    // Alter table to add 'vark' to the enum
    $sql = "ALTER TABLE questionnaires MODIFY COLUMN type ENUM('mslq', 'ams', 'vark') NOT NULL";
    $result = $pdo->exec($sql);
    
    echo "Alter table result: Success\n";
    
    // Now update VARK questionnaire type
    $stmt = $pdo->prepare("UPDATE questionnaires SET type = 'vark' WHERE id = 3");
    $result = $stmt->execute();
    
    echo "Update VARK type result: " . ($result ? "Success" : "Failed") . "\n";
    
    // Check result
    $stmt = $pdo->query("SELECT id, name, type, status FROM questionnaires");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nAll questionnaires after update:\n";
    foreach ($all as $q) {
        echo "{$q['id']}: {$q['name']} (type: '{$q['type']}', status: {$q['status']})\n";
    }
    
    // Test the filter function
    echo "\nTesting getAvailableQuestionnaires filter:\n";
    $stmt = $pdo->query("
        SELECT id, name, description, type, total_questions, status 
        FROM questionnaires 
        WHERE status = 'active' AND type != 'vark'
        ORDER BY type
    ");
    $filtered = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Questionnaires that will show in Available Questionnaires section:\n";
    foreach ($filtered as $q) {
        echo "- {$q['name']} (type: {$q['type']})\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
