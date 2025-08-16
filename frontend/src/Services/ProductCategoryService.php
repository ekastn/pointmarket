<?php

namespace App\Services;

use App\Core\ApiClient;

class ProductCategoryService
{
    protected ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getAllProductCategories(string $search = '', int $page = 1, int $limit = 10): ?array
    {
        $queryParams = [
            'page' => $page,
            'limit' => $limit,
        ];
        if (!empty($search)) {
            $queryParams['search'] = $search;
        }

        $response = $this->apiClient->request('GET', '/api/v1/product-categories', ['query' => $queryParams]);

        if ($response['success']) {
            return $response;
        }

        return null;
    }

    public function getProductCategoryById(int $id): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/product-categories/' . $id);

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function createProductCategory(array $categoryData): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/product-categories', ['json' => $categoryData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to create product category.';
        }

        return null;
    }

    public function updateProductCategory(int $id, array $categoryData): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/product-categories/' . $id, ['json' => $categoryData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to update product category.';
        }

        return null;
    }

    public function deleteProductCategory(int $id): ?bool
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/product-categories/' . $id);

        if ($response['success']) {
            return true;
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to delete product category.';
        }

        return false;
    }
}
