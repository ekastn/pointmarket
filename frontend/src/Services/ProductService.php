<?php

namespace App\Services;

use App\Core\ApiClient;

class ProductService
{
    protected ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getAllProducts(string $search = '', ?int $categoryId = null, int $page = 1, int $limit = 10, ?bool $onlyActive = null): ?array
    {
        $queryParams = [
            'page' => $page,
            'limit' => $limit,
        ];
        if (!empty($search)) {
            $queryParams['search'] = $search;
        }
        if ($categoryId !== null) {
            $queryParams['category_id'] = $categoryId;
        }
        if ($onlyActive !== null) {
            $queryParams['only_active'] = $onlyActive ? 1 : 0;
        }

        $response = $this->apiClient->request('GET', '/api/v1/products', ['query' => $queryParams]);

        if ($response['success']) {
            return $response;
        }

        return null;
    }

    public function getAllProductCategories(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/product-categories');

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function getProductById(int $id): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/products/' . $id);

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function createProduct(array $productData): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/products', ['json' => $productData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['messages']['error'] = $response['message'] ?? 'Failed to create product.';
        }

        return null;
    }

    public function updateProduct(int $id, array $productData): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/products/' . $id, ['json' => $productData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            echo $response;
        }

        return null;
    }

    public function deleteProduct(int $id): ?bool
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/products/' . $id);

        if ($response['success']) {
            return true;
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to delete product.';
        }

        return false;
    }

    public function purchaseProduct(int $productId): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/products/' . $productId . '/purchase');
        error_log(print_r($response, true));

        return $response;
    }

    public function getAllOrders(int $page = 1, int $limit = 10, string $search = ''): ?array
    {
        $queryParams = [
            'page' => $page,
            'limit' => $limit,
        ];
        if (!empty($search)) {
            $queryParams['search'] = $search;
        }

        $response = $this->apiClient->request('GET', '/api/v1/orders', ['query' => $queryParams]);

        if ($response['success']) {
            return $response;
        }

        return null;
    }
}
