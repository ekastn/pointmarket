<?php

namespace App\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiClient
{
    private Client $client;
    private string $baseUrl;
    private ?string $jwtToken = null;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 5.0,
        ]);
    }

    public function setJwtToken(?string $token): void
    {
        $this->jwtToken = $token;
    }

    public function getJwtToken(): ?string
    {
        return $this->jwtToken;
    }

    public function request(string $method, string $uri, array $options = []): array
    {
        if ($this->jwtToken) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->jwtToken;
        }

        try {
            $response = $this->client->request($method, $uri, $options);
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);
            return $body;
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
            $errorMessage = $e->getMessage();
            if (empty($errorMessage) && $e->hasResponse()) {
                $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                $errorMessage = $responseBody['message'] ?? $errorMessage;
            }
            return ['success' => false, 'error' => $errorMessage, 'status' => $statusCode];
        }
    }

    public function login(string $username, string $password): array
    {
        $response = $this->request('POST', '/api/v1/auth/login', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);

        if ($response['success'] && isset($response['data']['token'])) {
            $this->setJwtToken($response['data']['token']);
        }
        return $response;
    }
}
