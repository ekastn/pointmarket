<?php

namespace App\Controllers;

use App\Core\ApiClient;

class ErrorController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function show404(string $message = "Page Not Found"): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }

    public function show500(string $message = "Internal Server Error"): void
    {
        http_response_code(500);
        $this->render('errors/500', ['message' => $message]);
    }
}
