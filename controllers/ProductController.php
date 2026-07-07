<?php
// controllers/ProductController.php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/ValidationHelper.php';

class ProductController {
    private $productModel;
    private $categoryModel;
    private $userModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->userModel = new User();
    }

    /**
     * Display the sell form
     */
    public function showSell() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        $categories = $this->categoryModel->getAll();
        include __DIR__ . '/../views/sell.php';
    }

    /**
     * Process the sell form (create a new product)
     */
    public function sell() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Validate that the user exists
        $seller = $this->userModel->getById($_SESSION['user_id']);
        if (!$seller) {
            session_destroy();
            $_SESSION['login_error'] = 'Your account was not found. Please login again.';
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=sell');
            exit;
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $categoryId = $_POST['category_id'] ?? null;
        $cond = $_POST['cond'] ?? 'Good';
        $location = $_POST['location'] ?? $_SESSION['location'] ?? '';
        $postalCode = $_POST['postal_code'] ?? '';

        $validator = new ValidationHelper([
            'title' => $title,
            'price' => $price,
            'cond' => $cond
        ]);

        $validator->validateRequired('title')
                  ->validateLength('title', 3, 255)
                  ->validateNumeric('price')
                  ->validateInArray('cond', ['New', 'Like New', 'Good', 'Fair', 'Poor']);

        if ($validator->fails()) {
            $_SESSION['sell_errors'] = $validator->getErrors();
            $_SESSION['sell_data'] = $_POST;
            header('Location: index.php?action=sell');
            exit;
        }

        $data = [
            'seller_id' => $_SESSION['user_id'],
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'category_id' => $categoryId ?: null,
            'cond' => $cond,
            'location' => $location,
            'postal_code' => $postalCode,
            'status' => 'approved'
        ];

        $productId = $this->productModel->create($data);

        if (!$productId) {
            $_SESSION['sell_errors'] = ['general' => 'Failed to create listing. Please try again.'];
            header('Location: index.php?action=sell');
            exit;
        }

        // Handle image uploads
        $this->handleImageUploads($productId);

        $_SESSION['sell_success'] = 'Item listed successfully! Waiting for admin approval.';
        header('Location: index.php?action=marketplace');
        exit;
    }

    /**
     * Handle image uploads for a product
     */
    private function handleImageUploads($productId) {
        $uploadDir = __DIR__ . '/../assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Cover image
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $this->saveImage($_FILES['cover_image'], $productId, $uploadDir, true);
        }

        // Additional images
        if (isset($_FILES['images']) && !empty($_FILES['images']['tmp_name'][0])) {
            $sortOrder = 0;
            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['images']['name'][$index],
                        'tmp_name' => $tmpName,
                        'size' => $_FILES['images']['size'][$index],
                        'error' => $_FILES['images']['error'][$index]
                    ];
                    $this->saveImage($file, $productId, $uploadDir, false, $sortOrder);
                    $sortOrder++;
                }
            }
        }
    }

    /**
     * Save a single image
     */
    private function saveImage($file, $productId, $targetDir, $isCover = false, $sortOrder = 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            return false;
        }

        $filename = $productId . '_' . uniqid() . '.' . $ext;
        $targetPath = $targetDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $relativePath = 'assets/uploads/' . $filename;
            return $this->productModel->addImage($productId, $relativePath, $isCover, $sortOrder);
        }
        return false;
    }

    /**
     * Show the edit form for a product
     */
    public function showEdit() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $productId = $_GET['id'] ?? 0;
        $product = $this->productModel->getById($productId);

        if (!$product || $product['seller_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to edit this item.';
            header('Location: index.php?action=marketplace');
            exit;
        }

        $categories = $this->categoryModel->getAll();
        $images = $this->productModel->getImages($productId);
        include __DIR__ . '/../views/sell.php'; // reuse sell view
    }

    /**
     * Process the edit form
     */
    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=marketplace');
            exit;
        }

        $productId = $_POST['product_id'] ?? 0;
        $product = $this->productModel->getById($productId);

        if (!$product || $product['seller_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to edit this item.';
            header('Location: index.php?action=marketplace');
            exit;
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'category_id' => $_POST['category_id'] ?? null,
            'cond' => $_POST['cond'] ?? 'Good',
            'location' => $_POST['location'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? null
        ];

        $validator = new ValidationHelper($data);
        $validator->validateRequired('title')
                  ->validateLength('title', 3, 255)
                  ->validateNumeric('price')
                  ->validateInArray('cond', ['New', 'Like New', 'Good', 'Fair', 'Poor']);

        if ($validator->fails()) {
            $_SESSION['edit_errors'] = $validator->getErrors();
            header('Location: index.php?action=edit-product&id=' . $productId);
            exit;
        }

        $this->productModel->update($productId, $data);

        $_SESSION['edit_success'] = 'Item updated successfully!';
        header('Location: index.php?action=marketplace');
        exit;
    }

    /**
     * Delete a product
     */
    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $productId = $_GET['id'] ?? 0;
        $product = $this->productModel->getById($productId);

        if (!$product || $product['seller_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to delete this item.';
            header('Location: index.php?action=marketplace');
            exit;
        }

        $this->productModel->delete($productId);
        $_SESSION['success'] = 'Item deleted successfully!';
        header('Location: index.php?action=marketplace');
        exit;
    }
}