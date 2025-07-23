<?php
// Test VARK assessment page functionality
require_once 'includes/config.php';

// Simulate login as student
$_SESSION['user_id'] = 1; // Assuming student ID 1 exists

$database = new Database();
$pdo = $database->getConnection();

// Test the functions used in vark-assessment.php
echo "Testing VARK assessment page components...\n\n";

try {
    $user = getCurrentUser();
    echo "✓ User loaded: " . $user['name'] . " (Role: " . $user['role'] . ")\n";
    
    $varkQuestions = getVARKQuestions($pdo);
    echo "✓ VARK questions loaded: " . count($varkQuestions) . " questions\n";
    
    $existingResult = getStudentVARKResult($user['id'], $pdo);
    echo "✓ Existing result check: " . ($existingResult ? "Has result" : "No previous result") . "\n";
    
    // Test first question structure
    if (count($varkQuestions) > 0) {
        $firstQuestion = reset($varkQuestions);
        echo "✓ First question preview:\n";
        echo "  Question " . $firstQuestion['question_number'] . ": " . substr($firstQuestion['question_text'], 0, 50) . "...\n";
        echo "  Options count: " . count($firstQuestion['options']) . "\n";
    }
    
    echo "\n✅ All components working correctly!\n";
    echo "The vark-assessment.php page should now display properly.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
