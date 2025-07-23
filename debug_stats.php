<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die('Please login first');
}

require_once 'includes/config.php';

$database = new Database();
$pdo = $database->getConnection();
$student_id = $_SESSION['user_id'];

echo "<h2>Debugging Questionnaire Stats</h2>";
echo "Student ID: $student_id<br><br>";

// Check VARK table
echo "<h3>1. Check VARK Results Table:</h3>";
$stmt = $pdo->prepare("SELECT * FROM vark_results WHERE student_id = ?");
$stmt->execute([$student_id]);
$varkData = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "VARK Records found: " . count($varkData) . "<br>";
if (!empty($varkData)) {
    echo "<pre>";
    print_r($varkData);
    echo "</pre>";
}

// Check questionnaire_results table
echo "<h3>2. Check Questionnaire Results Table:</h3>";
$stmt = $pdo->prepare("SELECT qr.*, q.name, q.type FROM questionnaire_results qr JOIN questionnaires q ON qr.questionnaire_id = q.id WHERE qr.student_id = ?");
$stmt->execute([$student_id]);
$questData = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Questionnaire Records found: " . count($questData) . "<br>";
if (!empty($questData)) {
    echo "<pre>";
    print_r($questData);
    echo "</pre>";
}

// Check questionnaires table
echo "<h3>3. Check Questionnaires Table:</h3>";
$stmt = $pdo->query("SELECT id, name, type, status FROM questionnaires");
$questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($questionnaires);
echo "</pre>";

// Test VARK stats query
echo "<h3>4. Test VARK Stats Query:</h3>";
$stmt = $pdo->prepare("SELECT COUNT(*) as total_completed, MAX(completed_at) as last_completed FROM vark_results WHERE student_id = ?");
$stmt->execute([$student_id]);
$varkStats = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($varkStats);
echo "</pre>";

// Test regular questionnaire stats query
echo "<h3>5. Test Regular Questionnaire Stats Query:</h3>";
$stmt = $pdo->prepare("
    SELECT 
        q.type,
        COUNT(qr.id) as total_completed,
        AVG(qr.total_score) as average_score,
        MAX(qr.total_score) as best_score,
        MIN(qr.total_score) as lowest_score,
        MAX(qr.completed_at) as last_completed
    FROM questionnaires q
    LEFT JOIN questionnaire_results qr ON (q.id = qr.questionnaire_id AND qr.student_id = ?)
    WHERE q.status = 'active' AND q.type IN ('mslq', 'ams')
    GROUP BY q.id, q.type
    ORDER BY q.type
");
$stmt->execute([$student_id]);
$regularStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($regularStats);
echo "</pre>";
?>
