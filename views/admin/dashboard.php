<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h2>SpazaSa Admin</h2>
        <ul>
            <li><a href="admin.php?action=dashboard" class="active">Dashboard</a></li>
            <li><a href="admin.php?action=users">Users</a></li>
            <li><a href="admin.php?action=products&status=pending">Pending Products</a></li>
            <li><a href="admin.php?action=products&status=approved">Approved Products</a></li>
            <li><a href="index.php?action=logout">Logout</a></li>
        </ul>
    </aside>
    
    <main class="admin-main">
        <h1 style="font-size:28px;font-weight:700;margin-bottom:20px;">Dashboard</h1>
        
        <div class="admin-stats">
            <div class="admin-stat">
                <div class="label">Total Users</div>
                <div class="value"><?= $stats['total_users'] ?></div>
            </div>
            <div class="admin-stat">
                <div class="label">Total Products</div>
                <div class="value"><?= $stats['total_products'] ?></div>
            </div>
            <div class="admin-stat">
                <div class="label">Pending Products</div>
                <div class="value"><?= $stats['pending_products'] ?></div>
            </div>
            <div class="admin-stat">
                <div class="label">Total Orders</div>
                <div class="value"><?= $stats['total_orders'] ?></div>
            </div>
            <div class="admin-stat">
                <div class="label">Total Revenue</div>
                <div class="value">R<?= number_format($stats['total_revenue'], 2) ?></div>
            </div>
        </div>
        
        <div class="admin-table-wrap">
            <h2 style="font-size:18px;margin-bottom:15px;">Recent Orders</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Buyer</th>
                        <th>Seller</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?= $order['order_id'] ?></td>
                            <td><?= htmlspecialchars($order['buyer_username']) ?></td>
                            <td><?= htmlspecialchars($order['seller_username']) ?></td>
                            <td>R<?= number_format($order['total_amount'], 2) ?></td>
                            <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
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
