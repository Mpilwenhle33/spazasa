<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function create($data) {
        $sql = "INSERT INTO products (seller_id, title, description, price, category_id, cond, location, postal_code, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['seller_id'],
            $data['title'],
            $data['description'] ?? '',
            $data['price'],
            $data['category_id'] ?? null,
            $data['cond'] ?? 'Good',
            $data['location'] ?? '',
            $data['postal_code'] ?? null,
            $data['status'] ?? 'approved'   
        ]);
        return $this->db->lastInsertId();
    }
    public function getApproved($limit = 40) {
        $sql = "SELECT p.*, u.username as seller_username, u.full_name as seller_name,
                       c.name as category_name,
                       (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_cover = 1 LIMIT 1) as cover_image
                FROM products p 
                LEFT JOIN users u ON p.seller_id = u.user_id 
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.status != 'sold'
                ORDER BY p.created_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getByCategory($categoryId, $limit = 40) {
        $sql = "SELECT p.*, u.username as seller_username, u.full_name as seller_name,
                       c.name as category_name,
                       (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_cover = 1 LIMIT 1) as cover_image
                FROM products p 
                LEFT JOIN users u ON p.seller_id = u.user_id 
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.category_id = ? AND p.status != 'sold'
                ORDER BY p.created_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId, $limit]);
        return $stmt->fetchAll();
    }

    public function search($query, $limit = 40) {
        $sql = "SELECT p.*, u.username as seller_username, u.full_name as seller_name,
                       c.name as category_name,
                       (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_cover = 1 LIMIT 1) as cover_image
                FROM products p 
                LEFT JOIN users u ON p.seller_id = u.user_id 
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE (p.title LIKE ? OR p.description LIKE ?) AND p.status != 'sold'
                ORDER BY p.created_at DESC
                LIMIT ?";
        $search = '%' . $query . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$search, $search, $limit]);
        return $stmt->fetchAll();
    }

    public function getBySeller($sellerId) {
        $sql = "SELECT p.*, 
                       (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_cover = 1 LIMIT 1) as cover_image
                FROM products p 
                WHERE p.seller_id = ?
                ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function getById($productId) {
        $sql = "SELECT p.*, u.username as seller_username, u.full_name as seller_name,
                       c.name as category_name,
                       (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_cover = 1 LIMIT 1) as cover_image
                FROM products p 
                LEFT JOIN users u ON p.seller_id = u.user_id 
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    public function getAll($filters = []) {
        $sql = "SELECT p.*, u.username as seller_username, u.full_name as seller_name,
                       c.name as category_name,
                       (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_cover = 1 LIMIT 1) as cover_image
                FROM products p 
                LEFT JOIN users u ON p.seller_id = u.user_id 
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE 1=1";
        $params = [];

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY p.created_at DESC";
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function update($productId, $data) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            if ($key !== 'product_id') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        $params[] = $productId;
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($productId) {
        $sql = "DELETE FROM products WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$productId]);
    }

    public function approve($productId) {
        $sql = "UPDATE products SET status = 'approved' WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$productId]);
    }

    public function reject($productId) {
        $sql = "UPDATE products SET status = 'rejected' WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$productId]);
    }

    public function markSold($productId) {
        $sql = "UPDATE products SET status = 'sold' WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$productId]);
    }

    public function incrementViews($productId) {
        $sql = "UPDATE products SET views = views + 1 WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$productId]);
    }

    public function getTotal($status = null) {
        $sql = "SELECT COUNT(*) as total FROM products";
        $params = [];
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }

    public function addImage($productId, $imagePath, $isCover = false, $sortOrder = 0) 
    $isCoverInt = $isCover ? 1 : 0;
    
    $sql = "INSERT INTO product_images (product_id, image_path, is_cover, sort_order) VALUES (?, ?, ?, ?)";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$productId, $imagePath, $isCoverInt, $sortOrder]);
}

    public function getImages($productId) {
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_cover DESC, sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function deleteImages($productId) {
        $sql = "DELETE FROM product_images WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$productId]);
    }
}
