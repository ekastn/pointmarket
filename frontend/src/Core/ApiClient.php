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
            'timeout' => 0,
            'connect_timeout' => 30.0,
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
            $body = json_decode($response->getBody()->getContents(), true);
            return is_array($body) ? $body : ['success' => true, 'data' => null];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 500;
            $message = 'An unknown error occurred.';

            if ($response) {
                $responseBody = json_decode($response->getBody()->getContents(), true);
                if (isset($responseBody['message'])) {
                    $message = $responseBody['message'];
                } else {
                    $message = $e->getMessage();
                }
            }

            return ['success' => false, 'message' => $message, 'status' => $statusCode];
        }
    }

    public function requestRaw(string $method, string $uri, array $options = []): ?\Psr\Http\Message\ResponseInterface
    {
        if ($this->jwtToken) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->jwtToken;
        }

        try {
            return $this->client->request($method, $uri, $options);
        } catch (RequestException $e) {
            // Log error? For now just return null or rethrow?
            // Returning null allows caller to handle failure
            return null;
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

    public function requestMultipart(string $method, string $uri, array $multipart, array $headers = []): array
    {
        $options = [
            'multipart' => $multipart,
        ];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }
        if ($this->jwtToken) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->jwtToken;
        }
        try {
            $response = $this->client->request($method, $uri, $options);
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
}
