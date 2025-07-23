<?php
require_once 'includes/config.php';
$database = new Database();
$pdo = $database->getConnection();

echo "Testing VARK result saving...\n";

// Test data
$studentId = 1; // Assuming student ID 1 exists
$testAnswers = [
    1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd',
    5 => 'a', 6 => 'b', 7 => 'c', 8 => 'd',
    9 => 'a', 10 => 'b', 11 => 'c', 12 => 'd',
    13 => 'a', 14 => 'b', 15 => 'c', 16 => 'd'
];

echo "Testing calculateVARKScore...\n";
$varkResult = calculateVARKScore($testAnswers, $pdo);

if ($varkResult) {
    echo "✓ VARK calculation successful:\n";
    echo "  Scores: " . json_encode($varkResult['scores']) . "\n";
    echo "  Dominant style: " . $varkResult['dominant_style'] . "\n";
    echo "  Learning preference: " . $varkResult['learning_preference'] . "\n";
    
    echo "\nTesting saveVARKResult...\n";
    $resultId = saveVARKResult(
        $studentId,
        $varkResult['scores'],
        $varkResult['dominant_style'],
        $varkResult['learning_preference'],
        $testAnswers,
        $pdo
    );
    
    if ($resultId) {
        echo "✓ VARK result saved successfully with ID: " . $resultId . "\n";
        
        // Clean up test data
        $stmt = $pdo->prepare("DELETE FROM vark_results WHERE id = ?");
        $stmt->execute([$resultId]);
        echo "✓ Test data cleaned up\n";
    } else {
        echo "❌ Failed to save VARK result\n";
    }
} else {
    echo "❌ Failed to calculate VARK score\n";
}
?>
