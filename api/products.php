<?php

// =====================================================

// api/products.php

// GET    /api/products.php               — list / search

// GET    /api/products.php?id=X          — single product

// POST   /api/products.php               — create (auth required, multipart)

// DELETE /api/products.php?id=X          — delete own product (auth required)

// =====================================================

require_once __DIR__ . '/../config/db.php';

apiHeaders();

startSession();

$method = $_SERVER['REQUEST_METHOD'];

$db     = getDB();

// ---- GET ----

if ($method === 'GET') {

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Single product

    if ($id > 0) {

        $stmt = $db->prepare(

            'SELECT p.*, u.name AS seller_name, u.avatar AS seller_avatar, u.phone AS seller_phone, u.location AS seller_location

             FROM products p

             JOIN users u ON u.id = p.seller_id

             WHERE p.id = ? AND p.is_active = 1'

        );

        $stmt->execute([$id]);

        $product = $stmt->fetch();

        if (!$product) { jsonResponse(false, null, 'Product not found.', 404); }

        // Increment view count

        $db->prepare('UPDATE products SET views = views + 1 WHERE id = ?')->execute([$id]);

        // Decode images

        $product['images'] = json_decode($product['images'] ?? '[]', true);

        jsonResponse(true, $product);

    }

    // List / search / filter

    $where  = ['p.is_active = 1', 'p.is_sold = 0'];

    $params = [];

    if (!empty($_GET['category'])) {

        $where[]  = 'p.category = ?';

        $params[] = clean($_GET['category']);

    }

    if (!empty($_GET['cond'])) {

        $where[]  = 'p.cond = ?';

        $params[] = clean($_GET['cond']);

    }

    if (!empty($_GET['seller_id'])) {

        $where[]  = 'p.seller_id = ?';

        $params[] = (int)$_GET['seller_id'];

    }

    if (!empty($_GET['q'])) {

        $where[]  = 'MATCH(p.title, p.description) AGAINST(? IN BOOLEAN MODE)';

        $params[] = clean($_GET['q']) . '*';

    }

    if (!empty($_GET['min_price'])) {

        $where[]  = 'p.price >= ?';

        $params[] = (float)$_GET['min_price'];

    }

    if (!empty($_GET['max_price'])) {

        $where[]  = 'p.price <= ?';

        $params[] = (float)$_GET['max_price'];

    }

    $limit  = min((int)($_GET['limit']  ?? 20), 100);

    $offset = (int)($_GET['offset'] ?? 0);

    $sql = 'SELECT p.id, p.title, p.price, p.category, p.cond, p.location, p.images, p.views, p.created_at,

                   u.name AS seller_name, u.avatar AS seller_avatar

            FROM products p

            JOIN users u ON u.id = p.seller_id

            WHERE ' . implode(' AND ', $where) . '

            ORDER BY p.created_at DESC

            LIMIT ? OFFSET ?';

    $params[] = $limit;

    $params[] = $offset;

    $stmt = $db->prepare($sql);

    $stmt->execute($params);

    $products = $stmt->fetchAll();

    // Decode images for each

    foreach ($products as &$p) {

        $p['images'] = json_decode($p['images'] ?? '[]', true);

        $p['cover']  = $p['images'][0] ?? 'uploads/products/default.jpg';

    }

    // Total count for pagination

    $countSql = 'SELECT COUNT(*) FROM products p WHERE ' . implode(' AND ', array_slice($where, 0));

    $countParams = array_slice($params, 0, count($params) - 2);

    $total = (int)$db->prepare($countSql)->execute($countParams) ? 0 : 0;

    jsonResponse(true, ['products' => $products, 'count' => count($products)]);

}

// ---- POST (create product) ----

if ($method === 'POST') {

    $user = requireAuth();

    $title       = clean($_POST['title']       ?? '');

    $description = clean($_POST['description'] ?? '');

    $price       = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);

    $category    = clean($_POST['category']    ?? 'other');

    $cond        = in_array($_POST['cond'] ?? '', ['new','used']) ? $_POST['cond'] : 'new';

    $location    = clean($_POST['location']    ?? '');

    if (!$title || $price === false || $price < 0) {

        jsonResponse(false, null, 'Title and valid price are required.', 422);

    }

    $validCategories = ['women','men','kids','electronics','furniture','clothing','other'];

    if (!in_array($category, $validCategories)) $category = 'other';

    // Handle image uploads

    $imagePaths = [];

    if (!empty($_FILES['images'])) {

        $files = $_FILES['images'];

        // Normalise to array

        if (!is_array($files['name'])) {

            $files = array_map(fn($v) => [$v], $files);

        }

        $count = count($files['name']);

        for ($i = 0; $i < min($count, 6); $i++) {

            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            if ($files['size'][$i]  > MAX_FILE_SIZE)   continue;

            if (!in_array($files['type'][$i], ALLOWED_IMG_TYPES)) continue;

            $ext      = pathinfo($files['name'][$i], PATHINFO_EXTENSION);

            $filename = 'products/' . uniqid('img_', true) . '.' . strtolower($ext);

            $dest     = UPLOAD_DIR . $filename;

            if (!is_dir(UPLOAD_DIR . 'products')) {

                mkdir(UPLOAD_DIR . 'products', 0755, true);

            }

            if (move_uploaded_file($files['tmp_name'][$i], $dest)) {

                $imagePaths[] = 'uploads/' . $filename;

            }

        }

    }

    $imagesJson = json_encode($imagePaths);

    $stmt = $db->prepare(

        'INSERT INTO products (seller_id, title, description, price, category, cond, location, images)

         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'

    );

    $stmt->execute([$user['id'], $title, $description, $price, $category, $cond, $location, $imagesJson]);

    $productId = (int)$db->lastInsertId();

    jsonResponse(true, [

        'id'          => $productId,

        'title'       => $title,

        'price'       => $price,

        'category'    => $category,

        'cond'        => $cond,

        'location'    => $location,

        'images'      => $imagePaths,

        'seller_name' => $user['name'],

    ], '', 201);

}

// ---- DELETE ----

if ($method === 'DELETE') {

    $user = requireAuth();

    $id   = (int)($_GET['id'] ?? 0);

    if (!$id) { jsonResponse(false, null, 'Product ID required.', 422); }

    // Verify ownership

    $stmt = $db->prepare('SELECT seller_id FROM products WHERE id = ?');

    $stmt->execute([$id]);

    $product = $stmt->fetch();

    if (!$product) { jsonResponse(false, null, 'Product not found.', 404); }

    if ($product['seller_id'] !== $user['id']) { jsonResponse(false, null, 'Not your product.', 403); }

    $db->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([$id]);

    jsonResponse(true, ['message' => 'Product removed.']);

}

jsonResponse(false, null, 'Method not allowed.', 405);