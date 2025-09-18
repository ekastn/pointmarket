<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\ProductService;

class ProductsController extends BaseController
{
    protected ProductService $productService;

    public function __construct(ApiClient $apiClient, ProductService $productService)
    {
        parent::__construct($apiClient);
        $this->productService = $productService;
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 10;

        $userRole = $_SESSION['user_data']['role'] ?? '';
        $onlyActive = $userRole === 'admin' ? false : true;
        $productsResponse = $this->productService->getAllProducts($search, $categoryId, $page, $limit, $onlyActive);
        $categoriesResponse = $this->productService->getAllProductCategories(); // Fetch all categories

        if ($productsResponse !== null && $categoriesResponse !== null) {
            $products = $productsResponse['data'] ?? [];
            $meta = $productsResponse['meta'] ?? [];
            $categories = $categoriesResponse ?? []; // Extract categories data

            $userRole = $_SESSION['user_data']['role'] ?? '';

            $commonData = [
                'user' => $_SESSION['user_data'],
                'products' => $products,
                'search' => $search,
                'category_id' => $categoryId,
                'page' => $meta['page'],
                'limit' => $meta['limit'],
                'total_data' => $meta['total_records'],
                'total_pages' => $meta['total_pages'],
                'categories' => $categories, // Pass categories to both views
            ];

            if ($userRole === 'admin') {
                $this->render('admin/products', array_merge($commonData, [
                    'title' => 'Product Management',
                ]));
            } else {
                $this->render('products/index', array_merge($commonData, [
                    'title' => 'Marketplace',
                ]));
            }
        } else {
            $_SESSION['messages']['error'] = 'Failed to fetch products or categories.';
            $this->redirect('/dashboard');
        }
    }

    public function store(): void
    {
        $productData = [
            'category_id' => (int) $_POST['category_id'] ?? null,
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? null,
            'points_price' => isset($_POST['points_price']) ? (int) $_POST['points_price'] : 0,
            'type' => $_POST['type'] ?? '',
            'stock_quantity' => isset($_POST['stock_quantity']) ? (int) $_POST['stock_quantity'] : null,
            'is_active' => isset($_POST['is_active']) ? (bool) (int) $_POST['is_active'] : false,
            'metadata' => $_POST['metadata'] ?? '{}',
        ];

        $result = $this->productService->createProduct($productData);

        if ($result !== null) {
            $this->redirect('/products');
        }  else {
            throw new \Exception('Failed to create product.');
        }
    }

    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $productData = [
            'category_id' => isset($input['category_id']) ? (int) $input['category_id'] : null,
            'name' => $input['name'] ?? '',
            'description' => $input['description'] ?? null,
            'points_price' => isset($input['points_price']) ? (int) $input['points_price'] : 0,
            'type' => $input['type'] ?? '',
            'stock_quantity' => isset($input['stock_quantity']) ? (int) $input['stock_quantity'] : null,
            'is_active' => isset($input['is_active']) ? (bool) (int) $input['is_active'] : false,
            'metadata' => $input['metadata'] ?? '{}',
        ];

        $result = $this->productService->updateProduct($id, $productData);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update product.']);
        }
    }

    public function destroy(int $id): void
    {
        $result = $this->productService->deleteProduct($id);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product.']);
        }
    }

    public function purchase(int $id): void
    {
        $result = $this->productService->purchaseProduct($id);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Product purchased successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to purchase product.']);
        }
    }
}
