<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Sell</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div style="min-height:100vh;background:var(--cream);padding:20px;">
    <div style="max-width:800px;margin:0 auto;background:white;padding:30px;border-radius:var(--radius-lg);box-shadow:var(--shadow);">
        <h1 style="font-size:24px;font-weight:700;margin-bottom:20px;">List Your Item</h1>
        
        <?php if (isset($_SESSION['sell_errors'])): ?>
            <div style="padding:10px;margin-bottom:20px;background:#fff3f3;border:1px solid #e74c3c;border-radius:var(--radius);">
                <?php foreach ($_SESSION['sell_errors'] as $error): ?>
                    <p style="color:#e74c3c;margin:5px 0;"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['sell_errors']); ?>
        <?php endif; ?>

        <?php $data = $_SESSION['sell_data'] ?? []; ?>
        <?php $isEdit = isset($product); ?>
        
        <form method="POST" action="index.php?action=<?= $isEdit ? 'do-edit-product' : 'do-sell' ?>" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <?php endif; ?>
            
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Title</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($isEdit ? $product['title'] : ($data['title'] ?? '')) ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Description</label>
                <textarea name="description" rows="4" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);"><?= htmlspecialchars($isEdit ? ($product['description'] ?? '') : ($data['description'] ?? '')) ?></textarea>
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Price (ZAR)</label>
                <input type="number" name="price" step="0.01" required value="<?= $isEdit ? $product['price'] : ($data['price'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Category</label>
                <select name="category_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= (($isEdit ? ($product['category_id'] ?? '') : ($data['category_id'] ?? '')) == $cat['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['icon'] ?? '') ?> <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Condition</label>
                <select name="cond" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
                    <option value="New" <?= ($isEdit ? ($product['cond'] ?? '') : ($data['cond'] ?? '')) === 'New' ? 'selected' : '' ?>>New</option>
                    <option value="Like New" <?= ($isEdit ? ($product['cond'] ?? '') : ($data['cond'] ?? '')) === 'Like New' ? 'selected' : '' ?>>Like New</option>
                    <option value="Good" <?= ($isEdit ? ($product['cond'] ?? '') : ($data['cond'] ?? '')) === 'Good' || (!isset($data['cond']) && !$isEdit) ? 'selected' : '' ?>>Good</option>
                    <option value="Fair" <?= ($isEdit ? ($product['cond'] ?? '') : ($data['cond'] ?? '')) === 'Fair' ? 'selected' : '' ?>>Fair</option>
                    <option value="Poor" <?= ($isEdit ? ($product['cond'] ?? '') : ($data['cond'] ?? '')) === 'Poor' ? 'selected' : '' ?>>Poor</option>
                </select>
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($isEdit ? ($product['location'] ?? '') : ($data['location'] ?? $_SESSION['location'] ?? '')) ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Postal Code</label>
                <input type="text" name="postal_code" value="<?= htmlspecialchars($isEdit ? ($product['postal_code'] ?? '') : ($data['postal_code'] ?? '')) ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:var(--radius);">
            </div>
            
            <?php if (!$isEdit): ?>
                <div style="margin-bottom:15px;">
                    <label style="display:block;font-weight:600;margin-bottom:5px;">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*" style="padding:10px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;font-weight:600;margin-bottom:5px;">Additional Images</label>
                    <input type="file" name="images[]" multiple accept="image/*" style="padding:10px;">
                </div>
            <?php endif; ?>
            
            <button type="submit" style="padding:12px 30px;background:var(--teal);color:white;border:none;border-radius:var(--radius-pill);font-weight:700;font-size:16px;cursor:pointer;">
                <?= $isEdit ? 'Update' : 'List' ?> Item
            </button>
            <a href="index.php?action=marketplace" style="margin-left:10px;">Cancel</a>
        </form>
    </div>
</div>

<?php unset($_SESSION['sell_data']); ?>
<script src="assets/js/app.js"></script>
</body>
</html>
