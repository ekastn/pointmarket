<?php

// Global helper functions for path resolution and includes anchored at src/

if (!function_exists('src_path')) {
    function src_path(string $relative = ''): string
    {
        // __DIR__ is frontend/src/Core, so parent is frontend/src
        $base = dirname(__DIR__);
        return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($relative, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('include_src')) {
    /**
     * Require a PHP file relative to the src/ directory and return its value.
     * Example: include_src('config/menu.php');
     */
    function include_src(string $relative)
    {
        $file = src_path($relative);
        if (!file_exists($file)) {
            throw new \RuntimeException("File not found: {$file}");
        }
        return require $file;
    }
}

if (!function_exists('app_path')) {
    function app_path(string $relative = ''): string
    {
        // __DIR__ is frontend/src/Core, so two parents is frontend/
        $base = dirname(__DIR__, 2);
        return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($relative, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('include_app')) {
    /**
     * Require a PHP file relative to the frontend/ application root and return its value.
     * Example: include_app('config/menu.php');
     */
    function include_app(string $relative)
    {
        $file = app_path($relative);
        if (!file_exists($file)) {
            throw new \RuntimeException("File not found: {$file}");
        }
        return require $file;
    }
}
