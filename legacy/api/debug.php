<?php
// Debug API to test paths
echo "Current directory: " . __DIR__ . "\n";
echo "Parent directory: " . dirname(__DIR__) . "\n";
echo "Config path: " . dirname(__DIR__) . '/includes/config.php' . "\n";
echo "Config file exists: " . (file_exists(dirname(__DIR__) . '/includes/config.php') ? 'YES' : 'NO') . "\n";

// Try to include config
try {
    require_once dirname(__DIR__) . '/includes/config.php';
    echo "Config included successfully\n";
} catch (Exception $e) {
    echo "Error including config: " . $e->getMessage() . "\n";
}
?>
