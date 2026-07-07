<?php
// models/Wishlist.php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Wishlist {
    private $db;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function add($userId, $productId) {
        $sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $productId]);
    }

    public function remove($userId, $productId) {
        $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $productId]);
    }

    public function isInWishlist($userId, $productId) {
        $sql = "SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $productId]);
        return $stmt->fetch() !== false;
    }
}