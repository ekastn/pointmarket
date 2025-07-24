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

        if (isset($this->routes[$method][$path])) {
            [$controllerClass, $methodName] = $this->routes[$method][$path];

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass($this->apiClient);
                if (method_exists($controller, $methodName)) {
                    $controller->$methodName();
                } else {
                    $this->handleError(500, "Method {$methodName} not found in {$controllerClass}");
                }
            } else {
                $this->handleError(500, "Controller class {$controllerClass} not found");
            }
        } else {
            $this->handleError(404, "No route found for {$method} {$path}");
        }
    }

    protected function handleError(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        $errorController = new ErrorController();
        if ($statusCode === 404) {
            $errorController->show404($message);
        } else {
            $errorController->show500($message);
        }
    }
}