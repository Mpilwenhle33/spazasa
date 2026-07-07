<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — <?= t('marketplace') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: default;
        }
        .user-display {
            font-size: 12px;
            font-weight: 700;
            color: var(--teal);
            padding: 7px 12px;
            background: #e6f8f7;
            border-radius: var(--radius-pill);
            text-decoration: none;
            transition: background 0.2s;
        }
        .user-display:hover {
            background: #d4ecea;
        }
        .nav-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--teal);
            cursor: pointer;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--white);
            min-width: 180px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 8px 0;
            z-index: 200;
            margin-top: 4px;
        }
        .dropdown-menu.open {
            display: block;
        }
        .dropdown-menu a {
            display: block;
            padding: 10px 16px;
            color: var(--charcoal);
            text-decoration: none;
            font-size: 13px;
            transition: background 0.2s;
        }
        .dropdown-menu a:hover {
            background: var(--cream);
        }
        .dropdown-divider {
            border-top: 1px solid #eee;
            margin: 6px 0;
        }
        .icon-btn {
            background: none;
            border: none;
            font-size: 16px;
            padding: 5px 7px;
            color: var(--charcoal);
            border-radius: var(--radius);
            transition: background 0.2s;
            flex-shrink: 0;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }
        .icon-btn:hover {
            background: #eee;
        }
        .cart-nav-btn {
            position: relative;
        }
        .cart-badge-nav {
            position: absolute;
            top: -4px;
            right: -6px;
            background: #e74c3c;
            color: white;
            font-size: 9px;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        .messages-badge {
            position: absolute;
            top: -4px;
            right: -6px;
            background: #e74c3c;
            color: white;
            font-size: 9px;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        @media (max-width: 500px) {
            .user-display {
                font-size: 10px;
                padding: 5px 8px;
            }
            .nav-avatar {
                width: 28px;
                height: 28px;
            }
        }
    </style>
    <script>
        const SESSION = {
            userId: <?= json_encode($_SESSION['user_id'] ?? null) ?>,
            userName: <?= json_encode($_SESSION['full_name'] ?? null) ?>,
            isLoggedIn: <?= json_encode(isset($_SESSION['user_id'])) ?>
        };
        const LANG = <?= json_encode($currentLang) ?>;
    </script>
</head>
<body>

<div id="page-marketplace" class="page-section active">
    <div class="marketplace-page">
        <nav class="main-nav">
            <div class="logo" style="color:var(--teal);">SpazaSa</div>

            <button class="icon-btn" onclick="window.location='index.php?action=language'" title="<?= t('language') ?>">
                Language
            </button>
            <button class="icon-btn" onclick="window.location='index.php?action=home'" title="<?= t('home') ?>">
                Home
            </button>

            <div class="search-bar">
                <span class="search-icon-left">&#128269;</span>
                <input type="text" id="searchInput" placeholder="<?= t('search') ?>" 
                       onkeydown="if(event.key==='Enter') window.location='index.php?action=search&q='+encodeURIComponent(this.value)" />
            </div>

            <button class="icon-btn cart-nav-btn" onclick="window.location='index.php?action=cart'" title="<?= t('cart') ?>">
                Cart
                <span class="cart-badge-nav" id="cartBadgeNav">0</span>
            </button>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-menu">
                    <a href="index.php?action=profile" class="user-display" id="userDisplay">
                        <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?>
                    </a>
                    
                    <a href="index.php?action=messages" class="icon-btn" title="Messages" style="position:relative;font-weight:600;">
                        Messages
                        <?php
                        require_once __DIR__ . '/../models/Message.php';
                        $messageModel = new Message();
                        $unread = $messageModel->getUnreadCount($_SESSION['user_id']);
                        ?>
                        <?php if ($unread > 0): ?>
                            <span class="messages-badge"><?= $unread ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <img class="nav-avatar" id="userAvatar" src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['full_name'] ?? $_SESSION['username']) ?>&size=34&background=5bbcb8&color=fff" alt="User" onclick="toggleUserMenu()" />
                    
                    <div class="dropdown-menu" id="userDropdown">
                        <a href="index.php?action=profile">Profile</a>
                        <a href="index.php?action=messages">Messages</a>
                        <a href="index.php?action=marketplace&seller=<?= $_SESSION['user_id'] ?>">My Listings</a>
                        <a href="index.php?action=orders">My Orders</a>
                        <div class="dropdown-divider"></div>
                        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'moderator'): ?>
                            <a href="admin.php">Admin Panel</a>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <a href="index.php?action=logout" style="color:#e74c3c;">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <button class="btn-auth" onclick="window.location='index.php?action=login'"><?= t('login') ?></button>
                <button class="btn-auth" onclick="window.location='index.php?action=register'" style="background:var(--teal);color:white;"><?= t('sign_up') ?></button>
            <?php endif; ?>
        </nav>
        <div class="category-tabs" id="categoryTabs">
            <button class="cat-tab active" onclick="window.location='index.php?action=marketplace'">ALL</button>
            <?php foreach ($categories as $cat): ?>
                <button class="cat-tab" onclick="window.location='index.php?action=category&id=<?= $cat['category_id'] ?>'">
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endforeach; ?>
            <?php if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'moderator')): ?>
                <button class="cat-tab" onclick="window.location='admin.php'" style="border-color:var(--teal);color:var(--teal);">MANAGE</button>
            <?php endif; ?>
        </div>
        <div class="products-section">
            <div class="products-grid" id="productsGrid">
                <?php if (empty($products)): ?>
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:#ddd;font-size:15px;">
                        <?= t('no_items') ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" onclick="window.location='index.php?action=detail&id=<?= $product['product_id'] ?>'">
                            <?php if ($product['status'] === 'sold'): ?>
                                <span style="position:absolute;top:10px;left:10px;background:#e74c3c;color:white;padding:3px 10px;border-radius:50px;font-size:10px;font-weight:700;"><?= t('sold') ?></span>
                            <?php elseif ($product['cond'] === 'New'): ?>
                                <span style="position:absolute;top:10px;left:10px;background:var(--teal);color:white;padding:3px 10px;border-radius:50px;font-size:10px;font-weight:700;"><?= t('new') ?></span>
                            <?php endif; ?>
                            
                            <img class="product-img" src="<?= htmlspecialchars($product['cover_image'] ?? 'assets/uploads/default-product.jpg') ?>" 
                                 alt="<?= htmlspecialchars($product['title']) ?>" loading="lazy" />
                            
                            <div class="product-info">
                                <div class="product-price">R<?= number_format($product['price'], 2) ?></div>
                                <div class="product-name"><?= htmlspecialchars($product['title']) ?></div>
                                <div class="product-location"><?= htmlspecialchars($product['location'] ?? '') ?></div>
                            </div>
                            
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['seller_id']): ?>
                                <div style="padding:8px 10px;display:flex;gap:8px;">
                                    <a href="index.php?action=edit-product&id=<?= $product['product_id'] ?>" style="font-size:11px;color:var(--teal);text-decoration:none;"><?= t('edit') ?></a>
                                    <a href="index.php?action=delete-product&id=<?= $product['product_id'] ?>" style="font-size:11px;color:#e74c3c;text-decoration:none;" onclick="event.stopPropagation();return confirm('Delete this item?');"><?= t('delete') ?></a>
                                </div>
                            <?php else: ?>
                                <button class="btn-view" onclick="event.stopPropagation();window.location='index.php?action=detail&id=<?= $product['product_id'] ?>'">
                                    <?= $product['status'] === 'sold' ? t('sold') : t('view') ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="fab-sell" onclick="window.location='index.php?action=sell'" title="<?= t('sell') ?>">+</button>
        <?php endif; ?>
    </div>
</div>

<div class="toast" id="toast"></div>

<script src="assets/js/app.js"></script>
</body>
</html>
