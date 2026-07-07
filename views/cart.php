<?php
// views/cart.php - No emojis, consistent theme
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Cart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-page { min-height: 100vh; background: var(--cream); padding: 20px; }
        .cart-container { max-width: 860px; margin: 0 auto; }
        .cart-heading { font-size: 26px; font-weight: 700; margin-bottom: 24px; }
        .cart-empty { text-align: center; padding: 60px 20px; background: var(--white); border-radius: var(--radius-lg); }
        .cart-item {
            background: var(--white); border-radius: var(--radius-lg);
            padding: 16px; display: flex; align-items: center; gap: 14px;
            margin-bottom: 12px; box-shadow: var(--shadow);
        }
        .cart-item-img { width: 80px; height: 80px; border-radius: var(--radius); object-fit: cover; flex-shrink: 0; }
        .cart-item-info { flex: 1; }
        .cart-item-name { font-size: 15px; font-weight: 700; }
        .cart-item-price { font-size: 18px; font-weight: 700; color: var(--teal-dark); }
        .btn-remove {
            background: none; border: 1.5px solid #ddd; color: #999;
            padding: 6px 14px; border-radius: var(--radius-pill);
            font-size: 11px; font-weight: 600; transition: all 0.2s;
            cursor: pointer;
        }
        .btn-remove:hover { border-color: #e74c3c; color: #e74c3c; }
        .cart-summary {
            background: var(--white); border-radius: var(--radius-lg);
            padding: 20px; margin-top: 20px; box-shadow: var(--shadow);
        }
        .cart-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 700; }
        .btn-checkout {
            width: 100%; padding: 14px; background: var(--teal); color: var(--white);
            border: none; border-radius: var(--radius); font-size: 16px; font-weight: 700;
            margin-top: 16px; cursor: pointer;
        }
        .btn-checkout:hover { background: var(--teal-dark); }
        .btn-continue {
            display: inline-block; margin-top: 16px; color: var(--teal); text-decoration: none;
        }
        .btn-continue:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="cart-page">
    <div class="cart-container">
        <h1 class="cart-heading">Your Cart</h1>

        <?php if (empty($items)): ?>
            <div class="cart-empty">
                <p>Your cart is empty.</p>
                <a href="index.php?action=marketplace" class="btn-continue">Continue Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <img class="cart-item-img" src="<?= htmlspecialchars($item['image'] ?? 'assets/uploads/default-product.jpg') ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                    <div class="cart-item-info">
                        <div class="cart-item-name"><?= htmlspecialchars($item['title']) ?></div>
                        <div style="font-size:13px;color:#666;">Condition: <?= htmlspecialchars($item['cond']) ?></div>
                        <div class="cart-item-price">R<?= number_format($item['price'], 2) ?></div>
                        <?php if ($item['quantity'] > 1): ?>
                            <div style="font-size:13px;color:#888;">Qty: <?= $item['quantity'] ?></div>
                        <?php endif; ?>
                    </div>
                    <button class="btn-remove" onclick="removeFromCart(<?= $item['product_id'] ?>)">Remove</button>
                </div>
            <?php endforeach; ?>

            <div class="cart-summary">
                <div class="cart-total">
                    <span>Total</span>
                    <span>R<?= number_format($total, 2) ?></span>
                </div>
                <form method="POST" action="index.php?action=checkout">
                    <button type="submit" class="btn-checkout">Proceed to Checkout</button>
                </form>
                <a href="index.php?action=marketplace" class="btn-continue">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function removeFromCart(productId) {
    if (!confirm('Remove this item from cart?')) return;
    window.location = 'index.php?action=remove-from-cart&id=' + productId;
}
</script>

<script src="assets/js/app.js"></script>
</body>
</html>