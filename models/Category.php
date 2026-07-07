<?php
// models/Category.php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM categories ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($categoryId) {
        $sql = "SELECT * FROM categories WHERE category_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetch();
    }
    
    public function getBySlug($slug) {
        $sql = "SELECT * FROM categories WHERE slug = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    public function create($name, $slug, $icon = null) {
        $sql = "INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $slug, $icon]);
    }
    
    public function update($categoryId, $data) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $categoryId;
        $sql = "UPDATE categories SET " . implode(', ', $fields) . " WHERE category_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($categoryId) {
        $sql = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$categoryId]);
    }
}