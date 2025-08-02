<?php
session_start();

// Simulate logged in user for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';

echo "<!DOCTYPE html>";
echo "<html><head><title>Test</title></head><body>";
echo "<h1>Testing VARK page access...</h1>";
echo "<p>Session ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>Username: " . $_SESSION['username'] . "</p>";
echo "<a href='vark-correlation-analysis.php'>Go to VARK Analysis</a>";
echo "</body></html>";
?>
