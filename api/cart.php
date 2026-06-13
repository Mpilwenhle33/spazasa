<?php

// =====================================================

// api/cart.php

// GET    — fetch current user's cart

// POST   — add product to cart        { product_id }

// DELETE — remove from cart           ?product_id=X

// =====================================================

require_once __DIR__ . '/../config/db.php';

apiHeaders();

startSession();

$user   = requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

$db     = getDB();

// ---- GET: fetch cart items ----

if ($method === 'GET') {

    $stmt = $db->prepare(

        'SELECT ci.id AS cart_id, ci.product_id, ci.added_at,

                p.title, p.price, p.cond, p.location, p.images, p.is_sold,

                u.name AS seller_name

         FROM cart_items ci

         JOIN products p ON p.id = ci.product_id

         JOIN users    u ON u.id = p.seller_id

         WHERE ci.user_id = ?

         ORDER BY ci.added_at DESC'

    );

    $stmt->execute([$user['id']]);

    $items = $stmt->fetchAll();

    $total = 0;

    foreach ($items as &$item) {

        $item['images'] = json_decode($item['images'] ?? '[]', true);

        $item['cover']  = $item['images'][0] ?? 'uploads/products/default.jpg';

        $total += (float)$item['price'];

    }

    jsonResponse(true, ['items' => $items, 'total' => $total, 'count' => count($items)]);

}

// ---- POST: add to cart ----

if ($method === 'POST') {

    $body       = json_decode(file_get_contents('php://input'), true);

    $product_id = (int)($body['product_id'] ?? 0);

    if (!$product_id) { jsonResponse(false, null, 'product_id required.', 422); }

    // Check product exists and is not sold

    $stmt = $db->prepare('SELECT id, seller_id, is_sold FROM products WHERE id = ? AND is_active = 1');

    $stmt->execute([$product_id]);

    $product = $stmt->fetch();

    if (!$product)              { jsonResponse(false, null, 'Product not found.', 404); }

    if ($product['is_sold'])    { jsonResponse(false, null, 'Product already sold.', 409); }

    if ($product['seller_id'] === $user['id']) {

        jsonResponse(false, null, 'You cannot add your own item to cart.', 409);

    }

    // Insert (ignore duplicate)

    $stmt = $db->prepare(

        'INSERT IGNORE INTO cart_items (user_id, product_id) VALUES (?, ?)'

    );

    $stmt->execute([$user['id'], $product_id]);

    jsonResponse(true, ['message' => 'Added to cart.', 'product_id' => $product_id]);

}

// ---- DELETE: remove from cart ----

if ($method === 'DELETE') {

    $product_id = (int)($_GET['product_id'] ?? 0);

    if (!$product_id) { jsonResponse(false, null, 'product_id required.', 422); }

    $stmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');

    $stmt->execute([$user['id'], $product_id]);

    jsonResponse(true, ['message' => 'Removed from cart.']);

}

jsonResponse(false, null, 'Method not allowed.', 405);