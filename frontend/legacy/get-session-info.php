<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set content type
header('Content-Type: application/json');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include config file if it exists
$configLoaded = false;
try {
    if (file_exists('includes/config.php')) {
        require_once 'includes/config.php';
        $configLoaded = true;
    }
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error loading config file: ' . $e->getMessage()
    ]);
    exit;
}

// Check if user is logged in
$isLoggedIn = false;
$user = null;

if ($configLoaded && function_exists('isLoggedIn')) {
    $isLoggedIn = isLoggedIn();
    
    if ($isLoggedIn && function_exists('getCurrentUser')) {
        $user = getCurrentUser();
    }
} else {
    // Fallback check for session
    $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    
    if ($isLoggedIn) {
        $user = [
            'id' => $_SESSION['user_id'] ?? 'unknown',
            'username' => $_SESSION['username'] ?? 'unknown',
            'name' => $_SESSION['name'] ?? 'unknown',
            'email' => $_SESSION['email'] ?? 'unknown',
            'role' => $_SESSION['role'] ?? 'unknown'
        ];
    }
}

// Return session info
echo json_encode([
    'isLoggedIn' => $isLoggedIn,
    'user' => $user,
    'sessionId' => session_id(),
    'sessionData' => $_SESSION,
    'configLoaded' => $configLoaded
]);
?>
