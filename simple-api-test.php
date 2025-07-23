<?php
/**
 * Simple API Test - Test API without session dependency
 */

// Set content type
header('Content-Type: application/json');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test the API endpoint
$url = 'http://localhost/pointmarket/api/nlp-analysis.php?test=1&v=' . time();

echo json_encode([
    'test_info' => [
        'url' => $url,
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => 'GET'
    ]
]);

// Create a test session to bypass login requirement
session_start();

// Create minimal test session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['role'] = 'siswa';

// Test the API
try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'header' => 'Cookie: ' . session_name() . '=' . session_id()
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch API response',
            'url' => $url
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'raw_response' => $response,
            'response_length' => strlen($response),
            'json_valid' => (json_decode($response) !== null),
            'json_error' => json_last_error_msg()
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
