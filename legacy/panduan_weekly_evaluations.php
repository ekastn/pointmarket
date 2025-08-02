<!DOCTYPE html>
<html>
<head>
    <title>Panduan Mengisi Weekly Evaluation Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
require_once 'includes/config.php';
requireLogin();
requireRole('siswa');

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();
?>

<div class="container mt-4">
    <h2>🔍 Panduan Mengisi Weekly Evaluation Data</h2>
    <p>Student: <?php echo htmlspecialchars($user['name']); ?></p>
    
    <div class="alert alert-info">
        <h5>📋 Mengapa Muncul "No evaluation data available"?</h5>
        <p>Pesan ini muncul karena belum ada data completed evaluations di tabel <code>questionnaire_results</code> untuk siswa ini.</p>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>📝 Cara Mengisi Data Weekly Evaluations (Normal Flow)</h5>
        </div>
        <div class="card-body">
            <ol>
                <li><strong>Akses Weekly Evaluations:</strong> Buka <a href="weekly-evaluations.php" target="_blank">weekly-evaluations.php</a></li>
                <li><strong>Lihat Pending Evaluations:</strong> Akan muncul questionnaire MSLQ/AMS yang due</li>
                <li><strong>Klik "Start Evaluation":</strong> Isi questionnaire dengan skala 1-7</li>
                <li><strong>Submit:</strong> Data akan masuk ke <code>questionnaire_results</code></li>
                <li><strong>Check Progress:</strong> Refresh halaman, akan muncul di "Weekly Evaluation Progress"</li>
            </ol>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5>⚡ Quick Fix untuk Testing</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>1. Generate & Set Due Date</h6>
                    <form method="post">
                        <button type="submit" name="generate_and_set" class="btn btn-warning">Generate Evaluations & Set Due Today</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h6>2. Create Sample Data</h6>
                    <form method="post">
                        <button type="submit" name="create_sample" class="btn btn-success">Create Sample Completed Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6>📊 Current Status</h6>
                </div>
                <div class="card-body">
                    <?php
                    $current_week = getCurrentWeekNumber();
                    $current_year = getCurrentYear();
                    echo "Current Week: $current_week<br>";
                    echo "Current Year: $current_year<br><br>";
                    
                    $pendingEvaluations = getPendingWeeklyEvaluations($user['id'], $pdo);
                    echo "Pending Evaluations: " . count($pendingEvaluations) . "<br>";
                    
                    $evaluationProgress = getWeeklyEvaluationProgress($user['id'], $pdo, 8);
                    echo "Progress Items: " . count($evaluationProgress) . "<br>";
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6>🎯 Next Steps</h6>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Click "Generate Evaluations & Set Due Today"</li>
                        <li>Go to <a href="weekly-evaluations.php">Weekly Evaluations</a></li>
                        <li>Complete pending questionnaires</li>
                        <li>Or use "Create Sample Data" for testing</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    if (isset($_POST['generate_and_set'])) {
        echo "<div class='alert alert-info mt-3'>";
        echo "<h6>Executing Generate & Set...</h6>";
        
        // Generate evaluations
        generateWeeklyEvaluations($pdo);
        echo "✅ Generated weekly evaluations<br>";
        
        // Set due dates to today
        $stmt = $pdo->prepare("UPDATE weekly_evaluations SET due_date = CURDATE() WHERE student_id = ? AND status = 'pending'");
        $stmt->execute([$user['id']]);
        $updated = $stmt->rowCount();
        echo "✅ Set $updated evaluations to be due today<br>";
        
        echo "<a href='weekly-evaluations.php' class='btn btn-primary mt-2'>Check Weekly Evaluations Page</a>";
        echo "</div>";
    }
    
    if (isset($_POST['create_sample'])) {
        echo "<div class='alert alert-success mt-3'>";
        echo "<h6>Creating Sample Data...</h6>";
        
        $current_week = getCurrentWeekNumber();
        $current_year = getCurrentYear();
        
        // Get questionnaires
        $stmt = $pdo->query("SELECT id, type FROM questionnaires WHERE type IN ('mslq', 'ams') AND status = 'active'");
        $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($questionnaires as $q) {
            // Create for current week - 1
            $prev_week = $current_week - 1;
            $prev_year = $current_year;
            if ($prev_week < 1) {
                $prev_week = 52;
                $prev_year--;
            }
            
            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM questionnaire_results WHERE student_id = ? AND questionnaire_id = ? AND week_number = ? AND year = ?");
            $stmt->execute([$user['id'], $q['id'], $prev_week, $prev_year]);
            
            if (!$stmt->fetch()) {
                // Create sample answers
                $sample_answers = [];
                for ($i = 1; $i <= 20; $i++) {
                    $sample_answers[$i] = rand(4, 7);
                }
                $total_score = array_sum($sample_answers) / count($sample_answers);
                
                // Insert result
                $stmt = $pdo->prepare("INSERT INTO questionnaire_results (student_id, questionnaire_id, answers, total_score, week_number, year, completed_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$user['id'], $q['id'], json_encode($sample_answers), $total_score, $prev_week, $prev_year]);
                
                // Update weekly_evaluation
                $stmt = $pdo->prepare("UPDATE weekly_evaluations SET status = 'completed', completed_at = NOW() WHERE student_id = ? AND questionnaire_id = ? AND week_number = ? AND year = ?");
                $stmt->execute([$user['id'], $q['id'], $prev_week, $prev_year]);
                
                echo "✅ Created {$q['type']} result for week $prev_week/$prev_year<br>";
            }
        }
        
        echo "<a href='weekly-evaluations.php' class='btn btn-primary mt-2'>Check Weekly Evaluations Page</a>";
        echo "</div>";
    }
    ?>
</div>
</body>
</html>
