<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/src/Core/helpers.php';

use App\Core\ApiClient;
use App\Core\Router;
use DI\ContainerBuilder;

// Load environment variables from .env file
if (file_exists(__DIR__.'/.env')) {
    $env = parse_ini_file(__DIR__.'/.env');
    define('API_BASE_URL', $env['API_BASE_URL'] ?? 'http://localhost:8080');
} else {
    define('API_BASE_URL', 'http://localhost:8080');
}

// Build the DI Container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(require __DIR__ . '/config/dependencies.php');
$container = $containerBuilder->build();

$router = $container->get(Router::class);

// Load routes
(require __DIR__ . '/config/routes.php')($router);

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
