<?php

namespace App\Controllers;

use App\Services\ApiClient;
use App\Services\ViewRenderer;

class BaseController
{
    protected ApiClient $apiClient;
    protected ViewRenderer $renderer;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
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