<?php
/**
 * NLP Setup Script
 * 
 * This script creates the necessary database tables for NLP functionality
 * if they don't exist.
 */

require_once 'includes/config.php';

function setupNLPDatabase() {
    try {
        $database = new Database();
        $pdo = $database->getConnection();
        
        // Create nlp_analysis_results table
        $sql = "
        CREATE TABLE IF NOT EXISTS `nlp_analysis_results` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `student_id` int(11) NOT NULL,
          `assignment_id` int(11) DEFAULT NULL,
          `quiz_id` int(11) DEFAULT NULL,
          `original_text` text NOT NULL,
          `clean_text` text DEFAULT NULL,
          `word_count` int(11) DEFAULT 0,
          `sentence_count` int(11) DEFAULT 0,
          `total_score` decimal(5,2) DEFAULT 0.00,
          `grammar_score` decimal(5,2) DEFAULT 0.00,
          `keyword_score` decimal(5,2) DEFAULT 0.00,
          `structure_score` decimal(5,2) DEFAULT 0.00,
          `readability_score` decimal(5,2) DEFAULT 0.00,
          `sentiment_score` decimal(5,2) DEFAULT 0.00,
          `complexity_score` decimal(5,2) DEFAULT 0.00,
          `feedback` json DEFAULT NULL,
          `personalized_feedback` json DEFAULT NULL,
          `context_type` varchar(50) DEFAULT 'assignment',
          `analysis_version` varchar(10) DEFAULT '1.0',
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `student_id` (`student_id`),
          KEY `assignment_id` (`assignment_id`),
          KEY `quiz_id` (`quiz_id`),
          KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        
        // Create nlp_keywords table
        $sql = "
        CREATE TABLE IF NOT EXISTS `nlp_keywords` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `context` varchar(100) NOT NULL,
          `keyword` varchar(255) NOT NULL,
          `weight` decimal(3,2) DEFAULT 1.00,
          `category` varchar(50) DEFAULT 'general',
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `context_keyword` (`context`, `keyword`),
          KEY `context` (`context`),
          KEY `category` (`category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        
        // Create nlp_feedback_templates table
        $sql = "
        CREATE TABLE IF NOT EXISTS `nlp_feedback_templates` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `score_range_min` decimal(5,2) NOT NULL,
          `score_range_max` decimal(5,2) NOT NULL,
          `category` varchar(50) NOT NULL,
          `feedback_text` text NOT NULL,
          `suggestions` json DEFAULT NULL,
          `vark_type` varchar(20) DEFAULT NULL,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `score_range` (`score_range_min`, `score_range_max`),
          KEY `category` (`category`),
          KEY `vark_type` (`vark_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        
        // Insert some sample keywords
        $keywords = [
            ['assignment', 'teknologi', 1.5, 'concept'],
            ['assignment', 'pembelajaran', 1.3, 'concept'],
            ['assignment', 'pendidikan', 1.2, 'concept'],
            ['assignment', 'analisis', 1.4, 'analysis'],
            ['assignment', 'evaluasi', 1.4, 'analysis'],
            ['assignment', 'implementasi', 1.3, 'implementation'],
            ['matematik', 'rumus', 1.5, 'concept'],
            ['matematik', 'persamaan', 1.4, 'concept'],
            ['matematik', 'integral', 1.6, 'advanced'],
            ['matematik', 'diferensial', 1.6, 'advanced'],
            ['fisika', 'gaya', 1.4, 'concept'],
            ['fisika', 'energi', 1.5, 'concept'],
            ['fisika', 'momentum', 1.5, 'concept'],
            ['kimia', 'reaksi', 1.5, 'concept'],
            ['kimia', 'molekul', 1.4, 'concept'],
            ['biologi', 'sel', 1.4, 'concept'],
            ['biologi', 'organisme', 1.5, 'concept']
        ];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO nlp_keywords (context, keyword, weight, category) VALUES (?, ?, ?, ?)");
        foreach ($keywords as $keyword) {
            $stmt->execute($keyword);
        }
        
        // Insert sample feedback templates
        $feedbacks = [
            [80, 100, 'overall', 'Excellent work! Your analysis demonstrates strong understanding and clear communication.', '["Keep up the excellent work!", "Consider exploring advanced topics"]', null],
            [60, 79, 'overall', 'Good work! Your analysis shows understanding but could benefit from more detail.', '["Add more specific examples", "Improve structure and flow"]', null],
            [40, 59, 'overall', 'Your analysis shows basic understanding but needs improvement in several areas.', '["Review key concepts", "Improve grammar and structure", "Add more analysis"]', null],
            [0, 39, 'overall', 'Your analysis needs significant improvement. Please review the material and try again.', '["Study the material more carefully", "Improve writing skills", "Seek help from instructor"]', null]
        ];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO nlp_feedback_templates (score_range_min, score_range_max, category, feedback_text, suggestions, vark_type) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($feedbacks as $feedback) {
            $stmt->execute($feedback);
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("NLP Setup Error: " . $e->getMessage());
        return false;
    }
}

// Auto-setup if accessed directly
if (basename($_SERVER['PHP_SELF']) == 'setup-nlp.php') {
    header('Content-Type: application/json');
    
    if (setupNLPDatabase()) {
        echo json_encode([
            'success' => true,
            'message' => 'NLP database tables created successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create NLP database tables'
        ]);
    }
}
?>
