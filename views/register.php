<?php
// views/register.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Sign Up</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div style="min-height:100vh;background:var(--cream);display:flex;align-items:center;justify-content:center;padding:20px;">
    <div style="max-width:500px;width:100%;background:white;padding:40px;border-radius:var(--radius-lg);box-shadow:var(--shadow);">
        <h1 style="font-size:24px;font-weight:700;margin-bottom:20px;">Join SpazaSa</h1>
        
        <?php if (isset($_SESSION['register_errors'])): ?>
            <div style="padding:10px;margin-bottom:20px;background:#fff3f3;border:1px solid #e74c3c;border-radius:var(--radius);">
                <?php foreach ($_SESSION['register_errors'] as $error): ?>
                    <p style="color:#e74c3c;margin:5px 0;"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['register_errors']); ?>
        <?php endif; ?>

        <?php $data = $_SESSION['register_data'] ?? []; ?>

        <form method="POST" action="index.php?action=do-register">
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Username</label>
                <input type="text" name="username" required value="<?= htmlspecialchars($data['username'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Full Name</label>
                <input type="text" name="full_name" required value="<?= htmlspecialchars($data['full_name'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($data['email'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Phone (optional)</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Location</label>
                <input type="text" name="location" placeholder="e.g. Midrand" value="<?= htmlspecialchars($data['location'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Postal Code</label>
                <input type="text" name="postal_code" value="<?= htmlspecialchars($data['postal_code'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Password (8+ chars, 1 uppercase, 1 number)</label>
                <input type="password" name="password" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <button type="submit" style="width:100%;padding:12px;background:var(--teal);color:white;border:none;border-radius:var(--radius-pill);font-weight:700;font-size:16px;cursor:pointer;">
                Sign Up
            </button>
        </form>
        
        <p style="margin-top:20px;text-align:center;">
            Already have an account? <a href="index.php?action=login">Login</a>
        </p>
    </div>
</div>

<?php unset($_SESSION['register_data']); ?>
<script src="assets/js/app.js"></script>
</body>
</html>