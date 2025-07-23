<?php
require_once 'includes/config.php';
$database = new Database();
$pdo = $database->getConnection();

echo "Running VARK database setup...\n";

try {
    // Read and execute the SQL file
    $sql = file_get_contents('database/vark_data.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            echo "Executing: " . substr($statement, 0, 60) . "...\n";
            $pdo->exec($statement);
        }
    }
    
    echo "VARK setup completed successfully!\n";
    
    // Verify the data
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM questionnaire_questions WHERE questionnaire_id = 3');
    $questionCount = $stmt->fetch()['count'];
    echo "Found " . $questionCount . " VARK questions\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM vark_answer_options');
    $optionCount = $stmt->fetch()['count'];
    echo "Found " . $optionCount . " VARK answer options\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
