// =====================================================
// SpazaSa - Complete Application JavaScript
// =====================================================

// =============================================
// SESSION FALLBACK
// =============================================
if (typeof SESSION === 'undefined') {
    var SESSION = {
        userId: 0,
        userName: null,
        isLoggedIn: false
    };
    console.warn('SESSION was not defined, using default.');
}

// =============================================
// GLOBAL VARIABLES (set in PHP views)
// =============================================
// SESSION = { userId, userName, isLoggedIn }
// LANG = 'english' | 'isizulu' | 'sesotho' | 'afrikaans'

// =============================================
// TOAST NOTIFICATION
// =============================================
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

// =============================================
// NAVIGATION HELPERS
// =============================================
function goTo(page, params = {}) {
    let url = 'index.php?action=' + page;
    for (let key in params) {
        url += '&' + key + '=' + encodeURIComponent(params[key]);
    }
    window.location = url;
}

function goToAdmin() {
    window.location = 'admin.php';
}

// =============================================
// USER MENU DROPDOWN
// =============================================
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('open');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('userDropdown');
    const avatar = document.getElementById('userAvatar');
    if (menu && avatar && !avatar.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('open');
    }
});

// =============================================
// CART FUNCTIONS
// =============================================

/**
 * Add item to cart and redirect to checkout (BUY NOW)
 */
async function buyNow(productId) {
    if (!SESSION || !SESSION.isLoggedIn) {
        showToast('Please login first');
        window.location = 'index.php?action=login';
        return;
    }

    try {
        const formData = new FormData();
        formData.append('product_id', productId);

        const url = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/') + 'index.php?action=add-to-cart';

        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.success) {
            showToast('Added to cart! Redirecting to checkout...');
            setTimeout(() => {
                window.location = 'index.php?action=checkout';
            }, 1000);
        } else {
            showToast(data.error || 'Failed to add to cart');
        }
    } catch (e) {
        console.error('Network error:', e);
        showToast('Network error');
    }
}

/**
 * Add item to cart and stay on page (ADD TO CART)
 */
async function addToCart(productId) {
    if (!SESSION || !SESSION.isLoggedIn) {
        showToast('Please login first');
        window.location = 'index.php?action=login';
        return;
    }

    try {
        const formData = new FormData();
        formData.append('product_id', productId);

        const url = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/') + 'index.php?action=add-to-cart';

        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.success) {
            showToast('Added to cart! 🛒');
            refreshCartBadge();
        } else {
            showToast(data.error || 'Failed to add to cart');
        }
    } catch (e) {
        console.error('Network error:', e);
        showToast('Network error');
    }
}

/**
 * Remove item from cart (cart page)
 */
async function removeFromCart(cartItemId) {
    if (!confirm('Remove this item from cart?')) return;
    window.location = 'index.php?action=remove-from-cart&id=' + cartItemId;
}

/**
 * Refresh the cart badge in the navigation
 */
async function refreshCartBadge() {
    try {
        const res = await fetch('index.php?action=cart-count');
        const data = await res.json();
        const badge = document.getElementById('cartBadgeNav');
        if (badge) {
            badge.textContent = data.count || 0;
        }
    } catch (e) {
        // Silent fail
    }
}

// =============================================
// WISHLIST FUNCTIONS (for grid cards)
// =============================================
async function toggleWishlist(productId) {
    if (!SESSION || !SESSION.isLoggedIn) {
        showToast('Please login first');
        window.location = 'index.php?action=login';
        return;
    }

    try {
        const formData = new FormData();
        formData.append('product_id', productId);

        const res = await fetch('index.php?action=wishlist-toggle', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            const msg = data.action === 'added' ? 'Added to wishlist' : 'Removed from wishlist';
            showToast(msg);
            
            const btn = document.querySelector(`.wishlist-btn[data-product-id="${productId}"]`);
            if (btn) {
                btn.textContent = data.action === 'added' ? 'Wish' : 'Wish';
                btn.dataset.inWishlist = data.action === 'added' ? 'true' : 'false';
            }
        }
    } catch (e) {
        showToast('Network error');
    }
}

// =============================================
// CHAT FUNCTIONS
// =============================================
let chatUserId = null;
let chatProductId = null;
let chatLastId = 0;
let chatPollInterval = null;

function openChat(userId, productId, userName) {
    chatUserId = userId;
    chatProductId = productId;
    chatLastId = 0;
    
    const overlay = document.getElementById('chatOverlay');
    if (overlay) {
        const nameEl = document.getElementById('chatSellerName');
        if (nameEl) nameEl.textContent = userName || 'Seller';
        overlay.classList.add('open');
        loadChatMessages();
        if (chatPollInterval) clearInterval(chatPollInterval);
        chatPollInterval = setInterval(pollNewMessages, 3000);
    }
}

function closeChat() {
    const overlay = document.getElementById('chatOverlay');
    if (overlay) overlay.classList.remove('open');
    if (chatPollInterval) {
        clearInterval(chatPollInterval);
        chatPollInterval = null;
    }
}

async function loadChatMessages() {
    if (!chatUserId) return;
    
    const container = document.getElementById('chatMessages');
    if (!container) return;
    container.innerHTML = '<div style="text-align:center;padding:20px;color:#888;">Loading...</div>';
    
    try {
        const res = await fetch(`index.php?action=get-messages&user=${chatUserId}`);
        const data = await res.json();
        
        if (data.success) {
            renderChatMessages(data.messages);
            if (data.messages.length > 0) {
                chatLastId = data.messages[data.messages.length - 1].message_id;
            }
        } else {
            container.innerHTML = '<div style="text-align:center;padding:20px;color:#e74c3c;">Error loading messages</div>';
        }
    } catch (e) {
        container.innerHTML = '<div style="text-align:center;padding:20px;color:#e74c3c;">Network error</div>';
    }
}

function renderChatMessages(messages) {
    const container = document.getElementById('chatMessages');
    if (!container) return;
    
    if (!messages || messages.length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:20px;color:#888;">No messages yet. Say hello!</div>';
        return;
    }
    
    container.innerHTML = '';
    messages.forEach(msg => {
        const isOwn = msg.sender_id == SESSION.userId;
        const div = document.createElement('div');
        div.className = 'chat-msg ' + (isOwn ? 'sent' : 'received');
        div.textContent = msg.message_text;
        container.appendChild(div);
    });
    container.scrollTop = container.scrollHeight;
}

async function sendChat() {
    const input = document.getElementById('chatInput');
    if (!input) return;
    const message = input.value.trim();
    if (!message || !chatUserId) return;
    
    input.value = '';
    
    try {
        const formData = new FormData();
        formData.append('receiver_id', chatUserId);
        formData.append('message', message);
        if (chatProductId) formData.append('product_id', chatProductId);
        
        const res = await fetch('index.php?action=send-message', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            const container = document.getElementById('chatMessages');
            if (container) {
                const div = document.createElement('div');
                div.className = 'chat-msg sent';
                div.textContent = message;
                container.appendChild(div);
                container.scrollTop = container.scrollHeight;
                chatLastId++;
            }
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
        
        if (data.success && data.messages && data.messages.length > 0) {
            const container = document.getElementById('chatMessages');
            if (!container) return;
            
            const placeholder = container.querySelector('div[style*="text-align:center"]');
            if (placeholder) {
                container.innerHTML = '';
            }
            
            data.messages.forEach(msg => {
                const isOwn = msg.sender_id == SESSION.userId;
                const div = document.createElement('div');
                div.className = 'chat-msg ' + (isOwn ? 'sent' : 'received');
                div.textContent = msg.message_text;
                container.appendChild(div);
            });
            
            chatLastId = data.messages[data.messages.length - 1].message_id;
            container.scrollTop = container.scrollHeight;
        }
    } catch (e) {
        // Silent fail
    }
}

// =============================================
// AUTH MODAL
// =============================================
function showAuthModal(mode = 'login') {
    let modal = document.getElementById('authModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'authModal';
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="auth-modal-card" style="background:white;border-radius:10px;padding:32px 28px;max-width:420px;width:100%;position:relative;">
                <button class="btn-close-modal" onclick="closeAuthModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:22px;color:#888;cursor:pointer;">✕</button>
                <div class="auth-tabs" style="display:flex;border-bottom:2px solid #eee;margin-bottom:24px;">
                    <button class="auth-tab active" id="authTabLogin" onclick="switchAuthTab('login')" style="flex:1;padding:10px;background:none;border:none;font-weight:600;color:#5bbcb8;border-bottom:3px solid #5bbcb8;cursor:pointer;">Login</button>
                    <button class="auth-tab" id="authTabReg" onclick="switchAuthTab('register')" style="flex:1;padding:10px;background:none;border:none;font-weight:600;color:#999;cursor:pointer;">Register</button>
                </div>
                <div id="authLoginForm">
                    <div class="auth-form-group" style="margin-bottom:14px;">
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Email</label>
                        <input type="email" id="loginEmail" placeholder="you@example.com" style="width:100%;padding:12px 14px;border:1.5px solid #ddd;border-radius:6px;font-size:14px;" />
                    </div>
                    <div class="auth-form-group" style="margin-bottom:20px;">
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Password</label>
                        <input type="password" id="loginPass" placeholder="••••••" style="width:100%;padding:12px 14px;border:1.5px solid #ddd;border-radius:6px;font-size:14px;" />
                    </div>
                    <button class="btn-post" onclick="doLogin()" style="width:100%;padding:15px;background:#5bbcb8;color:white;border:none;border-radius:6px;font-weight:700;cursor:pointer;">Login</button>
                </div>
                <div id="authRegForm" style="display:none;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="auth-form-group"><label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Name</label><input type="text" id="regName" placeholder="Full Name" style="width:100%;padding:12px 14px;border:1.5px solid #ddd;border-radius:6px;" /></div>
                        <div class="auth-form-group"><label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Phone</label><input type="tel" id="regPhone" placeholder="082..." style="width:100%;padding:12px 14px;border:1.5px solid #ddd;border-radius:6px;" /></div>
                    </div>
                    <div class="auth-form-group"><label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Email</label><input type="email" id="regEmail" placeholder="you@example.com" style="width:100%;padding:12px 14px;border:1.5px solid #ddd;border-radius:6px;" /></div>
                    <div class="auth-form-group"><label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Password</label><input type="password" id="regPass" placeholder="Min 6 characters" style="width:100%;padding:12px 14px;border:1.5px solid #ddd;border-radius:6px;" /></div>
                    <div class="auth-form-group"><label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Location</label><input type="text" id="regLocation" placeholder="e.g. Midrand" style="width:100%;padding:12px 14px;border:1.5px solid #ddd;border-radius:6px;" /></div>
                    <button class="btn-post" onclick="doRegister()" style="width:100%;padding:15px;background:#5bbcb8;color:white;border:none;border-radius:6px;font-weight:700;cursor:pointer;">Register</button>
                </div>
            </div>
        `;
        modal.addEventListener('click', e => { if (e.target === modal) closeAuthModal(); });
        document.body.appendChild(modal);
    }
    setTimeout(() => modal.classList.add('active'), 10);
    switchAuthTab(mode);
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) modal.classList.remove('active');
}

function switchAuthTab(tab) {
    const loginForm = document.getElementById('authLoginForm');
    const regForm = document.getElementById('authRegForm');
    const loginTab = document.getElementById('authTabLogin');
    const regTab = document.getElementById('authTabReg');
    
    if (tab === 'login') {
        if (loginForm) loginForm.style.display = 'block';
        if (regForm) regForm.style.display = 'none';
        if (loginTab) { loginTab.style.color = '#5bbcb8'; loginTab.style.borderBottomColor = '#5bbcb8'; }
        if (regTab) { regTab.style.color = '#999'; regTab.style.borderBottomColor = 'transparent'; }
    } else {
        if (loginForm) loginForm.style.display = 'none';
        if (regForm) regForm.style.display = 'block';
        if (regTab) { regTab.style.color = '#5bbcb8'; regTab.style.borderBottomColor = '#5bbcb8'; }
        if (loginTab) { loginTab.style.color = '#999'; loginTab.style.borderBottomColor = 'transparent'; }
    }
}

async function doLogin() {
    const email = document.getElementById('loginEmail')?.value.trim();
    const pass = document.getElementById('loginPass')?.value;
    if (!email || !pass) {
        showToast('Email and password required.');
        return;
    }
    
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', pass);
    
    try {
        const res = await fetch('index.php?action=do-login', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            closeAuthModal();
            showToast('Welcome back!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.error || 'Login failed.');
        }
    } catch (e) {
        showToast('Network error');
    }
}

async function doRegister() {
    const name = document.getElementById('regName')?.value.trim();
    const phone = document.getElementById('regPhone')?.value.trim();
    const email = document.getElementById('regEmail')?.value.trim();
    const password = document.getElementById('regPass')?.value;
    const location = document.getElementById('regLocation')?.value.trim();
    
    if (!name || !email || !password) {
        showToast('Name, email and password required.');
        return;
    }
    
    if (password.length < 6) {
        showToast('Password must be at least 6 characters.');
        return;
    }
    
    const formData = new FormData();
    formData.append('full_name', name);
    formData.append('phone', phone || '');
    formData.append('email', email);
    formData.append('password', password);
    formData.append('location', location || '');
    
    try {
        const res = await fetch('index.php?action=do-register', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            closeAuthModal();
            showToast('Registration successful! Please login.');
            switchAuthTab('login');
            const emailField = document.getElementById('loginEmail');
            if (emailField) emailField.value = email;
        } else {
            showToast(data.error || 'Registration failed.');
        }
    } catch (e) {
        showToast('Network error');
    }
}

function doLogout() {
    window.location = 'index.php?action=logout';
}

// =============================================
// ADMIN ACCESS
// =============================================
function openAdmin() {
    window.location = 'admin.php';
}

// =============================================
// FILTER PRODUCTS
// =============================================
function filterProducts() {
    const search = document.getElementById('searchInput')?.value || '';
    goTo('search', { q: search });
}

function filterCategory(categoryId) {
    goTo('category', { id: categoryId });
}

// =============================================
// SELL MODAL
// =============================================
function openSellModal() {
    if (!SESSION || !SESSION.isLoggedIn) {
        showToast('Please login first');
        window.location = 'index.php?action=login';
        return;
    }
    const modal = document.getElementById('sellModal');
    if (modal) modal.classList.add('active');
}

function closeSellModal() {
    const modal = document.getElementById('sellModal');
    if (modal) modal.classList.remove('active');
}

function closeSellOnBg(e) {
    if (e.target === document.getElementById('sellModal')) closeSellModal();
}

// =============================================
// LANGUAGE SELECTION
// =============================================
function setLanguage(lang) {
    fetch('index.php?action=set-language', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ language: lang })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Language selected!');
            setTimeout(() => {
                window.location = 'index.php?action=marketplace';
            }, 1200);
        }
    })
    .catch(() => showToast('Failed to set language'));
}

// =============================================
// INITIALIZATION
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    if (SESSION && SESSION.isLoggedIn) {
        refreshCartBadge();
    }
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                filterProducts();
            }
        });
    }
});

// =============================================
// EXPOSE FUNCTIONS TO GLOBAL SCOPE
// =============================================
window.buyNow = buyNow;
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.toggleWishlist = toggleWishlist;
window.refreshCartBadge = refreshCartBadge;
window.openChat = openChat;
window.closeChat = closeChat;
window.sendChat = sendChat;
window.showToast = showToast;
window.goTo = goTo;
window.goToAdmin = goToAdmin;
window.doLogin = doLogin;
window.doRegister = doRegister;
window.doLogout = doLogout;
window.showAuthModal = showAuthModal;
window.closeAuthModal = closeAuthModal;
window.switchAuthTab = switchAuthTab;
window.openAdmin = openAdmin;
window.filterProducts = filterProducts;
window.filterCategory = filterCategory;
window.openSellModal = openSellModal;
window.closeSellModal = closeSellModal;
window.closeSellOnBg = closeSellOnBg;
window.setLanguage = setLanguage;
window.toggleUserMenu = toggleUserMenu;