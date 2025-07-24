<?php
require_once 'includes/config.php';

$database = new Database();
$pdo = $database->getConnection();

try {
    // Check table structure
    echo "Table structure:\n";
    $stmt = $pdo->query("DESCRIBE questionnaires");
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($structure as $column) {
        echo "{$column['Field']}: {$column['Type']} (Null: {$column['Null']}, Default: {$column['Default']})\n";
    }
    
    // Check current VARK questionnaire
    $stmt = $pdo->query("SELECT id, name, type, status FROM questionnaires WHERE id = 3");
    $vark = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nBefore update:\n";
    print_r($vark);
    
    // Try setting type to empty string first, then vark
    $stmt = $pdo->prepare("UPDATE questionnaires SET type = NULL WHERE id = 3");
    $result1 = $stmt->execute();
    echo "\nSet to NULL result: " . ($result1 ? "Success" : "Failed") . "\n";
    
    $stmt = $pdo->prepare("UPDATE questionnaires SET type = 'vark' WHERE id = 3");
    $result2 = $stmt->execute();
    echo "Set to 'vark' result: " . ($result2 ? "Success" : "Failed") . "\n";
    
    // Check after update
    $stmt = $pdo->query("SELECT id, name, type, status FROM questionnaires WHERE id = 3");
    $vark = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nAfter update:\n";
    print_r($vark);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
