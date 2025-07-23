<?php
/**
 * NLP API Endpoint untuk POINTMARKET
 * 
 * Endpoint ini menangani request analisis NLP dari frontend
 * dan mengembalikan hasil analisis dalam format JSON
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/nlp-model.php';

// Set content type
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please login first.'
    ]);
    exit;
}

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();
$nlpModel = new NLPModel($pdo);

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            handlePostRequest($nlpModel, $user);
            break;
        case 'GET':
            handleGetRequest($nlpModel, $user);
            break;
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
    ]);
}

/**
 * Handle POST request untuk analisis teks
 */
function handlePostRequest($nlpModel, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // Validate required fields
    if (!isset($input['text']) || empty(trim($input['text']))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Text is required for analysis'
        ]);
        return;
    }
    
    $text = trim($input['text']);
    $context = $input['context'] ?? 'assignment';
    $assignment_id = $input['assignment_id'] ?? null;
    $save_result = $input['save_result'] ?? false;
    
    // Perform NLP analysis
    $analysis = $nlpModel->analyzeText($text, $context, $user['id']);
    
    if (!$analysis || isset($analysis['error'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $analysis['error'] ?? 'Analysis failed'
        ]);
        return;
    }
    
    // Save result if requested
    if ($save_result && $assignment_id) {
        $saved_id = $nlpModel->saveAnalysisResult($user['id'], $assignment_id, $analysis);
        $analysis['saved_id'] = $saved_id;
    }
    
    // Return analysis result
    echo json_encode([
        'success' => true,
        'data' => $analysis,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Handle GET request untuk history dan statistics
 */
function handleGetRequest($nlpModel, $user) {
    $action = $_GET['action'] ?? 'history';
    
    switch ($action) {
        case 'history':
            $limit = (int)($_GET['limit'] ?? 10);
            $history = $nlpModel->getAnalysisHistory($user['id'], $limit);
            
            echo json_encode([
                'success' => true,
                'data' => $history,
                'count' => count($history)
            ]);
            break;
            
        case 'statistics':
            $stats = getNLPStatistics($nlpModel, $user['id']);
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'progress':
            $progress = getNLPProgress($nlpModel, $user['id']);
            echo json_encode([
                'success' => true,
                'data' => $progress
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action'
            ]);
    }
}

/**
 * Get NLP statistics untuk student
 */
function getNLPStatistics($nlpModel, $student_id) {
    try {
        $pdo = $nlpModel->getPDO();
        
        // Check if table exists first
        $stmt = $pdo->query("SHOW TABLES LIKE 'nlp_analysis_results'");
        if ($stmt->rowCount() == 0) {
            // Try to auto-create tables
            require_once dirname(__DIR__) . '/setup-nlp.php';
            if (setupNLPDatabase()) {
                // Tables created, continue with query
            } else {
                // Table creation failed, return empty stats
                return [
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
                    'note' => 'NLP database setup failed - please contact administrator'
                ];
            }
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_analyses,
                AVG(total_score) as average_score,
                MAX(total_score) as best_score,
                MIN(total_score) as worst_score,
                AVG(grammar_score) as avg_grammar,
                AVG(keyword_score) as avg_keyword,
                AVG(structure_score) as avg_structure,
                AVG(readability_score) as avg_readability,
                AVG(sentiment_score) as avg_sentiment,
                AVG(complexity_score) as avg_complexity,
                DATE(created_at) as analysis_date
            FROM nlp_analysis_results 
            WHERE student_id = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY analysis_date DESC
        ");
        
        $stmt->execute([$student_id]);
        $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Overall stats
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
        
        $stmt->execute([$student_id]);
        $overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no data, return zeros
        if (!$overallStats || $overallStats['total_analyses'] == 0) {
            return [
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
                'note' => 'No analysis data found for this student'
            ];
        }
        
        return [
            'overall' => $overallStats,
            'daily' => $dailyStats,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        error_log("Error getting NLP statistics: " . $e->getMessage());
        return [
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
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get NLP progress over time
 */
function getNLPProgress($nlpModel, $student_id) {
    try {
        $pdo = $nlpModel->getPDO();
        
        // Check if table exists first
        $stmt = $pdo->query("SHOW TABLES LIKE 'nlp_analysis_results'");
        if ($stmt->rowCount() == 0) {
            // Try to auto-create tables
            require_once dirname(__DIR__) . '/setup-nlp.php';
            if (setupNLPDatabase()) {
                // Tables created, continue with query
            } else {
                return [
                    'monthly_progress' => [],
                    'generated_at' => date('Y-m-d H:i:s'),
                    'note' => 'NLP database setup failed - please contact administrator'
                ];
            }
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as total_analyses,
                AVG(total_score) as average_score,
                MAX(total_score) as best_score,
                AVG(grammar_score) as avg_grammar,
                AVG(keyword_score) as avg_keyword,
                AVG(structure_score) as avg_structure
            FROM nlp_analysis_results 
            WHERE student_id = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        
        $stmt->execute([$student_id]);
        $progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate improvement for each month
        for ($i = 1; $i < count($progress); $i++) {
            $current = $progress[$i]['average_score'];
            $previous = $progress[$i-1]['average_score'];
            $progress[$i]['improvement'] = $current - $previous;
        }
        
        return [
            'monthly_progress' => $progress,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        error_log("Error getting NLP progress: " . $e->getMessage());
        return [
            'monthly_progress' => [],
            'generated_at' => date('Y-m-d H:i:s'),
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Test endpoint untuk quick testing
 */
if (isset($_GET['test'])) {
    try {
        // Check if NLP model can be created
        $testModel = new NLPModel($pdo);
        
        // Check if tables exist
        $tables = ['nlp_analysis_results', 'nlp_keywords', 'nlp_feedback_templates'];
        $tableStatus = [];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $tableStatus[$table] = $stmt->rowCount() > 0;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'NLP API is working',
            'user' => $user,
            'tables' => $tableStatus,
            'nlp_model' => 'OK',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'NLP API test failed',
            'error' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
?>
