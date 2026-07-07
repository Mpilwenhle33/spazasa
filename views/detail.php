<?php
// views/detail.php - No emojis, consistent theme
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — <?= htmlspecialchars($product['title']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .detail-actions {
            display: flex;
            gap: 12px;
            padding: 16px 20px 20px;
            flex-wrap: wrap;
        }
        .btn-buy, .btn-wish, .btn-sold {
            flex: 1;
            min-width: 120px;
            padding: 14px 20px;
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .btn-buy {
            background: var(--teal);
            color: var(--white);
        }
        .btn-buy:hover {
            background: var(--teal-dark);
            transform: translateY(-2px);
        }
        .btn-wish {
            background: var(--brown);
            color: var(--white);
        }
        .btn-wish:hover {
            background: var(--brown-mid);
            transform: translateY(-2px);
        }
        .btn-sold {
            background: #888;
            color: white;
            cursor: not-allowed;
        }
        .btn-chat {
            width: 100%;
            padding: 14px;
            background: var(--charcoal);
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-chat:hover {
            background: var(--brown);
        }
        @media (max-width: 500px) {
            .detail-actions {
                flex-direction: column;
            }
            .btn-buy, .btn-wish, .btn-sold {
                min-width: 100%;
            }
        }
    </style>
    <script>
        const PRODUCT_ID = <?= $product['product_id'] ?>;
        const SELLER_ID = <?= $product['seller_id'] ?>;
        const SESSION_USER_ID = <?= json_encode($_SESSION['user_id'] ?? null) ?>;
    </script>
</head>
<body>

<div style="min-height:100vh;background:var(--cream);padding:20px;">
    <div style="max-width:900px;margin:0 auto;">
        <button class="back-btn" onclick="window.location='index.php?action=marketplace'">Back</button>
        
        <div class="detail-card">
            <?php if (!empty($images)): ?>
                <div style="position:relative;">
                    <img id="detailImage" class="detail-img" src="<?= htmlspecialchars($images[0]['image_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" style="width:100%;max-height:400px;object-fit:cover;">
                    <?php if (count($images) > 1): ?>
                        <div style="position:absolute;bottom:15px;left:50%;transform:translateX(-50%);display:flex;gap:8px;">
                            <?php foreach ($images as $index => $img): ?>
                                <button onclick="changeImage(<?= $index ?>)" style="width:12px;height:12px;border-radius:50%;border:none;background:<?= $index === 0 ? 'var(--teal)' : '#ddd' ?>;cursor:pointer;"></button>
                            <?php endforeach; ?>
                        </div>
                        <button onclick="prevImage()" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:white;border:none;padding:10px 15px;border-radius:50%;cursor:pointer;font-size:20px;">‹</button>
                        <button onclick="nextImage()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:white;border:none;padding:10px 15px;border-radius:50%;cursor:pointer;font-size:20px;">›</button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <img class="detail-img" src="assets/uploads/default-product.jpg" alt="<?= htmlspecialchars($product['title']) ?>" style="width:100%;max-height:400px;object-fit:cover;">
            <?php endif; ?>
            
            <div class="detail-info">
                <h1 class="detail-title"><?= htmlspecialchars($product['title']) ?></h1>
                <div class="detail-price">R<?= number_format($product['price'], 2) ?></div>
                <div class="detail-meta"><strong>Condition:</strong> <?= htmlspecialchars($product['cond']) ?></div>
                <div class="detail-meta"><strong>Location:</strong> <?= htmlspecialchars($product['location'] ?? 'N/A') ?></div>
                <div class="detail-meta"><strong>Seller:</strong> <?= htmlspecialchars($product['seller_name'] ?? $product['seller_username']) ?></div>
                <?php if ($product['description']): ?>
                    <div style="margin-top:15px;padding-top:15px;border-top:1px solid #eee;">
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="detail-actions">
                <?php if ($product['status'] !== 'sold' && isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['seller_id']): ?>
                    <button class="btn-buy" onclick="buyNow(<?= $product['product_id'] ?>)">Buy Now</button>
                    <button class="btn-wish" onclick="addToCart(<?= $product['product_id'] ?>)">Add to Cart</button>
                <?php elseif ($product['status'] === 'sold'): ?>
                    <button class="btn-sold">Sold</button>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['seller_id']): ?>
                <div style="padding:0 20px 20px;">
                    <button class="btn-chat" onclick="openChat(<?= $product['seller_id'] ?>, <?= $product['product_id'] ?>, '<?= htmlspecialchars($product['seller_name'] ?? $product['seller_username']) ?>')">
                        Chat with Seller
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chat Overlay -->
<div class="chat-overlay" id="chatOverlay">
    <div class="chat-header">
        <span id="chatSellerName">Seller</span>
        <button class="back" onclick="closeChat()">✕</button>
    </div>
    <div class="chat-messages" id="chatMessages">
        <div style="text-align:center;color:#888;padding:20px;">No messages yet</div>
    </div>
    <div class="chat-input">
        <input type="text" id="chatInput" placeholder="Type a message..." onkeydown="if(event.key==='Enter') sendChat()">
        <button onclick="sendChat()">Send</button>
    </div>
</div>

<script>
// Image gallery
let currentImage = 0;
const images = <?= json_encode(array_column($images, 'image_path')) ?>;

function changeImage(index) {
    currentImage = index;
    document.getElementById('detailImage').src = images[index];
    const dots = document.querySelectorAll('.detail-card button[style*="width:12px"]');
    dots.forEach((dot, i) => {
        dot.style.background = i === index ? 'var(--teal)' : '#ddd';
    });
}

function prevImage() {
    if (currentImage > 0) changeImage(currentImage - 1);
}

function nextImage() {
    if (currentImage < images.length - 1) changeImage(currentImage + 1);
}

function showToast(msg) {
    let t = document.getElementById('toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'toast';
        t.className = 'toast';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._timeout);
    t._timeout = setTimeout(() => t.classList.remove('show'), 2800);
}

async function buyNow(productId) {
    if (!SESSION_USER_ID) {
        showToast('Please login first');
        window.location = 'index.php?action=login';
        return;
    }
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        const res = await fetch('index.php?action=add-to-cart', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Added to cart! Redirecting to checkout...');
            setTimeout(() => { window.location = 'index.php?action=checkout'; }, 1000);
        } else {
            showToast(data.error || 'Failed to add to cart');
        }
    } catch (e) {
        showToast('Network error');
    }
}

async function addToCart(productId) {
    if (!SESSION_USER_ID) {
        showToast('Please login first');
        window.location = 'index.php?action=login';
        return;
    }
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        const res = await fetch('index.php?action=add-to-cart', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Added to cart!');
            refreshCartBadge();
        } else {
            showToast(data.error || 'Failed to add to cart');
        }
    } catch (e) {
        showToast('Network error');
    }
}

async function refreshCartBadge() {
    try {
        const res = await fetch('index.php?action=cart-count');
        const data = await res.json();
        const badge = document.getElementById('cartBadgeNav');
        if (badge) badge.textContent = data.count || 0;
    } catch (e) {}
}

// Chat functions
let chatUserId = null, chatProductId = null, chatLastId = 0, chatPollInterval = null;

function openChat(userId, productId, userName) {
    chatUserId = userId; chatProductId = productId; chatLastId = 0;
    const overlay = document.getElementById('chatOverlay');
    if (overlay) {
        document.getElementById('chatSellerName').textContent = userName || 'Seller';
        overlay.classList.add('open');
        loadChatMessages();
        if (chatPollInterval) clearInterval(chatPollInterval);
        chatPollInterval = setInterval(pollNewMessages, 3000);
    }
}

function closeChat() {
    document.getElementById('chatOverlay')?.classList.remove('open');
    if (chatPollInterval) { clearInterval(chatPollInterval); chatPollInterval = null; }
}

async function loadChatMessages() {
    if (!chatUserId) return;
    const container = document.getElementById('chatMessages');
    container.innerHTML = '<div style="text-align:center;padding:20px;color:#888;">Loading...</div>';
    try {
        const res = await fetch(`index.php?action=get-messages&user=${chatUserId}`);
        const data = await res.json();
        if (data.success) {
            renderChatMessages(data.messages);
            if (data.messages.length > 0) chatLastId = data.messages[data.messages.length - 1].message_id;
        } else {
            container.innerHTML = '<div style="text-align:center;padding:20px;color:#e74c3c;">Error loading messages</div>';
        }
    } catch (e) {
        container.innerHTML = '<div style="text-align:center;padding:20px;color:#e74c3c;">Network error</div>';
    }
}

function renderChatMessages(messages) {
    const container = document.getElementById('chatMessages');
    if (!messages || messages.length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:20px;color:#888;">No messages yet. Say hello!</div>';
        return;
    }
    container.innerHTML = '';
    messages.forEach(msg => {
        const isOwn = msg.sender_id == SESSION_USER_ID;
        const div = document.createElement('div');
        div.className = 'chat-msg ' + (isOwn ? 'sent' : 'received');
        div.textContent = msg.message_text;
        container.appendChild(div);
    });
    container.scrollTop = container.scrollHeight;
}

async function sendChat() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message || !chatUserId) return;
    input.value = '';
    try {
        const formData = new FormData();
        formData.append('receiver_id', chatUserId);
        formData.append('message', message);
        if (chatProductId) formData.append('product_id', chatProductId);
        const res = await fetch('index.php?action=send-message', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            const container = document.getElementById('chatMessages');
            const div = document.createElement('div');
            div.className = 'chat-msg sent';
            div.textContent = message;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
            chatLastId++;
        } else {
            showToast('Failed to send message');
        }
    } catch (e) {
        showToast('Network error');
    }
}

async function pollNewMessages() {
    if (!chatUserId) return;
    try {
        const res = await fetch(`index.php?action=get-messages&user=${chatUserId}&last_id=${chatLastId}`);
        const data = await res.json();
        if (data.success && data.messages.length > 0) {
            const container = document.getElementById('chatMessages');
            const placeholder = container.querySelector('div[style*="text-align:center"]');
            if (placeholder) container.innerHTML = '';
            data.messages.forEach(msg => {
                const isOwn = msg.sender_id == SESSION_USER_ID;
                const div = document.createElement('div');
                div.className = 'chat-msg ' + (isOwn ? 'sent' : 'received');
                div.textContent = msg.message_text;
                container.appendChild(div);
            });
            chatLastId = data.messages[data.messages.length - 1].message_id;
            container.scrollTop = container.scrollHeight;
        }
    } catch (e) {}
}
</script>

<div class="toast" id="toast"></div>
<script src="assets/js/app.js"></script>
</body>
</html>