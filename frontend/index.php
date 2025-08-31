<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/src/Core/helpers.php';

use App\Core\ApiClient;
use App\Core\Router;
use DI\ContainerBuilder;

// Resolve API_BASE_URL with precedence: env > .env file > default
$apiBaseUrl = getenv('API_BASE_URL') ?: null;

if ($apiBaseUrl === null && file_exists(__DIR__.'/.env')) {
    // Lightweight dotenv: parse values and allow override if not set in real env
    $env = @parse_ini_file(__DIR__.'/.env', false, INI_SCANNER_TYPED) ?: [];
    if (isset($env['API_BASE_URL']) && is_string($env['API_BASE_URL']) && $env['API_BASE_URL'] !== '') {
        $apiBaseUrl = $env['API_BASE_URL'];
    }
}

if ($apiBaseUrl === null || $apiBaseUrl === '') {
    $apiBaseUrl = 'http://localhost:8080';
}

// Normalize and expose as constant for dependency wiring
define('API_BASE_URL', rtrim($apiBaseUrl, '/'));

// Build the DI Container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(require __DIR__ . '/config/dependencies.php');
$container = $containerBuilder->build();

$router = $container->get(Router::class);

// Load routes
(require __DIR__ . '/config/routes.php')($router);

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
