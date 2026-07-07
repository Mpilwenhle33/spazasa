<?php
// views/admin/users.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Admin Users</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h2>SpazaSa Admin</h2>
        <ul>
            <li><a href="admin.php?action=dashboard">Dashboard</a></li>
            <li><a href="admin.php?action=users" class="active">Users</a></li>
            <li><a href="admin.php?action=products&status=pending">Pending Products</a></li>
            <li><a href="admin.php?action=products&status=approved">Approved Products</a></li>
            <li><a href="index.php?action=logout">Logout</a></li>
        </ul>
    </aside>
    
    <main class="admin-main">
        <h1 style="font-size:28px;font-weight:700;margin-bottom:20px;">User Management</h1>
        
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
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><span class="status-badge <?= $user['role'] ?>"><?= htmlspecialchars($user['role']) ?></span></td>
                            <td><?= htmlspecialchars($user['location'] ?? '') ?></td>
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