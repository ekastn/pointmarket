<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\ProductCategoryService;

class ProductCategoriesController extends BaseController
{
    protected ProductCategoryService $productCategoryService;

    public function __construct(ApiClient $apiClient, ProductCategoryService $productCategoryService)
    {
        parent::__construct($apiClient);
        $this->productCategoryService = $productCategoryService;
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 10;

        $response = $this->productCategoryService->getAllProductCategories($search, $page, $limit);

        if ($response !== null) {
            $categories = $response['data'];
            $meta = $response['meta'];

            $this->render('admin/product_categories', [
                'user' => $_SESSION['user_data'],
                'title' => 'Product Categories',
                'categories' => $categories,
                'search' => $search,
                'page' => $meta['page'],
                'limit' => $meta['limit'],
                'total_data' => $meta['total_records'],
                'total_pages' => $meta['total_pages'],
            ]);
        } else {
            $_SESSION['messages']['error'] = 'Failed to fetch product categories.';
            $this->redirect('/dashboard');
        }
    }

    public function store(): void
    {
        $categoryData = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? null,
        ];

        $result = $this->productCategoryService->createProductCategory($categoryData);

        if ($result !== null) {
            $this->redirect('/product-categories');
        } else {
            throw new \Exception('Failed to create product category.');
        }
    }

    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $categoryData = [
            'name' => $input['name'] ?? null,
            'description' => $input['description'] ?? null,
        ];

        $result = $this->productCategoryService->updateProductCategory($id, $categoryData);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'Product category updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to update product category.']);
        }
        exit;
    }

    public function destroy(int $id): void
    {
        $result = $this->productCategoryService->deleteProductCategory($id);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Product category deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to delete product category.']);
        }
        exit;
    }
}
