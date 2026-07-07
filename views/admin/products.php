<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Admin Products</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h2>SpazaSa Admin</h2>
        <ul>
            <li><a href="admin.php?action=dashboard">Dashboard</a></li>
            <li><a href="admin.php?action=users">Users</a></li>
            <li><a href="admin.php?action=products" class="active">Products</a></li>
            <li><a href="index.php?action=logout">Logout</a></li>
        </ul>
    </aside>
    
    <main class="admin-main">
        <h1 style="font-size:28px;font-weight:700;margin-bottom:20px;">All Products</h1>
        
        <?php if (isset($_SESSION['admin_success'])): ?>
            <div style="padding:10px;margin-bottom:20px;background:#f0fff4;border:1px solid #28a745;border-radius:var(--radius);">
                <p style="color:#28a745;"><?= htmlspecialchars($_SESSION['admin_success']) ?></p>
            </div>
            <?php unset($_SESSION['admin_success']); ?>
        <?php endif; ?>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Seller</th>
                        <th>Price</th>
                        <th>Condition</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['product_id'] ?></td>
                            <td><?= htmlspecialchars($product['title']) ?></td>
                            <td><?= htmlspecialchars($product['seller_name'] ?? $product['seller_username']) ?></td>
                            <td>R<?= number_format($product['price'], 2) ?></td>
                            <td><?= htmlspecialchars($product['cond']) ?></td>
                            <td>
                                <a href="admin.php?action=delete&id=<?= $product['product_id'] ?>" class="btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="assets/js/admin.js"></script>
</body>
</html>
