<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Core\ViewRenderer;

class BaseController
{
    protected ApiClient $apiClient;
    protected ViewRenderer $renderer;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;

        // Start the session and check for JWT token
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['jwt_token'])) {
            $this->apiClient->setJwtToken($_SESSION['jwt_token']);
        }

        $this->renderer = new ViewRenderer(
            __DIR__ . '/../Views', // Path to your views
            __DIR__ . '/../Views/layouts' // Path to your layouts
        );
    }

    // Helper to render views using the renderer
    protected function render(string $viewName, array $data = [], string $layout = 'main'): void
    {
        echo $this->renderer->render($viewName, $data, $layout);
    }

    // Helper for redirects
    protected function redirect(string $path): void
    {
        header("Location: " . $path);
        exit();
    }
}