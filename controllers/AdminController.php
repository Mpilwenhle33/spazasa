<?php
// controllers/AdminController.php

require_once __DIR__ . '/../models/AdminManagement.php';
require_once __DIR__ . '/../models/UserManagement.php';
require_once __DIR__ . '/../models/ProductManagement.php';
require_once __DIR__ . '/../models/CategoryManagement.php';
require_once __DIR__ . '/../config/ValidationHelper.php';

class AdminController {
    private $adminModel;
    private $userModel;
    private $productModel;
    private $categoryModel;
    
    public function __construct() {
        $this->adminModel = new AdminManagement();
        $this->userModel = new UserManagement();
        $this->productModel = new ProductManagement();
        $this->categoryModel = new CategoryManagement();
    }
    
    private function checkAdminAccess() {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'moderator'])) {
            header('Location: admin.php?action=login');
            exit;
        }
    }
    
    public function dashboard() {
        $this->checkAdminAccess();
        $stats = $this->adminModel->getStats();
        $recentOrders = $this->adminModel->getRecentOrders(10);
        $recentLogs = $this->adminModel->getLogs(10);
        include __DIR__ . '/../views/admin/DashboardView.php';
    }
    
    public function users() {
        $this->checkAdminAccess();
        $users = $this->userModel->getAllUsers(100);
        include __DIR__ . '/../views/admin/UsersView.php';
    }
    
    public function userForm() {
        $this->checkAdminAccess();
        $userId = $_GET['id'] ?? 0;
        $user = null;
        if ($userId) {
            $user = $this->userModel->getUserById($userId);
        }
        include __DIR__ . '/../views/admin/UserFormView.php';
    }
    
    public function saveUser() {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin.php?action=users');
            exit;
        }
        
        $userId = $_POST['user_id'] ?? 0;
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $fullName = $_POST['full_name'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $phone = $_POST['phone'] ?? '';
        $location = $_POST['location'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $validator = new ValidationHelper([
            'username' => $username,
            'email' => $email,
            'full_name' => $fullName,
            'role' => $role
        ]);
        
        $validator->validateRequired('username')
                  ->validateLength('username', 3, 50)
                  ->validateRequired('email')
                  ->validateEmail('email')
                  ->validateRequired('full_name')
                  ->validateLength('full_name', 2, 100)
                  ->validateInArray('role', ['admin', 'moderator', 'user']);
        
        if ($userId === 0 && empty($password)) {
            $validator->validateRequired('password');
        } elseif (!empty($password)) {
            $validator->validatePassword('password');
        }
        
        if ($validator->fails()) {
            $_SESSION['admin_errors'] = $validator->getErrors();
            header('Location: admin.php?action=user-form&id=' . $userId);
            exit;
        }
        
        if ($this->userModel->isUsernameTaken($username, $userId)) {
            $_SESSION['admin_errors'] = ['username' => 'Username is already taken'];
            header('Location: admin.php?action=user-form&id=' . $userId);
            exit;
        }
        
        if ($this->userModel->isEmailTaken($email, $userId)) {
            $_SESSION['admin_errors'] = ['email' => 'Email is already registered'];
            header('Location: admin.php?action=user-form&id=' . $userId);
            exit;
        }
        
        if ($userId > 0) {
            $data = [
                'username' => $username,
                'email' => $email,
                'full_name' => $fullName,
                'role' => $role,
                'phone' => $phone,
                'location' => $location
            ];
            if (!empty($password)) {
                $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            }
            $this->userModel->updateUser($userId, $data);
            $this->adminModel->logAction($_SESSION['user_id'], "Updated user #$userId", 'user', $userId);
            $_SESSION['admin_success'] = 'User updated successfully!';
        } else {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $this->userModel->createUser($username, $email, $passwordHash, $fullName, $phone, null, $location);
            $this->adminModel->logAction($_SESSION['user_id'], "Created user $username", 'user');
            $_SESSION['admin_success'] = 'User created successfully!';
        }
        
        header('Location: admin.php?action=users');
        exit;
    }
    
    public function deleteUser() {
        $this->checkAdminAccess();
        
        $userId = $_GET['id'] ?? 0;
        if ($userId && $userId != $_SESSION['user_id']) {
            $this->userModel->deleteUser($userId);
            $this->adminModel->logAction($_SESSION['user_id'], "Deleted user #$userId", 'user', $userId);
            $_SESSION['admin_success'] = 'User deleted successfully!';
        }
        
        header('Location: admin.php?action=users');
        exit;
    }
    
    public function products() {
        $this->checkAdminAccess();
        $status = $_GET['status'] ?? 'pending';
        $products = $this->productModel->getProducts(['status' => $status]);
        include __DIR__ . '/../views/admin/ProductsView.php';
    }
    
    public function approveProduct() {
        $this->checkAdminAccess();
        
        $productId = $_GET['id'] ?? 0;
        if ($productId) {
            $this->productModel->approveProduct($productId);
            $this->adminModel->logAction($_SESSION['user_id'], "Approved product #$productId", 'product', $productId);
            $_SESSION['admin_success'] = 'Product approved successfully!';
        }
        
        header('Location: admin.php?action=products');
        exit;
    }
    
    public function rejectProduct() {
        $this->checkAdminAccess();
        
        $productId = $_GET['id'] ?? 0;
        if ($productId) {
            $this->productModel->rejectProduct($productId);
            $this->adminModel->logAction($_SESSION['user_id'], "Rejected product #$productId", 'product', $productId);
            $_SESSION['admin_success'] = 'Product rejected successfully!';
        }
        
        header('Location: admin.php?action=products');
        exit;
    }
    
    public function deleteProduct() {
        $this->checkAdminAccess();
        
        $productId = $_GET['id'] ?? 0;
        if ($productId) {
            $this->productModel->deleteProduct($productId);
            $this->adminModel->logAction($_SESSION['user_id'], "Deleted product #$productId", 'product', $productId);
            $_SESSION['admin_success'] = 'Product deleted successfully!';
        }
        
        header('Location: admin.php?action=products');
        exit;
    }
    
    public function categories() {
        $this->checkAdminAccess();
        $categories = $this->categoryModel->getAll();
        include __DIR__ . '/../views/admin/CategoriesView.php';
    }
}