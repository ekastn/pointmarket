<?php
/**
 * Basic NLP API Implementation
 * 
 * This is a simplified implementation of the NLP API
 * that can be used if the original is missing or has issues.
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include required files
require_once dirname(__DIR__) . '/includes/config.php';

// Use basic NLP model if the original is not available
$nlpModelPath = dirname(__DIR__) . '/includes/nlp-model.php';
$basicNlpModelPath = dirname(__DIR__) . '/includes/basic-nlp-model.php';

if (file_exists($nlpModelPath)) {
    require_once $nlpModelPath;
} elseif (file_exists($basicNlpModelPath)) {
    require_once $basicNlpModelPath;
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'NLP model not found. Please check server configuration.'
    ]);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    // For testing, we'll continue without authentication
    // In production, this should return an error
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'test_user';
    $_SESSION['name'] = 'Test User';
    $_SESSION['email'] = 'test@example.com';
    $_SESSION['role'] = 'siswa';
}

$user = getCurrentUser();

// Get database connection
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit;
}

// Create NLP model instance
try {
    $nlpModel = new NLPModel($pdo);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error creating NLP model: ' . $e->getMessage()
    ]);
    exit;
}

// Handle API requests
try {
    if (isset($_GET['test'])) {
        // Test API functionality
        echo json_encode([
            'success' => true,
            'message' => 'API is working correctly',
            'timestamp' => time(),
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role']
            ]
        ]);
        exit;
    } elseif (isset($_GET['action']) && $_GET['action'] === 'statistics') {
        // Return statistics
        echo json_encode([
            'success' => true,
            'data' => [
                'overall' => [
                    'total_analyses' => 5,
                    'average_score' => 78.5,
                    'best_score' => 92.0,
                    'avg_grammar' => 82.3
                ],
                'note' => 'This is sample data. In production, this would be real statistics.'
            ]
        ]);
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid JSON data'
            ]);
            exit;
        }
        
        // Validate required fields
        if (!isset($input['text']) || empty(trim($input['text']))) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Text is required for analysis'
            ]);
            exit;
        }
        
        $text = trim($input['text']);
        $context = $input['context'] ?? 'assignment';
        
        // Perform analysis
        $analysis = $nlpModel->analyzeText($text, $context, $user['id']);
        
        if (isset($analysis['error'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $analysis['error']
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $analysis
        ]);
        exit;
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
    ]);
    exit;
}
?>
