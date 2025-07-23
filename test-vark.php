<?php
// Simple test for vark-correlation-analysis.php
echo "<!DOCTYPE html><html><head><title>Test</title></head><body>";
echo "<h1>Testing VARK Correlation Analysis</h1>";

// Check if session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<p>Session status: " . (isset($_SESSION['user_id']) ? "Logged in as user " . $_SESSION['user_id'] : "Not logged in") . "</p>";
echo "<p>Role: " . ($_SESSION['role'] ?? 'No role set') . "</p>";

// Test file inclusion
try {
    require_once 'includes/config.php';
    echo "<p>✅ Config file loaded successfully</p>";
} catch (Exception $e) {
    echo "<p>❌ Error loading config: " . $e->getMessage() . "</p>";
}

// Test database connection
try {
    $database = new Database();
    $pdo = $database->getConnection();
    echo "<p>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<br><a href='login.php'>Go to Login</a> | ";
echo "<a href='dashboard.php'>Go to Dashboard</a> | ";
echo "<a href='vark-correlation-analysis.php'>Go to VARK Analysis</a>";

echo "</body></html>";
?>
