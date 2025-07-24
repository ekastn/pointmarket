<?php

namespace App\Controllers;

class ErrorController
{
    public function show404(string $message = "Page Not Found"): void
    {
        http_response_code(404);
        $this->render('errors/404.php', ['message' => $message]);
    }

    public function show500(string $message = "Internal Server Error"): void
    {
        http_response_code(500);
        $this->render('errors/500.php', ['message' => $message]);
    }

    protected function render(string $viewPath, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $viewPath;
    }
}
