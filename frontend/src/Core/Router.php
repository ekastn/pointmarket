<?php

namespace App\Core;

use App\Controllers\ErrorController;
use Psr\Container\ContainerInterface;
use Throwable;

class Router
{
    protected array $routes = [];

    protected ApiClient $apiClient;

    protected string $currentGroupPrefix = '';

    protected array $currentGroupMiddleware = [];

    protected ContainerInterface $container;

    public function __construct(ApiClient $apiClient, ContainerInterface $container)
    {
        $this->apiClient = $apiClient;
        $this->container = $container;
    }

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function addRoute(string $method, string $path, array $handler, array $middleware = []): void
    {
        // Ensure prefix ends with a single slash, and path starts without one (unless it's just '/\')
        $normalizedPrefix = rtrim($this->currentGroupPrefix, '/');
        $normalizedPath = ltrim($path, '/');

        // Handle root path specifically to avoid "//" or trailing slash
        if ($normalizedPath === '') { // Case for root route within a group, e.g., group('/admin', function(){ $router->get('/', ...)})
            $fullPath = $normalizedPrefix . '/';
        } else {
            $fullPath = $normalizedPrefix . '/' . $normalizedPath;
        }

        // Final normalization for non-root paths to remove trailing slash
        if ($fullPath !== '/') {
            $fullPath = rtrim($fullPath, '/');
        }

        $fullMiddleware = array_merge($this->currentGroupMiddleware, $middleware);
        $this->routes[$method][$fullPath] = ['handler' => $handler, 'middleware' => $fullMiddleware];
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $previousGroupMiddleware = $this->currentGroupMiddleware;

        // Ensure proper slash handling for prefix concatenation
        $this->currentGroupPrefix = rtrim($this->currentGroupPrefix, '/').'/'.ltrim($prefix, '/');

        $this->currentGroupMiddleware = array_merge($this->currentGroupMiddleware, $middleware);

        $callback($this);

        $this->currentGroupPrefix = $previousGroupPrefix;
        $this->currentGroupMiddleware = $previousGroupMiddleware;
    }

    public function dispatch(string $method, string $uri): void
    {
        // The _method override logic is here
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $path = parse_url($uri, PHP_URL_PATH);
        // Normalize incoming path: remove trailing slash unless it's the root path
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        try {
            // Try direct match
            if (isset($this->routes[$method][$path])) {
                $route = $this->routes[$method][$path];
                $handler = $route['handler'];
                $middleware = $route['middleware'];

                foreach ($middleware as $m) {
                    [$middlewareClass, $middlewareMethod] = $m;
                    $middlewareInstance = $this->container->get($middlewareClass);
                    if (! $middlewareInstance->$middlewareMethod()) {
                        return; // Middleware stopped the request
                    }
                }

                [$controllerClass, $methodName] = $handler;
                $controller = $this->container->get($controllerClass);
                $controller->$methodName();

                return;
            }

            // Try dynamic routes
            foreach ($this->routes[$method] as $routePath => $route) {
                $handler = $route['handler'];
                $middleware = $route['middleware'];

                // Convert route path to a regex pattern
                $pattern = preg_quote($routePath, '#'); // Escapes / to \/ and { to \{
                // Now, replace the escaped placeholder pattern (e.g., \{id\}) with the capture group
                // We need to match the literal backslash before the curly brace (\\)
                $pattern = preg_replace('#\\\{([a-zA-Z0-9_]+)\\\\}#', '([a-zA-Z0-9_]+)', $pattern);
                $pattern = '#^'.$pattern.'$#';

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches); // Remove the full match

                    foreach ($middleware as $m) {
                        [$middlewareClass, $middlewareMethod] = $m;
                        $middlewareInstance = $this->container->get($middlewareClass);
                        if (! $middlewareInstance->$middlewareMethod()) {
                            return; // Middleware stopped the request
                        }
                    }

                    [$controllerClass, $methodName] = $handler;
                    $controller = $this->container->get($controllerClass);

                    // Call the method with captured parameters
                    call_user_func_array([$controller, $methodName], $matches);

                    return;
                }
            }

            $this->handleError(404, "No route found for {$method} {$path}");
        } catch (Throwable $e) {
            error_log($e);
            $this->handleError(500, 'An unexpected error occurred.'.$e->getMessage());
        }
    }

    protected function handleError(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        $errorController = $this->container->get(ErrorController::class);
        if ($statusCode === 404) {
            $errorController->show404($message);
        } else {
            $errorController->show500($message);
        }
    }
}
