<?php
// Simple cache clearing
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset\n";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "APC cache cleared\n";
}

echo "Cache clearing complete\n";
?>
