<?php

session_start();
$isAdmin = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!$isAdmin && $_GET['action'] !== 'login' && $_GET['action'] !== 'do-login') {
    header('Location: admin.php?action=login');
    exit;
}

require_once __DIR__ . '/config/DatabaseConnection.php';
require_once __DIR__ . '/config/ValidationHelper.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/AdminLog.php';

$userModel = new User();
$productModel = new Product();
$categoryModel = new Category();
$orderModel = new Order();
$action = $_GET['action'] ?? 'dashboard';


// ROUTING
switch ($action) {
    case 'login':
        if ($isAdmin) {
            header('Location: admin.php?action=dashboard');
            exit;
        }
        include __DIR__ . '/views/admin/login.php';
        break;

    case 'do-login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin.php?action=login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $userModel->getByEmail($email);

        if (!$user) {
            $_SESSION['admin_login_error'] = 'Invalid credentials';
            header('Location: admin.php?action=login');
            exit;
        }

        if (!password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_login_error'] = 'Invalid credentials';
            header('Location: admin.php?action=login');
            exit;
        }
        if ($user['role'] !== 'admin') {
            $_SESSION['admin_login_error'] = 'You do not have admin access';
            header('Location: admin.php?action=login');
            exit;
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        if (class_exists('AdminLog')) {
            $adminLog = new AdminLog();
            $adminLog->log($user['user_id'], 'Admin logged in');
        }

        header('Location: admin.php?action=dashboard');
        exit;
    case 'logout':
        session_destroy();
        header('Location: admin.php?action=login');
        exit;
    case 'dashboard':
        $stats = [
            'total_users' => $userModel->getTotal(),
            'total_products' => $productModel->getTotal(),
            'total_orders' => $orderModel->getStats()['total_orders'] ?? 0,
            'total_revenue' => $orderModel->getStats()['total_revenue'] ?? 0
        ];
        $recentOrders = $orderModel->getRecent(10);
        include __DIR__ . '/views/admin/dashboard.php';
        break;
    case 'users':
        $users = $userModel->getAll(100);
        include __DIR__ . '/views/admin/users.php';
        break;
    case 'products':
        $status = $_GET['status'] ?? 'all';
        $products = $productModel->getAll(['status' => $status]);
        include __DIR__ . '/views/admin/products.php';
        break;
    case 'delete-product':
        $productId = $_GET['id'] ?? 0;
        if ($productId) {
            $productModel->delete($productId);
            $_SESSION['admin_success'] = 'Product deleted successfully!';
        }
        header('Location: admin.php?action=products');
        exit;
    case 'delete-user':
        $userId = $_GET['id'] ?? 0;
        if ($userId && $userId != $_SESSION['user_id']) {
            $userModel->delete($userId);
            $_SESSION['admin_success'] = 'User deleted successfully!';
        }
        header('Location: admin.php?action=users');
        exit;
    default:
        header('Location: admin.php?action=dashboard');
        exit;
}
