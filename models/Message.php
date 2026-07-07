<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Message {
    private $db;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function send($senderId, $receiverId, $message, $productId = null) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, product_id, message_text) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$senderId, $receiverId, $productId, $message]);
    }

    public function getConversation($userId1, $userId2, $productId = null, $limit = 50) {
        $sql = "SELECT m.*, 
                       u1.username as sender_username, u1.full_name as sender_name,
                       u2.username as receiver_username, u2.full_name as receiver_name,
                       p.title as product_title
                FROM messages m
                JOIN users u1 ON m.sender_id = u1.user_id
                JOIN users u2 ON m.receiver_id = u2.user_id
                LEFT JOIN products p ON m.product_id = p.product_id
                WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                   OR (m.sender_id = ? AND m.receiver_id = ?)";
        $params = [$userId1, $userId2, $userId2, $userId1];

        if ($productId) {
            $sql .= " AND (m.product_id = ? OR m.product_id IS NULL)";
            $params[] = $productId;
        }

        $sql .= " ORDER BY m.created_at ASC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getConversations($userId) {
        $sql = "SELECT DISTINCT 
                    CASE 
                        WHEN m.sender_id = ? THEN m.receiver_id
                        ELSE m.sender_id
                    END as other_user_id,
                    u.username as other_username,
                    u.full_name as other_name,
                    (SELECT message_text FROM messages m2 
                     WHERE (m2.sender_id = ? AND m2.receiver_id = u.user_id) 
                        OR (m2.sender_id = u.user_id AND m2.receiver_id = ?)
                     ORDER BY m2.created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM messages m2 
                     WHERE (m2.sender_id = ? AND m2.receiver_id = u.user_id) 
                        OR (m2.sender_id = u.user_id AND m2.receiver_id = ?)
                     ORDER BY m2.created_at DESC LIMIT 1) as last_message_time,
                    (SELECT COUNT(*) FROM messages m3 
                     WHERE m3.receiver_id = ? AND m3.sender_id = u.user_id AND m3.is_read = 0) as unread_count
                FROM messages m
                JOIN users u ON (u.user_id = m.sender_id OR u.user_id = m.receiver_id)
                WHERE (m.sender_id = ? OR m.receiver_id = ?)
                AND u.user_id != ?
                GROUP BY other_user_id
                ORDER BY last_message_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);
        return $stmt->fetchAll();
    }

    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'];
    }

    public function markAsRead($userId, $senderId) {
        $sql = "UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $senderId]);
    }

    public function getNew($userId, $otherUserId, $lastId = 0) {
        $sql = "SELECT m.*, 
                       u1.username as sender_username, u1.full_name as sender_name,
                       p.title as product_title
                FROM messages m
                JOIN users u1 ON m.sender_id = u1.user_id
                LEFT JOIN products p ON m.product_id = p.product_id
                WHERE ((m.sender_id = ? AND m.receiver_id = ?) 
                   OR (m.sender_id = ? AND m.receiver_id = ?))
                AND m.message_id > ?
                ORDER BY m.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $otherUserId, $otherUserId, $userId, $lastId]);
        return $stmt->fetchAll();
    }
}
