<?php
require_once 'includes/config.php';
$database = new Database();
$pdo = $database->getConnection();

// Simulate user login (get first student)
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'siswa' LIMIT 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "No student user found!\n";
    exit;
}

echo "Testing VARK assessment logic for user: " . $user['name'] . "\n";
echo "User ID: " . $user['id'] . "\n\n";

// Test getVARKQuestions
$varkQuestions = getVARKQuestions($pdo);
echo "VARK questions count: " . count($varkQuestions) . "\n";

// Check if show_result is set (it shouldn't be on initial load)
$show_result = false; // This is what happens on initial page load
echo "show_result is set: " . (isset($show_result) ? 'YES' : 'NO') . "\n";
echo "show_result value: " . ($show_result ? 'TRUE' : 'FALSE') . "\n";

// Check existing result
$existingResult = getStudentVARKResult($user['id'], $pdo);
echo "Existing VARK result: " . ($existingResult ? 'YES' : 'NO') . "\n";

// Logic check
if (isset($show_result) && $show_result) {
    echo "CONDITION: Will show result display\n";
} else {
    echo "CONDITION: Will show questionnaire form\n";
    
    echo "First few questions:\n";
    $count = 0;
    foreach ($varkQuestions as $question) {
        if ($count < 3) {
            echo "Q{$question['question_number']}: " . substr($question['question_text'], 0, 50) . "...\n";
            echo "  Options: " . count($question['options']) . "\n";
            $count++;
        }
    }
}
?>
