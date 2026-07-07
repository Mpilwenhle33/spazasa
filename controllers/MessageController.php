<?php
// controllers/MessageController.php

require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/User.php';

class MessageController {
    private $messageModel;
    private $productModel;
    private $userModel;

    public function __construct() {
        $this->messageModel = new Message();
        $this->productModel = new Product();
        $this->userModel = new User();
    }

    public function showMessages() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        $conversations = $this->messageModel->getConversations($_SESSION['user_id']);
        include __DIR__ . '/../views/messages.php';
    }

    public function showChat() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        $otherUserId = $_GET['user'] ?? 0;
        $productId = $_GET['product'] ?? null;
        if (!$otherUserId) {
            header('Location: index.php?action=messages');
            exit;
        }
        $this->messageModel->markAsRead($_SESSION['user_id'], $otherUserId);
        $messages = $this->messageModel->getConversation($_SESSION['user_id'], $otherUserId, $productId);
        $otherUser = $this->userModel->getById($otherUserId);
        $product = null;
        if ($productId) {
            $product = $this->productModel->getById($productId);
        }
        include __DIR__ . '/../views/chat.php';
    }

    public function sendMessage() {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Please login']);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    $receiverId = $_POST['receiver_id'] ?? 0;
    $message = trim($_POST['message'] ?? '');
    $productId = $_POST['product_id'] ?? null;
    if (!$receiverId || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Missing fields']);
        exit;
    }
    $result = $this->messageModel->send($_SESSION['user_id'], $receiverId, $message, $productId);
    echo json_encode(['success' => $result]);
    exit;
}

public function getMessages() {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Please login']);
        exit;
    }
    $otherUserId = $_GET['user'] ?? 0;
    $lastId = $_GET['last_id'] ?? 0;
    if (!$otherUserId) {
        echo json_encode(['success' => false, 'error' => 'User ID required']);
        exit;
    }
    $messages = $this->messageModel->getNew($_SESSION['user_id'], $otherUserId, $lastId);
    $this->messageModel->markAsRead($_SESSION['user_id'], $otherUserId);
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'last_id' => !empty($messages) ? end($messages)['message_id'] : $lastId
    ]);
    exit;
}
}