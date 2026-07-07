<?php
// views/admin/login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Admin Login</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body {
            min-height: 100vh;
            background: var(--admin-darker);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-box {
            background: var(--admin-white);
            padding: 40px;
            border-radius: var(--radius);
            max-width: 400px;
            width: 100%;
            box-shadow: var(--shadow);
        }
        .login-box h1 {
            color: var(--admin-brown);
            font-size: 28px;
            margin-bottom: 8px;
        }
        .login-box .sub {
            color: #888;
            margin-bottom: 30px;
        }
        .login-box .form-group {
            margin-bottom: 15px;
        }
        .login-box label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .login-box input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #ddd;
            border-radius: var(--radius);
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .login-box input:focus {
            border-color: var(--admin-teal);
            outline: none;
        }
        .login-box .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--admin-teal);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-box .btn-login:hover {
            background: var(--admin-teal-dark);
        }
        .login-box .error {
            background: #fff3f3;
            border: 1px solid #e74c3c;
            border-radius: var(--radius);
            padding: 10px;
            color: #e74c3c;
            margin-bottom: 15px;
        }
        .login-box .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--admin-teal);
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h1>SpazaSa</h1>
    <p class="sub">Admin Portal</p>

    <?php if (isset($_SESSION['admin_login_error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['admin_login_error']) ?></div>
        <?php unset($_SESSION['admin_login_error']); ?>
    <?php endif; ?>

    <form method="POST" action="admin.php?action=do-login">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="admin@spaza.co.za" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••" required>
        </div>
        <button type="submit" class="btn-login">Sign In</button>
    </form>

    <a href="index.php" class="back-link">← Back to Site</a>
</div>

</body>
</html>