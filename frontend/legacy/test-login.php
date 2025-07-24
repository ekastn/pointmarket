<?php
// Simple test to check if login is working
require_once 'includes/config.php';

echo "<h2>Login Test</h2>";

echo "<p>Session status: " . session_status() . "</p>";

if (session_status() == PHP_SESSION_NONE) {
    echo "<p>Starting session...</p>";
    session_start();
}

echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session data: <pre>" . print_r($_SESSION, true) . "</pre></p>";

echo "<p>Is logged in: " . (isLoggedIn() ? 'YES' : 'NO') . "</p>";

if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<p>Current user: <pre>" . print_r($user, true) . "</pre></p>";
} else {
    echo "<p>Not logged in - redirecting to login would happen here</p>";
}

echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>PHP version: " . phpversion() . "</p>";
?>
