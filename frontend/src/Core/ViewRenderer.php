<?php

namespace App\Core;

class ViewRenderer
{
    protected string $viewsPath;
    protected string $layoutsPath;

    public function __construct(string $viewsPath, string $layoutsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/') . '/';
        $this->layoutsPath = rtrim($layoutsPath, '/') . '/';
    }

    public function render(string $viewName, array $data = [], string $layout = 'main'): string
    {
        $viewFile = $this->viewsPath . $viewName . '.php';
        $layoutFile = $this->layoutsPath . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: " . $viewFile);
        }
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file not found: " . $layoutFile);
        }

        // Capture the view content
        ob_start();
        extract($data);
        require $viewFile;
        $content = ob_get_clean();

        // Render the layout with the captured content
        ob_start();
        require $layoutFile;
        return ob_get_clean();
    }
}
