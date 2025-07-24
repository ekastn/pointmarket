<?php
require_once 'includes/config.php';
$database = new Database();
$pdo = $database->getConnection();

echo "Checking VARK questions in database...\n";

// Check questionnaire table
$stmt = $pdo->query('SELECT id, name FROM questionnaires WHERE id = 3');
$questionnaire = $stmt->fetch();
if ($questionnaire) {
    echo "Found VARK questionnaire: " . $questionnaire['name'] . "\n";
} else {
    echo "VARK questionnaire (ID 3) not found!\n";
}

// Check questions
$stmt = $pdo->query('SELECT COUNT(*) as count FROM questionnaire_questions WHERE questionnaire_id = 3');
$count = $stmt->fetch()['count'];
echo "Found " . $count . " VARK questions\n";

// Check answer options
$stmt = $pdo->query('SELECT COUNT(*) as count FROM vark_answer_options');
$optCount = $stmt->fetch()['count'];
echo "Found " . $optCount . " VARK answer options\n";

// Show first few questions
$stmt = $pdo->query('SELECT question_number, question_text FROM questionnaire_questions WHERE questionnaire_id = 3 ORDER BY question_number LIMIT 3');
$questions = $stmt->fetchAll();
foreach ($questions as $q) {
    echo "Q" . $q['question_number'] . ": " . substr($q['question_text'], 0, 80) . "...\n";
}
?>
