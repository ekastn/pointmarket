<?php
// Simple test for NLP API
require_once 'includes/config.php';

// Start session for testing
startSession();

// Set a test user session if not exists
if (!isLoggedIn()) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'test';
    $_SESSION['name'] = 'Test User';
    $_SESSION['email'] = 'test@example.com';
    $_SESSION['role'] = 'student';
}

echo "<h2>NLP API Test</h2>";

// Test 1: Check if API responds
echo "<h3>Test 1: API Response</h3>";
$apiUrl = 'api/nlp-analysis.php?test=1';
$response = file_get_contents($apiUrl);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test 2: Check if statistics work
echo "<h3>Test 2: Statistics</h3>";
$apiUrl = 'api/nlp-analysis.php?action=statistics';
$response = file_get_contents($apiUrl);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test 3: Check if NLP model exists
echo "<h3>Test 3: NLP Model</h3>";
if (file_exists('includes/nlp-model.php')) {
    echo "✓ NLP Model file exists<br>";
    
    try {
        require_once 'includes/nlp-model.php';
        $database = new Database();
        $pdo = $database->getConnection();
        $nlpModel = new NLPModel($pdo);
        echo "✓ NLP Model can be instantiated<br>";
        
        // Test basic analysis
        $result = $nlpModel->analyzeText("This is a test text for NLP analysis.", "assignment", 1);
        echo "✓ Basic analysis works<br>";
        echo "<pre>" . htmlspecialchars(print_r($result, true)) . "</pre>";
        
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ NLP Model file missing<br>";
}

// Test 4: Check database tables
echo "<h3>Test 4: Database Tables</h3>";
try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $tables = ['nlp_analysis_results', 'nlp_keywords', 'nlp_feedback_templates'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists<br>";
        } else {
            echo "✗ Table '$table' missing<br>";
        }
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}
?>
