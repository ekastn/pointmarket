<?php
require_once 'includes/config.php';
$database = new Database();
$pdo = $database->getConnection();

echo "Checking vark_results table structure...\n";

try {
    $stmt = $pdo->query('DESCRIBE vark_results');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Table columns:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
