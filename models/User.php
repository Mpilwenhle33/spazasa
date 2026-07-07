<?php
// models/User.php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }
    
    public function create($username, $email, $passwordHash, $fullName, $phone = null, $location = null, $postalCode = null) {
        $sql = "INSERT INTO users (username, email, password_hash, full_name, phone, location, postal_code) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$username, $email, $passwordHash, $fullName, $phone, $location, $postalCode]);
    }
    
    public function getById($userId) {
        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function getByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function update($userId, $data) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            if ($key !== 'user_id' && $key !== 'password_hash') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        if (isset($data['password_hash'])) {
            $fields[] = "password_hash = ?";
            $params[] = $data['password_hash'];
        }
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($userId) {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    public function getAll($limit = 100, $offset = 0) {
        $sql = "SELECT user_id, username, email, full_name, phone, location, role, created_at 
                FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function updateRole($userId, $role) {
        $sql = "UPDATE users SET role = ? WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$role, $userId]);
    }
    
    public function getTotal() {
        $sql = "SELECT COUNT(*) as total FROM users";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }
    
    public function validateCredentials($email, $password) {
        $user = $this->getByEmail($email);
        if (!$user) {
            return false;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        return $user;
    }
    
    public function isUsernameTaken($username, $excludeUserId = null) {
        $sql = "SELECT user_id FROM users WHERE username = ?";
        $params = [$username];
        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }
    
    public function isEmailTaken($email, $excludeUserId = null) {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $params = [$email];
        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }
}