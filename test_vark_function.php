<?php
require_once 'includes/config.php';
$database = new Database();
$pdo = $database->getConnection();

echo "Testing getVARKQuestions function...\n";

$questions = getVARKQuestions($pdo);

echo "Function returned " . count($questions) . " questions\n";

if (count($questions) > 0) {
    echo "First question structure:\n";
    var_dump($questions[1]);
    echo "\nSecond question structure:\n";
    var_dump($questions[2]);
} else {
    echo "No questions found! Checking raw query...\n";
    
    $stmt = $pdo->prepare("
        SELECT qq.id, qq.question_number, qq.question_text, qq.subscale,
               vo.option_letter, vo.option_text, vo.learning_style
        FROM questionnaire_questions qq
        LEFT JOIN vark_answer_options vo ON qq.id = vo.question_id
        WHERE qq.questionnaire_id = 3
        ORDER BY qq.question_number, vo.option_letter
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Raw query returned " . count($results) . " rows\n";
    if (count($results) > 0) {
        echo "First few rows:\n";
        for ($i = 0; $i < min(4, count($results)); $i++) {
            echo "Row $i: Q{$results[$i]['question_number']} - {$results[$i]['option_letter']} - {$results[$i]['learning_style']}\n";
        }
    }
}
?>
