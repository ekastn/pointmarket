<?php

namespace App;

use App\Services\ApiClient;
use App\Controllers\ErrorController;

class Router
{
    protected array $routes = [];
    protected ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        // Try direct match
        if (isset($this->routes[$method][$path])) {
            [$controllerClass, $methodName] = $this->routes[$method][$path];
            $controller = new $controllerClass($this->apiClient);
            $controller->$methodName();
            return;
        }

        // Try dynamic routes
        foreach ($this->routes[$method] as $routePath => $handler) {
            // Convert route path to a regex pattern
            $pattern = preg_replace('/{([a-zA-Z0-9_]+)}', '([a-zA-Z0-9_]+)', preg_quote($routePath, '/'));
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove the full match
                [$controllerClass, $methodName] = $handler;
                $controller = new $controllerClass($this->apiClient);
                
                // Call the method with captured parameters
                call_user_func_array([$controller, $methodName], $matches);
                return;
            }
        }

        $this->handleError(404, "No route found for {$method} {$path}");
    }

    protected function handleError(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        $errorController = new ErrorController($this->apiClient);
        if ($statusCode === 404) {
            $errorController->show404($message);
        } else {
            $errorController->show500($message);
        }
    }
}
