<?php

if (!class_exists('CartController')) {

    class CartController {
        private $cartModel;
        private $productModel;
        private $wishlistModel;

        public function __construct() {
            require_once __DIR__ . '/../models/Cart.php';
            require_once __DIR__ . '/../models/Product.php';
            require_once __DIR__ . '/../models/Wishlist.php';
            $this->cartModel = new Cart();
            $this->productModel = new Product();
            $this->wishlistModel = new Wishlist();
        }

        public function showCart() {
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?action=login');
                exit;
            }
            $items = $this->cartModel->getItems($_SESSION['user_id']);
            $total = $this->cartModel->getTotal($_SESSION['user_id']);
            include __DIR__ . '/../views/cart.php';
        }

        public function addToCart() {
            header('Content-Type: application/json');

            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Please login']);
                exit;
            }

            $productId = $_POST['product_id'] ?? 0;
            if (!$productId) {
                echo json_encode(['success' => false, 'error' => 'Product ID missing']);
                exit;
            }

            $quantity = $_POST['quantity'] ?? 1;
            $product = $this->productModel->getById($productId);

            if (!$product) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }
            if ($product['status'] === 'sold') {
                echo json_encode(['success' => false, 'error' => 'Item already sold']);
                exit;
            }

            $result = $this->cartModel->add($_SESSION['user_id'], $productId, $quantity);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to add to cart']);
            }
            exit;
        }

        public function removeFromCart() {
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            $productId = $_GET['id'] ?? 0;
            $this->cartModel->remove($_SESSION['user_id'], $productId);
            header('Location: index.php?action=cart');
            exit;
        }

        public function getCount() {
            header('Content-Type: application/json');
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['count' => 0]);
                exit;
            }

            $count = $this->cartModel->getCount($_SESSION['user_id']);
            echo json_encode(['count' => $count]);
            exit;
        }

        public function toggleWishlist() {
            header('Content-Type: application/json');
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Please login']);
                exit;
            }

            $productId = $_POST['product_id'] ?? 0;
            if (!$productId) {
                echo json_encode(['success' => false, 'error' => 'Product ID missing']);
                exit;
            }

            $inWishlist = $this->wishlistModel->isInWishlist($_SESSION['user_id'], $productId);

            if ($inWishlist) {
                $result = $this->wishlistModel->remove($_SESSION['user_id'], $productId);
                $action = 'removed';
            } else {
                $result = $this->wishlistModel->add($_SESSION['user_id'], $productId);
                $action = 'added';
            }

            if ($result) {
                echo json_encode(['success' => true, 'action' => $action]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update wishlist']);
            }
            exit;
        }
    }

}
