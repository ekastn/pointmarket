<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

echo "<h2>📊 Cek Data Questionnaire Yang Sudah Ada</h2>";

// Check existing data in questionnaire_results
echo "<h3>Data di Tabel questionnaire_results:</h3>";
$stmt = $pdo->query("
    SELECT qr.*, q.name, q.type 
    FROM questionnaire_results qr 
    JOIN questionnaires q ON qr.questionnaire_id = q.id 
    ORDER BY qr.completed_at DESC
");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    echo "❌ Tidak ada data questionnaire results.<br><br>";
    
    // Let's create some sample data for testing
    echo "<h3>🎯 Membuat Data Sample untuk Testing:</h3>";
    
    // Get questionnaire IDs
    $stmt = $pdo->query("SELECT id, name, type FROM questionnaires WHERE type IN ('mslq', 'ams')");
    $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($questionnaires as $q) {
        // Create sample answers
        $sample_answers = [];
        for ($i = 1; $i <= ($q['type'] === 'mslq' ? 81 : 28); $i++) {
            $sample_answers[$i] = rand(4, 7); // Random score 4-7
        }
        $total_score = array_sum($sample_answers) / count($sample_answers);
        
        // Insert sample data
        $stmt = $pdo->prepare("
            INSERT INTO questionnaire_results 
            (student_id, questionnaire_id, answers, total_score, completed_at) 
            VALUES (?, ?, ?, ?, NOW() - INTERVAL 1 DAY)
        ");
        $stmt->execute([
            $user['id'],
            $q['id'],
            json_encode($sample_answers),
            $total_score
        ]);
        
        echo "✅ Created sample data for {$q['name']} (score: " . number_format($total_score, 2) . ")<br>";
    }
    
    echo "<br>✅ Sample data created! <a href='questionnaire-progress.php'>Check Progress Page</a><br><br>";
    
} else {
    echo "✅ Found " . count($results) . " questionnaire results:<br>";
    foreach ($results as $result) {
        echo "- {$result['name']} (Student ID: {$result['student_id']}) - Score: " . number_format($result['total_score'], 2) . " - Date: {$result['completed_at']}<br>";
    }
}

// Test the new functions
echo "<h3>Test New Functions:</h3>";

// Test getAvailableQuestionnaires
function getAvailableQuestionnaires($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT id, name, description, type, total_questions, status 
            FROM questionnaires 
            WHERE status = 'active' AND type IN ('mslq', 'ams')
            ORDER BY type
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting questionnaires: " . $e->getMessage());
        return [];
    }
}

$questionnaires = getAvailableQuestionnaires($pdo);
echo "Available questionnaires: " . count($questionnaires) . "<br>";
foreach ($questionnaires as $q) {
    echo "- {$q['name']} ({$q['type']})<br>";
}

// Test getQuestionnaireHistory
function getQuestionnaireHistory($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                qr.id,
                qr.total_score,
                qr.completed_at,
                q.name as questionnaire_name,
                q.type as questionnaire_type,
                q.description as questionnaire_description
            FROM questionnaire_results qr
            JOIN questionnaires q ON qr.questionnaire_id = q.id
            WHERE qr.student_id = ? AND q.type IN ('mslq', 'ams')
            ORDER BY qr.completed_at DESC
            LIMIT 20
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting questionnaire history: " . $e->getMessage());
        return [];
    }
}

echo "<br>";
$history = getQuestionnaireHistory($user['id'], $pdo);
echo "History for student {$user['id']}: " . count($history) . " items<br>";
foreach ($history as $item) {
    echo "- {$item['questionnaire_name']} - Score: " . number_format($item['total_score'], 2) . " - {$item['completed_at']}<br>";
}

// Test getQuestionnaireStats
function getQuestionnaireStats($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                q.type,
                q.name,
                COUNT(qr.id) as total_completed,
                AVG(qr.total_score) as average_score,
                MAX(qr.total_score) as best_score,
                MIN(qr.total_score) as lowest_score,
                MAX(qr.completed_at) as last_completed
            FROM questionnaires q
            LEFT JOIN questionnaire_results qr ON (q.id = qr.questionnaire_id AND qr.student_id = ?)
            WHERE q.status = 'active' AND q.type IN ('mslq', 'ams')
            GROUP BY q.id, q.type, q.name
            ORDER BY q.type
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting questionnaire stats: " . $e->getMessage());
        return [];
    }
}

echo "<br>";
$stats = getQuestionnaireStats($user['id'], $pdo);
echo "Stats for student {$user['id']}:<br>";
foreach ($stats as $stat) {
    echo "- {$stat['name']}: {$stat['total_completed']} completed, avg score: " . 
         ($stat['average_score'] ? number_format($stat['average_score'], 2) : 'N/A') . "<br>";
}

echo "<br><a href='questionnaire-progress.php' class='btn btn-primary'>🎯 Go to Progress Page</a>";
?>

<style>
.btn { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; text-decoration: none; display: inline-block; margin: 5px; }
</style>
