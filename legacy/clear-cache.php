<?php
// Clear OPcache if enabled
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared<br>";
}

// Clear realpath cache
clearstatcache();
echo "Stat cache cleared<br>";

echo "Cache clearing complete - " . date('Y-m-d H:i:s');
?>
