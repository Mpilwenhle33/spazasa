<?php
// views/login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div style="min-height:100vh;background:var(--cream);display:flex;align-items:center;justify-content:center;padding:20px;">
    <div style="max-width:450px;width:100%;background:white;padding:40px;border-radius:var(--radius-lg);box-shadow:var(--shadow);">
        <h1 style="font-size:24px;font-weight:700;margin-bottom:20px;">Welcome Back</h1>
        
        <?php if (isset($_SESSION['login_error'])): ?>
            <div style="padding:10px;margin-bottom:20px;background:#fff3f3;border:1px solid #e74c3c;border-radius:var(--radius);">
                <p style="color:#e74c3c;"><?= htmlspecialchars($_SESSION['login_error']) ?></p>
            </div>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['register_success'])): ?>
            <div style="padding:10px;margin-bottom:20px;background:#f0fff4;border:1px solid #28a745;border-radius:var(--radius);">
                <p style="color:#28a745;"><?= htmlspecialchars($_SESSION['register_success']) ?></p>
            </div>
            <?php unset($_SESSION['register_success']); ?>
        <?php endif; ?>

        <form method="POST" action="index.php?action=do-login">
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Email</label>
                <input type="email" name="email" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Password</label>
                <input type="password" name="password" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <button type="submit" style="width:100%;padding:12px;background:var(--teal);color:white;border:none;border-radius:var(--radius-pill);font-weight:700;font-size:16px;cursor:pointer;">
                Login
            </button>
        </form>
        
        <p style="margin-top:20px;text-align:center;">
            Don't have an account? <a href="index.php?action=register">Sign Up</a>
        </p>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>