<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Cart {
    private $db;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function add($userId, $productId, $quantity = 1) {
        $sql = "SELECT cart_item_id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $sql = "UPDATE cart_items SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$quantity, $userId, $productId]);
        } else {
            $sql = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId, $productId, $quantity]);
        }
    }

    public function remove($userId, $productId) {
        $sql = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $productId]);
    }

    public function getItems($userId) {
        $sql = "SELECT c.*, p.title, p.price, p.cond, p.location,
                       (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_cover = 1 LIMIT 1) as image
                FROM cart_items c
                JOIN products p ON c.product_id = p.product_id
                WHERE c.user_id = ?
                ORDER BY c.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getTotal($userId) {
        $sql = "SELECT SUM(p.price * c.quantity) as total 
                FROM cart_items c
                JOIN products p ON c.product_id = p.product_id
                WHERE c.user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getCount($userId) {
        $sql = "SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function clear($userId) {
        $sql = "DELETE FROM cart_items WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
}
