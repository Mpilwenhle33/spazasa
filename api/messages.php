<?php
// =====================================================
// api/messages.php
// POST — send a message { receiver_id, product_id, message }
// GET  — get messages for a conversation
// =====================================================
require_once __DIR__ . '/../config/db.php';
apiHeaders();
startSession();

$user   = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'POST') {
    $body       = json_decode(file_get_contents('php://input'), true);
    $receiver   = (int)($body['receiver_id'] ?? 0);
    $product_id = (int)($body['product_id']  ?? 0);
    $message    = trim($body['message'] ?? '');
    if (!$message) { jsonResponse(false, null, 'Message required.', 422); }
    $stmt = $db->prepare(
        'INSERT INTO messages (sender_id, receiver_id, product_id, message) VALUES (?,?,?,?)'
    );
    $stmt->execute([$user['id'], $receiver, $product_id ?: null, $message]);
    jsonResponse(true, ['message' => 'Sent.']);
}

if ($method === 'GET') {
    $product_id = (int)($_GET['product_id'] ?? 0);
    $other_id   = (int)($_GET['user_id']    ?? 0);
    $stmt = $db->prepare(
        'SELECT m.*, u.name AS sender_name
         FROM messages m
         JOIN users u ON u.id = m.sender_id
         WHERE (m.sender_id = ? OR m.receiver_id = ?)
           AND (? = 0 OR m.product_id = ?)
         ORDER BY m.sent_at ASC LIMIT 100'
    );
    $stmt->execute([$user['id'], $user['id'], $product_id, $product_id]);
    jsonResponse(true, $stmt->fetchAll());
}

jsonResponse(false, null, 'Method not allowed.', 405);
