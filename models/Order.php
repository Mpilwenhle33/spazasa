<?php
// models/Order.php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function create($data) {
        $sql = "INSERT INTO orders (buyer_id, seller_id, payment_reference, total_amount, delivery_type, delivery_address) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['buyer_id'],
            $data['seller_id'],
            $data['payment_reference'],
            $data['total_amount'],
            $data['delivery_type'] ?? 'free',
            $data['delivery_address'] ?? ''
        ]);
        return $this->db->lastInsertId();
    }

    public function addItem($orderId, $productId, $description, $price) {
        $sql = "INSERT INTO order_items (order_id, product_id, description, price) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$orderId, $productId, $description, $price]);
    }

    public function getById($orderId) {
        $sql = "SELECT o.*, 
                       b.username as buyer_username, b.full_name as buyer_name,
                       s.username as seller_username, s.full_name as seller_name
                FROM orders o
                LEFT JOIN users b ON o.buyer_id = b.user_id
                LEFT JOIN users s ON o.seller_id = s.user_id
                WHERE o.order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function getItems($orderId) {
        $sql = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getByReference($reference) {
        $sql = "SELECT o.*, 
                       b.username as buyer_username, b.full_name as buyer_name,
                       s.username as seller_username, s.full_name as seller_name
                FROM orders o
                LEFT JOIN users b ON o.buyer_id = b.user_id
                LEFT JOIN users s ON o.seller_id = s.user_id
                WHERE o.payment_reference = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reference]);
        return $stmt->fetchAll();
    }

    /**
     * Get orders where user is the buyer
     */
    public function getByBuyer($buyerId) {
        $sql = "SELECT o.*, 
                       s.username as seller_username, s.full_name as seller_name,
                       oi.description, oi.price
                FROM orders o
                LEFT JOIN users s ON o.seller_id = s.user_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.buyer_id = ?
                ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$buyerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get orders where user is the seller
     */
    public function getBySeller($sellerId) {
        $sql = "SELECT o.*, 
                       b.username as buyer_username, b.full_name as buyer_name,
                       oi.description, oi.price
                FROM orders o
                LEFT JOIN users b ON o.buyer_id = b.user_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.seller_id = ?
                ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function getRecent($limit = 10) {
        $sql = "SELECT o.*, 
                       b.username as buyer_username,
                       s.username as seller_username
                FROM orders o
                LEFT JOIN users b ON o.buyer_id = b.user_id
                LEFT JOIN users s ON o.seller_id = s.user_id
                ORDER BY o.created_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    COUNT(DISTINCT buyer_id) as unique_buyers
                FROM orders";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}