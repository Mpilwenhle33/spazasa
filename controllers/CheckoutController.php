<?php
// controllers/CheckoutController.php

require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';

class CheckoutController {
    private $cartModel;
    private $productModel;
    private $orderModel;
    private $db;

    public function __construct() {
        $this->cartModel = new Cart();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function checkout() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $items = $this->cartModel->getItems($userId);

        if (empty($items)) {
            $_SESSION['error'] = 'Your cart is empty';
            header('Location: index.php?action=cart');
            exit;
        }

        $paymentRef = 'SPAZASA_' . strtoupper(uniqid()) . '_' . date('YmdHis');
        $total = $this->cartModel->getTotal($userId);

        try {
            $this->db->beginTransaction();

            // Group by seller
            $sellerItems = [];
            foreach ($items as $item) {
                $product = $this->productModel->getById($item['product_id']);
                if ($product) {
                    $sellerId = $product['seller_id'];
                    if (!isset($sellerItems[$sellerId])) {
                        $sellerItems[$sellerId] = [];
                    }
                    $sellerItems[$sellerId][] = $item;
                }
            }

            $orderIds = [];
            foreach ($sellerItems as $sellerId => $sellerItemList) {
                $sellerTotal = 0;
                foreach ($sellerItemList as $item) {
                    $sellerTotal += $item['price'] * $item['quantity'];
                }

                $orderData = [
                    'buyer_id' => $userId,
                    'seller_id' => $sellerId,
                    'payment_reference' => $paymentRef,
                    'total_amount' => $sellerTotal
                ];

                $orderId = $this->orderModel->create($orderData);
                $orderIds[] = $orderId;

                foreach ($sellerItemList as $item) {
                    $this->productModel->markSold($item['product_id']);

                    $this->orderModel->addItem(
                        $orderId,
                        $item['product_id'],
                        $item['title'],
                        $item['price'] * $item['quantity']
                    );
                }
            }

            $this->cartModel->clear($userId);

            $this->db->commit();

            $_SESSION['checkout_success'] = true;
            $_SESSION['payment_reference'] = $paymentRef;
            $_SESSION['order_ids'] = $orderIds;

            header('Location: index.php?action=receipt');
            exit;

        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = 'Checkout failed: ' . $e->getMessage();
            header('Location: index.php?action=cart');
            exit;
        }
    }

    public function showReceipt() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_success'])) {
            header('Location: index.php?action=marketplace');
            exit;
        }

        $paymentRef = $_SESSION['payment_reference'];
        $orderIds = $_SESSION['order_ids'];

        $orders = [];
        $allItems = [];
        $totalAmount = 0;

        foreach ($orderIds as $orderId) {
            $order = $this->orderModel->getById($orderId);
            if ($order) {
                $orders[] = $order;
                $totalAmount += $order['total_amount'];
                $items = $this->orderModel->getItems($orderId);
                foreach ($items as $item) {
                    $allItems[] = $item;
                }
            }
        }

        // Clear session data for receipt
        unset($_SESSION['checkout_success']);
        unset($_SESSION['payment_reference']);
        unset($_SESSION['order_ids']);

        include __DIR__ . '/../views/receipt.php';
    }
}