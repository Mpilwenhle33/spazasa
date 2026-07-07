<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Receipt</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .receipt-page { min-height: 100vh; background: var(--cream); padding: 20px; }
        .receipt-container { max-width: 800px; margin: 0 auto; background: var(--white); border-radius: var(--radius-lg); padding: 40px; box-shadow: var(--shadow); }
        .receipt-header { text-align: center; margin-bottom: 30px; }
        .receipt-header h1 { font-size: 28px; font-weight: 700; margin-bottom: 10px; }
        .receipt-header p { color: #888; }
        .payment-ref { background: var(--cream); padding: 20px; border-radius: var(--radius); margin-bottom: 30px; }
        .payment-ref h3 { font-size: 16px; margin-bottom: 10px; }
        .payment-ref .ref { font-size: 20px; font-weight: 700; color: var(--teal-dark); }
        .order-box { border: 1px solid #eee; border-radius: var(--radius); padding: 20px; margin-bottom: 15px; }
        .order-box h4 { margin-bottom: 10px; }
        .order-box ul { list-style: none; padding: 0; }
        .order-box ul li { padding: 8px 0; border-bottom: 1px solid #f5f5f5; }
        .order-box ul li:last-child { border-bottom: none; }
        .total-row { text-align: right; padding: 20px 0; border-top: 2px solid #333; font-size: 22px; font-weight: 700; }
        .actions { text-align: center; margin-top: 30px; }
        .btn-print { padding: 10px 30px; background: var(--teal); color: white; border: none; border-radius: var(--radius-pill); font-weight: 700; cursor: pointer; margin: 5px; }
        .btn-print:hover { background: var(--teal-dark); }
        .btn-continue { display: inline-block; padding: 10px 30px; background: var(--brown); color: white; border-radius: var(--radius-pill); text-decoration: none; margin: 5px; }
        .btn-continue:hover { background: var(--brown-mid); }
    </style>
</head>
<body>

<div class="receipt-page">
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Order Confirmed! 🎉</h1>
            <p>Thank you for your purchase on SpazaSa</p>
        </div>

        <div class="payment-ref">
            <h3>Payment Reference</h3>
            <div class="ref"><?= htmlspecialchars($paymentRef) ?></div>
            <p style="font-size:13px;color:#888;margin-top:5px;">Date: <?= date('Y-m-d H:i:s') ?></p>
        </div>

        <h2 style="font-size:20px;margin-bottom:20px;">Order Details</h2>

        <?php foreach ($orders as $order): ?>
            <div class="order-box">
                <p><strong>Seller:</strong> <?= htmlspecialchars($order['seller_name'] ?? $order['seller_username']) ?></p>
                <p><strong>Order Total:</strong> R<?= number_format($order['total_amount'], 2) ?></p>
                <h4 style="margin-top:15px;">Items:</h4>
                <ul>
                    <?php foreach ($allItems as $item): ?>
                        <?php if ($item['order_id'] == $order['order_id']): ?>
                            <li><?= htmlspecialchars($item['description']) ?> — R<?= number_format($item['price'], 2) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <div class="total-row">
            Total: R<?= number_format($totalAmount, 2) ?>
        </div>

        <div class="actions">
            <button onclick="window.print()" class="btn-print">🖨️ Print Receipt</button>
            <a href="index.php?action=marketplace" class="btn-continue">Continue Shopping</a>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
