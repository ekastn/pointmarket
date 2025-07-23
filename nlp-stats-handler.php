<?php
/**
 * Fallback NLP API handler within the demo page
 */

// Only handle if it's an AJAX request
if (isset($_GET['nlp_action']) && $_GET['nlp_action'] === 'statistics') {
    require_once 'includes/config.php';
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized'
        ]);
        exit;
    }
    
    $user = getCurrentUser();
    
    try {
        $database = new Database();
        $pdo = $database->getConnection();
        
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'nlp_analysis_results'");
        if ($stmt->rowCount() == 0) {
            // No table, return empty stats
            echo json_encode([
                'success' => true,
                'data' => [
                    'overall' => [
                        'total_analyses' => 0,
                        'average_score' => 0,
                        'best_score' => 0,
                        'worst_score' => 0,
                        'avg_grammar' => 0,
                        'avg_keyword' => 0,
                        'avg_structure' => 0
                    ],
                    'daily' => [],
                    'generated_at' => date('Y-m-d H:i:s'),
                    'note' => 'NLP system is being initialized'
                ]
            ]);
        } else {
            // Table exists, get stats
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_analyses,
                    AVG(total_score) as average_score,
                    MAX(total_score) as best_score,
                    MIN(total_score) as worst_score,
                    AVG(grammar_score) as avg_grammar,
                    AVG(keyword_score) as avg_keyword,
                    AVG(structure_score) as avg_structure
                FROM nlp_analysis_results 
                WHERE student_id = ?
            ");
            
            $stmt->execute([$user['id']]);
            $overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$overallStats || $overallStats['total_analyses'] == 0) {
                $overallStats = [
                    'total_analyses' => 0,
                    'average_score' => 0,
                    'best_score' => 0,
                    'worst_score' => 0,
                    'avg_grammar' => 0,
                    'avg_keyword' => 0,
                    'avg_structure' => 0
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'overall' => $overallStats,
                    'daily' => [],
                    'generated_at' => date('Y-m-d H:i:s')
                ]
            ]);
        }
        
    } catch (Exception $e) {
        error_log("NLP Stats Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Error retrieving statistics'
        ]);
    }
    
    exit;
}
?>
