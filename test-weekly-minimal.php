<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing minimal weekly evaluations page...\n";

try {
    require_once 'includes/config.php';
    
    // Check if user is logged in (without redirect)
    if (!isset($_SESSION['user_id'])) {
        echo "User not logged in. Please login first.\n";
        echo '<a href="login.php">Login here</a>';
        exit;
    }
    
    $user = getCurrentUser();
    echo "✓ User logged in: " . $user['name'] . "\n";
    
    if ($user['role'] !== 'siswa') {
        echo "User role: " . $user['role'] . " (not siswa)\n";
        exit;
    }
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Test the functions that might be causing issues
    echo "Testing updateOverdueEvaluations...\n";
    updateOverdueEvaluations($pdo);
    echo "✓ updateOverdueEvaluations completed\n";
    
    echo "Testing generateWeeklyEvaluations...\n";
    generateWeeklyEvaluations($pdo);
    echo "✓ generateWeeklyEvaluations completed\n";
    
    echo "Testing getPendingWeeklyEvaluations...\n";
    $pendingEvaluations = getPendingWeeklyEvaluations($user['id'], $pdo);
    echo "✓ Found " . count($pendingEvaluations) . " pending evaluations\n";
    
    echo "Testing getWeeklyEvaluationProgress...\n";
    $evaluationProgress = getWeeklyEvaluationProgress($user['id'], $pdo, 8);
    echo "✓ Found " . count($evaluationProgress) . " evaluation progress records\n";
    
    echo "All tests passed! The page should work.\n";
    echo '<br><a href="weekly-evaluations.php">Go to Weekly Evaluations</a>';
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
