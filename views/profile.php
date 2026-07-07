<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-page { min-height: 100vh; background: var(--cream); padding: 20px; }
        .profile-container { max-width: 900px; margin: 0 auto; background: var(--white); border-radius: var(--radius-lg); padding: 30px; box-shadow: var(--shadow); }
        .profile-header { display: flex; gap: 30px; align-items: center; margin-bottom: 30px; flex-wrap: wrap; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--teal); }
        .profile-info h1 { font-size: 28px; margin-bottom: 5px; }
        .profile-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { background: var(--cream); padding: 15px; border-radius: var(--radius); text-align: center; }
        .stat-box .number { font-size: 24px; font-weight: 700; color: var(--teal-dark); }
        .stat-box .label { font-size: 12px; color: #888; }
        .back-link { display: inline-block; margin-bottom: 20px; color: var(--teal); text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; }
        .profile-form .form-group { margin-bottom: 15px; }
        .profile-form label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
        .profile-form input, .profile-form textarea { width: 100%; padding: 12px 14px; border: 1.5px solid #ddd; border-radius: var(--radius); font-size: 14px; transition: border-color 0.2s; }
        .profile-form input:focus, .profile-form textarea:focus { border-color: var(--teal); outline: none; }
        .profile-form textarea { min-height: 100px; resize: vertical; }
        .btn-save { padding: 12px 30px; background: var(--teal); color: white; border: none; border-radius: var(--radius-pill); font-weight: 700; font-size: 14px; cursor: pointer; transition: background 0.2s; }
        .btn-save:hover { background: var(--teal-dark); }
        .section-title { font-size: 20px; font-weight: 700; margin-top: 30px; margin-bottom: 15px; border-bottom: 2px solid var(--cream); padding-bottom: 10px; }
        .order-table { width: 100%; border-collapse: collapse; }
        .order-table th { padding: 10px; border: 1px solid #ddd; text-align: left; background: var(--cream); }
        .order-table td { padding: 10px; border: 1px solid #ddd; }
        .order-table code { background: #f5f5f5; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
        .empty-message { color: #888; padding: 20px 0; }
    </style>
</head>
<body>

<div class="profile-page">
    <div class="profile-container">
        <a href="index.php?action=marketplace" class="back-link">Back to Marketplace</a>
        
        <?php if (isset($_SESSION['profile_success'])): ?>
            <div style="padding:10px;margin-bottom:20px;background:#f0fff4;border:1px solid #28a745;border-radius:var(--radius);">
                <p style="color:#28a745;"><?= htmlspecialchars($_SESSION['profile_success']) ?></p>
            </div>
            <?php unset($_SESSION['profile_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['profile_errors'])): ?>
            <div style="padding:10px;margin-bottom:20px;background:#fff3f3;border:1px solid #e74c3c;border-radius:var(--radius);">
                <?php foreach ($_SESSION['profile_errors'] as $error): ?>
                    <p style="color:#e74c3c;margin:5px 0;"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['profile_errors']); ?>
        <?php endif; ?>

        <div class="profile-header">
            <img class="profile-avatar" src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name'] ?? $user['username']) ?>&size=120&background=5bbcb8&color=fff" alt="<?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>">
            <div class="profile-info">
                <h1><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h1>
                <p style="color:#888;"><?= htmlspecialchars($user['email']) ?></p>
                <p style="color:#888;">Location: <?= htmlspecialchars($user['location'] ?? 'Not set') ?></p>
                <p style="color:#888;">Joined: <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
            </div>
        </div>

        <div class="profile-stats">
            <div class="stat-box">
                <div class="number"><?= count($purchases) ?></div>
                <div class="label">Purchases</div>
            </div>
            <div class="stat-box">
                <div class="number"><?= count($sales) ?></div>
                <div class="label">Sales</div>
            </div>
            <div class="stat-box">
                <div class="number"><?= count($listings) ?></div>
                <div class="label">Listings</div>
            </div>
        </div>

        <h2 class="section-title">Edit Profile</h2>
        <form class="profile-form" method="POST" action="index.php?action=update-profile">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Postal Code</label>
                <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>">
            </div>

            <h3 style="margin-top:25px;font-size:16px;font-weight:700;">Change Password</h3>
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password">
            </div>

            <button type="submit" class="btn-save">Update Profile</button>
        </form>

        <h2 class="section-title">Purchase History</h2>
        <?php if (empty($purchases)): ?>
            <p class="empty-message">No purchases yet.</p>
        <?php else: ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Seller</th>
                        <th>Reference</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td><?= htmlspecialchars($purchase['description'] ?? 'Item') ?></td>
                            <td>R<?= number_format($purchase['price'] ?? $purchase['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($purchase['seller_name'] ?? 'N/A') ?></td>
                            <td><code><?= htmlspecialchars($purchase['payment_reference']) ?></code></td>
                            <td><?= date('Y-m-d', strtotime($purchase['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2 class="section-title">Sales History</h2>
        <?php if (empty($sales)): ?>
            <p class="empty-message">No sales yet.</p>
        <?php else: ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Buyer</th>
                        <th>Reference</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= htmlspecialchars($sale['description'] ?? 'Item') ?></td>
                            <td>R<?= number_format($sale['price'] ?? $sale['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($sale['buyer_name'] ?? 'N/A') ?></td>
                            <td><code><?= htmlspecialchars($sale['payment_reference']) ?></code></td>
                            <td><?= date('Y-m-d', strtotime($sale['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
