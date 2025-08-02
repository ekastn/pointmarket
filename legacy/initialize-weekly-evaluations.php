<?php
/**
 * Initialize Weekly Evaluations System
 * Run this script once to set up the weekly evaluation system
 */

require_once 'includes/config.php';

$database = new Database();
$pdo = $database->getConnection();

echo "<h2>Initializing POINTMARKET Weekly Evaluation System</h2>\n";

try {
    $pdo->beginTransaction();
    
    // Check if tables exist, create if they don't
    echo "<h3>Checking Database Structure...</h3>\n";
    
    // Check if weekly_evaluations table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'weekly_evaluations'");
    if ($stmt->rowCount() == 0) {
        echo "Creating weekly_evaluations table...<br>\n";
        
        $sql = "
        CREATE TABLE `weekly_evaluations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `student_id` int(11) NOT NULL,
          `questionnaire_id` int(11) NOT NULL,
          `week_number` int(11) NOT NULL,
          `year` int(11) NOT NULL,
          `due_date` date NOT NULL,
          `status` enum('pending','completed','overdue') DEFAULT 'pending',
          `reminder_sent` boolean DEFAULT FALSE,
          `completed_at` timestamp NULL DEFAULT NULL,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `student_questionnaire_week` (`student_id`, `questionnaire_id`, `week_number`, `year`),
          KEY `student_id` (`student_id`),
          KEY `questionnaire_id` (`questionnaire_id`),
          KEY `due_date` (`due_date`),
          FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($sql);
        echo "✓ weekly_evaluations table created<br>\n";
    } else {
        echo "✓ weekly_evaluations table already exists<br>\n";
    }
    
    // Check if questionnaire_questions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'questionnaire_questions'");
    if ($stmt->rowCount() == 0) {
        echo "Creating questionnaire_questions table...<br>\n";
        
        $sql = "
        CREATE TABLE `questionnaire_questions` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `questionnaire_id` int(11) NOT NULL,
          `question_number` int(11) NOT NULL,
          `question_text` text NOT NULL,
          `subscale` varchar(100) DEFAULT NULL,
          `reverse_scored` boolean DEFAULT FALSE,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `questionnaire_id` (`questionnaire_id`),
          FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($sql);
        echo "✓ questionnaire_questions table created<br>\n";
    } else {
        echo "✓ questionnaire_questions table already exists<br>\n";
    }
    
    // Check if activity_log table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'activity_log'");
    if ($stmt->rowCount() == 0) {
        echo "Creating activity_log table...<br>\n";
        
        $sql = "
        CREATE TABLE `activity_log` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) DEFAULT NULL,
          `action` varchar(100) NOT NULL,
          `description` text,
          `ip_address` varchar(45) DEFAULT NULL,
          `user_agent` text,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          KEY `created_at` (`created_at`),
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($sql);
        echo "✓ activity_log table created<br>\n";
    } else {
        echo "✓ activity_log table already exists<br>\n";
    }
    
    // Update questionnaire_results table to include week tracking
    echo "Updating questionnaire_results table...<br>\n";
    
    // Check if columns exist before adding them
    $stmt = $pdo->query("SHOW COLUMNS FROM questionnaire_results LIKE 'week_number'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `questionnaire_results` ADD COLUMN `week_number` INT NOT NULL DEFAULT 1");
        echo "✓ Added week_number column<br>\n";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM questionnaire_results LIKE 'year'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `questionnaire_results` ADD COLUMN `year` INT NOT NULL DEFAULT YEAR(NOW())");
        echo "✓ Added year column<br>\n";
    }
    
    // Add index for better performance
    $stmt = $pdo->query("SHOW INDEX FROM questionnaire_results WHERE Key_name = 'idx_student_week'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `questionnaire_results` ADD INDEX `idx_student_week` (`student_id`, `questionnaire_id`, `week_number`, `year`)");
        echo "✓ Added performance index<br>\n";
    }
    
    // Insert MSLQ questions if they don't exist
    echo "<h3>Populating MSLQ Questions...</h3>\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM questionnaire_questions WHERE questionnaire_id = 1");
    $stmt->execute();
    $mslq_count = $stmt->fetchColumn();
    
    if ($mslq_count == 0) {
        $mslq_questions = [
            [1, 'Dalam sebuah kelas seperti ini, saya lebih suka materi pembelajaran yang benar-benar menantang sehingga saya dapat belajar hal-hal baru.', 'Intrinsic Goal Orientation', false],
            [2, 'Dalam sebuah kelas seperti ini, saya lebih suka materi pembelajaran yang menggugah rasa ingin tahu saya, meskipun sulit untuk dipelajari.', 'Intrinsic Goal Orientation', false],
            [3, 'Hal yang paling memuaskan bagi saya dalam kelas ini adalah mencoba memahami konten selengkap mungkin.', 'Intrinsic Goal Orientation', false],
            [4, 'Ketika saya berkesempatan dalam kelas ini, saya memilih tugas yang dapat saya pelajari, bahkan jika itu tidak menjamin nilai yang baik.', 'Intrinsic Goal Orientation', false],
            [5, 'Mendapatkan nilai yang baik dalam kelas ini adalah hal yang paling memuaskan bagi saya saat ini.', 'Extrinsic Goal Orientation', false],
            [6, 'Hal yang paling penting bagi saya sekarang adalah meningkatkan nilai rata-rata saya secara keseluruhan, jadi perhatian utama saya dalam kelas ini adalah mendapatkan nilai yang baik.', 'Extrinsic Goal Orientation', false],
            [7, 'Jika saya dapat, saya ingin mendapat nilai yang lebih baik dalam kelas ini daripada kebanyakan siswa lain.', 'Extrinsic Goal Orientation', false],
            [8, 'Saya ingin berbuat baik dalam kelas ini karena penting untuk menunjukkan kemampuan saya kepada keluarga, teman, atasan, atau orang lain.', 'Extrinsic Goal Orientation', false],
            [9, 'Saya pikir saya akan dapat menggunakan apa yang saya pelajari dalam kelas ini di kelas lain.', 'Task Value', false],
            [10, 'Penting bagi saya untuk mempelajari materi dalam kelas ini.', 'Task Value', false],
            [11, 'Saya sangat tertarik dengan bidang konten kelas ini.', 'Task Value', false],
            [12, 'Saya pikir materi kelas ini berguna untuk dipelajari.', 'Task Value', false],
            [13, 'Jika saya belajar dengan cara yang tepat, maka saya akan dapat mempelajari materi dalam kelas ini.', 'Control of Learning Beliefs', false],
            [14, 'Terserah pada saya apakah saya mempelajari materi dengan baik dalam kelas ini atau tidak.', 'Control of Learning Beliefs', false],
            [15, 'Jika saya mencoba cukup keras, maka saya akan memahami materi kelas.', 'Control of Learning Beliefs', false],
            [16, 'Jika saya tidak mempelajari materi kelas dengan baik, itu karena saya tidak mencoba cukup keras.', 'Control of Learning Beliefs', false],
            [17, 'Saya yakin dapat memahami konsep yang paling sulit yang disajikan oleh instruktur dalam kelas ini.', 'Self-Efficacy for Learning and Performance', false],
            [18, 'Saya yakin dapat memahami materi yang paling rumit yang disajikan dalam bacaan untuk kelas ini.', 'Self-Efficacy for Learning and Performance', false],
            [19, 'Saya yakin dapat menguasai keterampilan yang diajarkan dalam kelas ini.', 'Self-Efficacy for Learning and Performance', false],
            [20, 'Saya yakin dapat berbuat baik dalam tugas dan tes dalam kelas ini.', 'Self-Efficacy for Learning and Performance', false]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO questionnaire_questions (questionnaire_id, question_number, question_text, subscale, reverse_scored) VALUES (1, ?, ?, ?, ?)");
        
        foreach ($mslq_questions as $question) {
            $stmt->execute($question);
        }
        
        echo "✓ Inserted " . count($mslq_questions) . " MSLQ questions<br>\n";
    } else {
        echo "✓ MSLQ questions already exist ($mslq_count questions)<br>\n";
    }
    
    // Insert AMS questions if they don't exist
    echo "<h3>Populating AMS Questions...</h3>\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM questionnaire_questions WHERE questionnaire_id = 2");
    $stmt->execute();
    $ams_count = $stmt->fetchColumn();
    
    if ($ams_count == 0) {
        $ams_questions = [
            [1, 'Karena saya merasakan kepuasan saat menemukan hal-hal baru yang tidak pernah saya lihat atau ketahui sebelumnya.', 'Intrinsic Motivation - To Know', false],
            [2, 'Karena saya merasakan kepuasan saat membaca tentang berbagai topik menarik.', 'Intrinsic Motivation - To Know', false],
            [3, 'Karena saya merasakan kepuasan saat saya merasakan diri saya benar-benar terlibat dalam apa yang saya lakukan.', 'Intrinsic Motivation - To Experience Stimulation', false],
            [4, 'Karena saya merasakan kepuasan saat saya dapat berkomunikasi dengan baik dalam bahasa Inggris.', 'Intrinsic Motivation - To Experience Stimulation', false],
            [5, 'Karena menurut saya sekolah menengah akan membantu saya membuat pilihan karir yang lebih baik.', 'Extrinsic Motivation - Identified', false],
            [6, 'Karena akan membantu saya membuat pilihan yang lebih baik mengenai orientasi karir saya.', 'Extrinsic Motivation - Identified', false],
            [7, 'Karena saya ingin menunjukkan kepada diri saya sendiri bahwa saya dapat berhasil dalam studi saya.', 'Extrinsic Motivation - Introjected', false],
            [8, 'Karena saya ingin menunjukkan kepada diri saya sendiri bahwa saya adalah orang yang cerdas.', 'Extrinsic Motivation - Introjected', false],
            [9, 'Saya tidak tahu; saya tidak dapat memahami apa yang saya lakukan di sekolah.', 'Amotivation', false],
            [10, 'Jujur, saya tidak tahu; saya benar-benar merasa bahwa saya membuang-buang waktu di sekolah.', 'Amotivation', false]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO questionnaire_questions (questionnaire_id, question_number, question_text, subscale, reverse_scored) VALUES (2, ?, ?, ?, ?)");
        
        foreach ($ams_questions as $question) {
            $stmt->execute($question);
        }
        
        echo "✓ Inserted " . count($ams_questions) . " AMS questions<br>\n";
    } else {
        echo "✓ AMS questions already exist ($ams_count questions)<br>\n";
    }
    
    // Insert VARK questionnaire if not exists
    echo "<p>Inserting VARK questionnaire...</p>";
    $stmt = $pdo->prepare("
        INSERT INTO questionnaires (id, name, description, type, total_questions, status) 
        VALUES (3, 'VARK Learning Style Assessment', 'Kuesioner untuk mendeteksi gaya belajar Visual, Auditory, Reading/Writing, dan Kinesthetic', 'vark', 16, 'active')
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            description = VALUES(description),
            total_questions = VALUES(total_questions)
    ");
    $stmt->execute();
    echo "<p style='color: green;'>✓ VARK questionnaire inserted/updated</p>";
    
    // Generate initial weekly evaluations for current week
    echo "<h3>Generating Initial Weekly Evaluations...</h3>\n";
    
    $current_week = getCurrentWeekNumber();
    $current_year = getCurrentYear();
    
    // Get all students
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'siswa'");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all questionnaires
    $stmt = $pdo->query("SELECT id FROM questionnaires WHERE status = 'active'");
    $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($students) && !empty($questionnaires)) {
        $insertStmt = $pdo->prepare("
            INSERT IGNORE INTO weekly_evaluations 
            (student_id, questionnaire_id, week_number, year, due_date) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $due_date = date('Y-m-d', strtotime('next sunday'));
        $count = 0;
        
        foreach ($students as $student) {
            foreach ($questionnaires as $questionnaire) {
                $insertStmt->execute([
                    $student['id'],
                    $questionnaire['id'],
                    $current_week,
                    $current_year,
                    $due_date
                ]);
                $count++;
            }
        }
        
        echo "✓ Generated $count weekly evaluation entries for week $current_week/$current_year<br>\n";
        echo "✓ Due date: $due_date<br>\n";
    } else {
        echo "⚠ No students or questionnaires found. Skipping evaluation generation.<br>\n";
    }
    
    // Log initialization
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action, description, ip_address) 
        VALUES (NULL, 'system_init', 'Weekly evaluation system initialized', ?)
    ");
    $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? 'localhost']);
    
    $pdo->commit();
    
    echo "<h3>✅ Initialization Complete!</h3>\n";
    echo "<p><strong>Next steps:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Students can access weekly evaluations at: <a href='weekly-evaluations.php'>weekly-evaluations.php</a></li>\n";
    echo "<li>Teachers can monitor progress at: <a href='teacher-evaluation-monitoring.php'>teacher-evaluation-monitoring.php</a></li>\n";
    echo "<li>The system will automatically generate new evaluations each week</li>\n";
    echo "<li>Overdue evaluations will be marked automatically</li>\n";
    echo "</ul>\n";
    
    echo "<p><strong>System Information:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Current Week: $current_week</li>\n";
    echo "<li>Current Year: $current_year</li>\n";
    echo "<li>Students: " . count($students) . "</li>\n";
    echo "<li>Active Questionnaires: " . count($questionnaires) . "</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<h3>❌ Error during initialization:</h3>\n";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>\n";
    echo "<p>Please check your database connection and table permissions.</p>\n";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
ul { margin-left: 20px; }
a { color: #0066cc; }
</style>
