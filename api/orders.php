<?php

// =====================================================

// api/orders.php

// GET    — list my orders (as buyer or seller)

// POST   — create order from cart    { product_id, delivery_type }

// PUT    — update order status       { order_id, status }  (seller only)

// =====================================================

require_once __DIR__ . '/../config/db.php';

apiHeaders();

startSession();

$user   = requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

$db     = getDB();

// Delivery cost map

$deliveryCosts = ['free' => 0, 'standard' => 160, 'express' => 259];

// ---- GET ----

if ($method === 'GET') {

    $role = $_GET['role'] ?? 'buyer';   // buyer | seller

    $col  = ($role === 'seller') ? 'o.seller_id' : 'o.buyer_id';

    $stmt = $db->prepare(

        "SELECT o.id, o.status, o.delivery_type, o.delivery_cost, o.total_amount, o.created_at,

                p.title AS product_title, p.images AS product_images,

                buyer.name  AS buyer_name,

                seller.name AS seller_name,

                dt.status   AS tracking_status,

                dt.eta_minutes,

                dt.distance_km

         FROM orders o

         JOIN products p      ON p.id  = o.product_id

         JOIN users buyer     ON buyer.id  = o.buyer_id

         JOIN users seller    ON seller.id = o.seller_id

         LEFT JOIN delivery_tracking dt ON dt.order_id = o.id

         WHERE $col = ?

         ORDER BY o.created_at DESC"

    );

    $stmt->execute([$user['id']]);

    $orders = $stmt->fetchAll();

    foreach ($orders as &$o) {

        $o['product_images'] = json_decode($o['product_images'] ?? '[]', true);

        $o['cover'] = $o['product_images'][0] ?? 'uploads/products/default.jpg';

    }

    jsonResponse(true, $orders);

}

// ---- POST: create order ----

if ($method === 'POST') {

    $body          = json_decode(file_get_contents('php://input'), true);

    $product_id    = (int)($body['product_id']    ?? 0);

    $delivery_type = $body['delivery_type'] ?? 'free';

    if (!$product_id) { jsonResponse(false, null, 'product_id required.', 422); }

    if (!array_key_exists($delivery_type, $deliveryCosts)) { $delivery_type = 'free'; }

    // Get product

    $stmt = $db->prepare('SELECT id, seller_id, price, is_sold FROM products WHERE id = ? AND is_active = 1');

    $stmt->execute([$product_id]);

    $product = $stmt->fetch();

    if (!$product)           { jsonResponse(false, null, 'Product not found.', 404); }

    if ($product['is_sold']) { jsonResponse(false, null, 'Product already sold.', 409); }

    if ($product['seller_id'] === $user['id']) {

        jsonResponse(false, null, 'Cannot buy your own item.', 409);

    }

    $delivery_cost = $deliveryCosts[$delivery_type];

    $total         = (float)$product['price'] + $delivery_cost;

    $db->beginTransaction();

    try {

        // Create order

        $stmt = $db->prepare(

            'INSERT INTO orders (buyer_id, product_id, seller_id, delivery_type, delivery_cost, total_amount)

             VALUES (?, ?, ?, ?, ?, ?)'

        );

        $stmt->execute([$user['id'], $product_id, $product['seller_id'], $delivery_type, $delivery_cost, $total]);

        $orderId = (int)$db->lastInsertId();

        // Mark product as sold

        $db->prepare('UPDATE products SET is_sold = 1 WHERE id = ?')->execute([$product_id]);

        // Remove from cart

        $db->prepare('DELETE FROM cart_items WHERE product_id = ?')->execute([$product_id]);

        // Create tracking record

        $db->prepare(

            'INSERT INTO delivery_tracking (order_id, status) VALUES (?, "order_placed")'

        )->execute([$orderId]);

        $db->commit();

        jsonResponse(true, [

            'order_id'      => $orderId,

            'total_amount'  => $total,

            'delivery_type' => $delivery_type,

            'delivery_cost' => $delivery_cost,

            'status'        => 'pending',

        ], '', 201);

    } catch (Exception $e) {

        $db->rollBack();

        jsonResponse(false, null, 'Order failed. Please try again.', 500);

    }

}

// ---- PUT: update order status (seller confirms / marks delivered) ----

if ($method === 'PUT') {

    $body     = json_decode(file_get_contents('php://input'), true);

    $order_id = (int)($body['order_id'] ?? 0);

    $status   = $body['status'] ?? '';

    $validStatuses = ['confirmed','in_transit','delivered','cancelled'];

    if (!$order_id || !in_array($status, $validStatuses)) {

        jsonResponse(false, null, 'order_id and valid status required.', 422);

    }

    // Verify seller owns this order

    $stmt = $db->prepare('SELECT seller_id, buyer_id FROM orders WHERE id = ?');

    $stmt->execute([$order_id]);

    $order = $stmt->fetch();

    if (!$order) { jsonResponse(false, null, 'Order not found.', 404); }

    // Seller or buyer can update

    if ($order['seller_id'] !== $user['id'] && $order['buyer_id'] !== $user['id']) {

        jsonResponse(false, null, 'Not authorised.', 403);

    }

    $db->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $order_id]);

    // Sync tracking status

    $trackingMap = [

        'confirmed'  => 'seller_confirmed',

        'in_transit' => 'in_transit',

        'delivered'  => 'delivered',

    ];

    if (isset($trackingMap[$status])) {

        $db->prepare(

            'UPDATE delivery_tracking SET status = ?, updated_at = NOW() WHERE order_id = ?'

        )->execute([$trackingMap[$status], $order_id]);

    }

    jsonResponse(true, ['message' => 'Order updated.', 'status' => $status]);

}

jsonResponse(false, null, 'Method not allowed.', 405);