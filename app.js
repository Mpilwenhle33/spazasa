// =====================================================
// SpazaSa — app.js  (PHP/MySQL backed)
// =====================================================

const API = 'api';          // relative path to api folder

let currentLang   = 'english';
let currentFilter = 'all';
let currentUser   = null;   // populated after login / session check
let currentDetailProductId = null;
let trackingInterval = null;
let trackingProgress = 30;

// =====================================================
// TRANSLATIONS  (client-side for instant UI)
// =====================================================
const T = {
  english:   { home:'Home',language:'Language',marketplace:'Marketplace',cart:'Cart',used:'USED',new:'NEW',women:'WOMEN',men:'MEN',kids:'KIDS',manage:'MANAGE',search:'Search...',sell:'SELL',view:'VIEW',yourCart:'Your Cart',continueShopping:'Continue Shopping',emptyCart:'Your cart is empty.',total:'Total',checkout:'Checkout',remove:'Remove',description:'Description',delivery:'Delivery',delivNote:'Choose your preferred delivery option:',freeDelivery:'Free Delivery',standard:'Standard',express:'Express',tracking:'Live Delivery Tracking',ts1:'Order Placed',ts2:'Seller Confirmed',ts3:'Driver En Route',ts4:'Delivered',msgSeller:'CHAT WITH SELLER',addToCart:'BUY',postTitle:'Post Your Item',drop:'Drop Your Image Here',title:'TITLE',price:'PRICE',category:'CATEGORY',condition:'Condition',newCond:'NEW',usedCond:'USED',descLabel:'DISCRIPTION',location:'Location (e.g. Midrand)',postFree:'PUBLISH',chat:'Chat',send:'Send',login:'Login',register:'Register',logout:'Logout',email:'Email',password:'Password',name:'Full Name',phone:'Phone',welcome:'Welcome back',loginRequired:'Please log in to continue.' },
  isizulu:   { home:'Ikhaya',language:'Ulimi',marketplace:'Imakethe',cart:'Ingolobane',used:'OSETSHENZISIWE',new:'ENTSHA',women:'ABESIFAZANE',men:'ABESILISA',kids:'IZINGANE',manage:'PHATHA',search:'Sesha...',sell:'THENGISA',view:'BUKA',yourCart:'Ingolobane Yakho',continueShopping:'Qhubeka Uthenga',emptyCart:'Ingolobane yakho ingenalutho.',total:'Isamba',checkout:'Khokha',remove:'Susa',description:'Incazelo',delivery:'Ukuhambiswa',delivNote:'Khetha indlela yokuhambiswa:',freeDelivery:'Mahala',standard:'Ejwayelekile',express:'Ekhawulezayo',tracking:'Landelela Ukuhambiswa',ts1:'I-oda Libekiwe',ts2:'Umthengisi Waqinisekisa',ts3:'Umshayeli Uyeza',ts4:'Kufikisiwe',msgSeller:'XHUMANA NOMTHENGISI',addToCart:'THENGA',postTitle:'Faka Into Yakho',drop:'Ekeleza Isithombe Sakho Lapha',title:'ISIHLOKO',price:'INTENGO',category:'UMKHAKHA',condition:'Isimo',newCond:'ENTSHA',usedCond:'OSETSHENZISIWE',descLabel:'INCAZELO',location:'Indawo',postFree:'SHICILELA',chat:'Xoxa',send:'Thumela',login:'Ngena',register:'Bhalisa',logout:'Phuma',email:'I-imeyili',password:'Iphasiwedi',name:'Igama Eligcwele',phone:'Ucingo',welcome:'Sawubona',loginRequired:'Ngena kuqala ukuqhubeka.' },
  sesotho:   { home:'Lapeng',language:'Puo',marketplace:'Mmaraka',cart:'Karolo',used:'E SEBEDISITSWENG',new:'E NTJHA',women:'BASADI',men:'BANNA',kids:'BANA',manage:'LAOLA',search:'Batlana...',sell:'REKISA',view:'SHEBA',yourCart:'Karolo Ya Hao',continueShopping:'Tswela Pele Ho Reka',emptyCart:'Karolo ya hao ha e na letho.',total:'Kakaretso',checkout:'Lefa',remove:'Tlosa',description:'Tlhaloso',delivery:'Phahloho',delivNote:'Khetha mokgwa wa phahloho:',freeDelivery:'Mahala',standard:'E tloaelehileng',express:'E potlakileng',tracking:'Lekalekang Phahloho',ts1:'Oda E Beiwe',ts2:'Motshedi O Netefaditse',ts3:'Mmolai O Tsamaea',ts4:'E Fihlisitswe',msgSeller:'BOLELA LE MOTSHEDI',addToCart:'REKA',postTitle:'Kenya Ntho Ya Hao',drop:'Kgokela Setshwantsho Sakho',title:'SEHLOOHO',price:'THEKO',category:'MOKHAHLELO',condition:'Maemo',newCond:'E NTJHA',usedCond:'E SEBEDISITSWENG',descLabel:'TLHALOSO',location:'Sebaka',postFree:'PHATLALATSA',chat:'Bua',send:'Romela',login:'Kena',register:'Ngodisa',logout:'Tswa',email:'Imeile',password:'Phasewete',name:'Lebitso le feletseng',phone:'Mohala',welcome:'Dumela',loginRequired:'Kena pele ho tswela pele.' },
  afrikaans: { home:'Tuis',language:'Taal',marketplace:'Markplek',cart:'Mandjie',used:'GEBRUIK',new:'NUUT',women:'VROUE',men:'MANS',kids:'KINDERS',manage:'BESTUUR',search:'Soek...',sell:'VERKOOP',view:'BEKYK',yourCart:'Jou Mandjie',continueShopping:'Gaan Voort',emptyCart:'Jou mandjie is leeg.',total:'Totaal',checkout:'Betaal',remove:'Verwyder',description:'Beskrywing',delivery:'Aflewering',delivNote:'Kies jou afleveringsopsie:',freeDelivery:'Gratis',standard:'Standaard',express:'Spoed',tracking:'Lewering Opsporing',ts1:'Bestelling Geplaas',ts2:'Verkoper Bevestig',ts3:'Bestuurder Onderweg',ts4:'Afgelewer',msgSeller:'GESELS MET VERKOPER',addToCart:'KOOP',postTitle:'Plaas Jou Item',drop:'Laat Val Jou Foto Hier',title:'TITEL',price:'PRYS',category:'KATEGORIE',condition:'Toestand',newCond:'NUUT',usedCond:'GEBRUIK',descLabel:'BESKRYWING',location:'Ligging',postFree:'PUBLISEER',chat:'Gesels',send:'Stuur',login:'Teken In',register:'Registreer',logout:'Teken Uit',email:'E-pos',password:'Wagwoord',name:'Volle Naam',phone:'Telefoon',welcome:'Welkom terug',loginRequired:'Meld asseblief aan om voort te gaan.' }
};

function tr(key){ return (T[currentLang]||T.english)[key]||key; }

// =====================================================
// API HELPERS
// =====================================================
async function apiFetch(endpoint, options = {}) {
  try {
    const res = await fetch(`${API}/${endpoint}`, {
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', ...(options.headers||{}) },
      ...options
    });
    const data = await res.json();
    return { ok: res.ok, status: res.status, ...data };
  } catch (e) {
    console.error('API error:', e);
    return { ok: false, error: 'Network error.' };
  }
}

async function apiGet(endpoint) {
  return apiFetch(endpoint, { method: 'GET', headers: {} });
}

async function apiPost(endpoint, body) {
  return apiFetch(endpoint, { method: 'POST', body: JSON.stringify(body) });
}

async function apiPostForm(endpoint, formData) {
  try {
    const res = await fetch(`${API}/${endpoint}`, {
      method: 'POST',
      credentials: 'include',
      body: formData
    });
    return await res.json();
  } catch(e) { return { success: false, error: 'Network error.' }; }
}

async function apiDelete(endpoint) {
  return apiFetch(endpoint, { method: 'DELETE', headers: {} });
}

// =====================================================
// PAGE NAVIGATION
// =====================================================
function showPage(name) {
  document.querySelectorAll('.page-section').forEach(p => p.classList.remove('active'));
  document.getElementById('page-' + name)?.classList.add('active');
  if (name === 'marketplace') loadProducts();
  if (name === 'cart')        loadCart();
  window.scrollTo(0,0);
}

// Menu toggle (hamburger) → language page
function toggleMenu() {
  showPage('language');
}

// Open admin portal in new tab / same window
function openAdmin() {
  window.location.href = 'admin.html';
}

// =====================================================
// TRANSLATIONS APPLY
// =====================================================
function applyTranslations() {
  const Tr = T[currentLang]||T.english;
  document.querySelectorAll('[data-key]').forEach(el => {
    const k = el.getAttribute('data-key');
    if (Tr[k]) el.textContent = Tr[k];
  });
  const catKeys = ['used','new','women','men','kids','manage'];
  document.querySelectorAll('#categoryTabs .cat-tab').forEach((btn,i)=>{ if(catKeys[i]) btn.textContent = Tr[catKeys[i]]||btn.textContent; });
  const si = document.getElementById('searchInput'); if(si) si.placeholder = Tr.search;
  setTxt('lblDrop', Tr.drop);
  setTxt('btnPostFree', Tr.postFree);
  setTxt('btnMsgSeller', Tr.msgSeller);
  setTxt('btnAddCart', Tr.addToCart);
  setTxt('cartHeading', Tr.yourCart);
  const loc = document.getElementById('itemLocation'); if(loc) loc.placeholder = Tr.location;
  updateAuthUI();
}

function setTxt(id,val){ const el=document.getElementById(id); if(el&&val) el.textContent=val; }

// =====================================================
// AUTH UI
// =====================================================
function updateAuthUI() {
  const Tr = T[currentLang]||T.english;
  const loginBtn    = document.getElementById('btnLogin');
  const logoutBtn   = document.getElementById('btnLogout');
  const userDisplay = document.getElementById('userDisplay');
  if (currentUser) {
    if(loginBtn)    loginBtn.style.display  = 'none';
    if(logoutBtn)   logoutBtn.style.display = 'inline-flex';
    if(userDisplay) { userDisplay.style.display='flex'; userDisplay.textContent = currentUser.name.split(' ')[0]; }
  } else {
    if(loginBtn)    loginBtn.style.display  = 'inline-flex';
    if(logoutBtn)   logoutBtn.style.display = 'none';
    if(userDisplay) userDisplay.style.display = 'none';
  }
}

// =====================================================
// SESSION CHECK ON LOAD
// =====================================================
async function checkSession() {
  const res = await apiGet('me.php');
  if (res.success && res.data) {
    currentUser = res.data;
    currentLang = res.data.language_pref || 'english';
    applyTranslations();
    updateAuthUI();
  }
}

// =====================================================
// LANGUAGE
// =====================================================
function setLang(lang) {
  currentLang = lang;
  applyTranslations();
  if (currentUser) {
    apiFetch('me.php', { method:'PUT', body: JSON.stringify({ language_pref: lang }) });
  }
  const greetings = {
    isizulu:'Sawubona! Siyakwamukela e-SpazaSa! 🎉',
    sesotho:'Dumela! Re a o amohela ho SpazaSa! 🎉',
    english:'Welcome to SpazaSa! 🎉',
    afrikaans:'Welkom by SpazaSa! 🎉'
  };
  showToast(greetings[lang]||'Welcome!');
  setTimeout(()=>showPage('marketplace'),1200);
}

// =====================================================
// PRODUCTS — load from PHP API
// =====================================================
async function loadProducts() {
  const grid = document.getElementById('productsGrid');
  if (!grid) return;
  grid.innerHTML = `<div class="loading-spinner">Loading...</div>`;
  const q      = document.getElementById('searchInput')?.value.trim() || '';
  let   params = 'limit=40';
  if (currentFilter !== 'all' && currentFilter !== 'manage') {
    if (currentFilter === 'new' || currentFilter === 'used') params += `&cond=${currentFilter}`;
    else params += `&category=${currentFilter}`;
  }
  if (currentFilter === 'manage' && currentUser) params = `seller_id=${currentUser.id}&limit=40`;
  if (q) params += `&q=${encodeURIComponent(q)}`;
  const res = await apiGet(`products.php?${params}`);
  if (!res.success) { grid.innerHTML = `<div class="error-msg">${res.error||'Failed to load products.'}</div>`; return; }
  renderProducts(res.data?.products || []);
}

function renderProducts(list) {
  const grid = document.getElementById('productsGrid');
  if (!grid) return;
  if (!list.length) {
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:#ddd;font-size:15px;">No items found.</div>`;
    return;
  }
  grid.innerHTML = list.map(p => `
    <div class="product-card" onclick="openDetail(${p.id})">
      <img class="product-img" src="${p.cover||p.images?.[0]||'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400'}"
           alt="${escHtml(p.title)}" loading="lazy" />
      <div class="product-info">
        <div class="product-price">R${parseFloat(p.price).toLocaleString()}</div>
        <div class="product-name">${escHtml(p.title)}</div>
        <div class="product-location">${escHtml(p.location||'')}</div>
      </div>
      <button class="btn-view" onclick="event.stopPropagation();openDetail(${p.id})">${tr('view')}</button>
    </div>
  `).join('');
}

function filterCat(el, cat) {
  document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  currentFilter = cat;
  loadProducts();
}

function filterProducts() { loadProducts(); }

// =====================================================
// PRODUCT DETAIL
// =====================================================
async function openDetail(id) {
  currentDetailProductId = id;
  showPage('detail');
  setTxt('detailName','Loading...');
  setTxt('detailPrice','');
  setTxt('detailLocation','');
  document.getElementById('detailCond').innerHTML = '';

  const res = await apiGet(`products.php?id=${id}`);
  if (!res.success) { showToast(res.error||'Could not load product.'); return; }
  const p = res.data;

  setTxt('detailName', p.title);
  setTxt('detailPrice','R' + parseFloat(p.price).toLocaleString());
  setTxt('detailLocation', `${p.location||''}, ${(p.cond||'').toUpperCase()}`);
  document.getElementById('detailCond').innerHTML = `<strong>${(p.cond||'new').toLowerCase()}</strong>`;

  const mainImg = document.getElementById('mainDetailImg');
  if (mainImg && p.images?.length) mainImg.src = p.images[0];

  // Store seller info for chat
  document.getElementById('btnAddCart')?.setAttribute('data-id', id);
  const sellerName = p.seller_name || 'Seller';
  setTxt('chatSellerName', sellerName);

  // Pre-load any seller avatar if available
  if (p.seller_avatar) {
    const av = document.getElementById('chatSellerAvatar');
    if (av) av.src = p.seller_avatar;
  }
}

// =====================================================
// WISHLIST (frontend only — can be extended to API)
// =====================================================
function addToWishlist() {
  showToast('Added to wishlist ❤️');
}

// =====================================================
// CART — backed by API
// =====================================================
async function addDetailToCart() {
  if (!currentUser) { showAuthModal(); return; }
  const id = currentDetailProductId;
  if (!id) return;
  const res = await apiPost('cart.php', { product_id: id });
  if (res.success) {
    showToast(tr('addToCart') + ' ✓');
    await refreshCartBadge();
  } else {
    showToast(res.error || 'Could not add to cart.');
  }
}

async function refreshCartBadge() {
  if (!currentUser) return;
  const res = await apiGet('cart.php');
  if (res.success) {
    const count = res.data?.count || 0;
    const badge = document.getElementById('cartBadgeNav');
    if (badge) badge.textContent = count;
  }
}

async function loadCart() {
  const container = document.getElementById('cartItems');
  const summary   = document.getElementById('cartSummary');
  if (!container) return;
  if (!currentUser) {
    container.innerHTML = `<div class="cart-empty">${tr('loginRequired')}</div>`;
    if(summary) summary.innerHTML = '';
    return;
  }
  container.innerHTML = `<div class="loading-spinner">Loading...</div>`;
  const res = await apiGet('cart.php');
  if (!res.success) { container.innerHTML = `<div class="error-msg">${res.error||'Failed to load cart.'}</div>`; return; }
  const items = res.data?.items || [];
  const total = res.data?.total || 0;
  if (!items.length) {
    container.innerHTML = `<div class="cart-empty">${tr('emptyCart')}</div>`;
    if(summary) summary.innerHTML = '';
    return;
  }
  container.innerHTML = items.map(item => `
    <div class="cart-item">
      <img class="cart-item-img" src="${item.cover||'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400'}"
           alt="${escHtml(item.title)}" onclick="openDetail(${item.product_id})" />
      <div class="cart-item-info">
        <div class="cart-item-name">${escHtml(item.title)}</div>
        <div class="cart-item-meta">${escHtml(item.location||'')} · ${(item.cond||'').toUpperCase()}</div>
        <div class="cart-item-price">R${parseFloat(item.price).toLocaleString()}</div>
        <div class="cart-seller">Sold by: ${escHtml(item.seller_name||'')}</div>
      </div>
      <div class="cart-item-actions">
        <button class="btn-view-detail" onclick="openDetail(${item.product_id})">${tr('view')}</button>
        <button class="btn-remove" onclick="removeFromCart(${item.product_id})">✕ ${tr('remove')}</button>
      </div>
    </div>
  `).join('');
  if (summary) {
    summary.innerHTML = `
      <div class="cart-total-row">
        <span>${tr('total')} (${items.length} item${items.length!==1?'s':''})</span>
        <span class="cart-total-price">R${parseFloat(total).toLocaleString()}</span>
      </div>
      <button class="btn-checkout" onclick="checkout()">✓ ${tr('checkout')}</button>
    `;
  }
}

async function removeFromCart(productId) {
  const res = await apiDelete(`cart.php?product_id=${productId}`);
  if (res.success) { await loadCart(); }
  else showToast(res.error||'Could not remove item.');
}

async function checkout() {
  if (!currentUser) { showAuthModal(); return; }
  showToast('Placing order...');
  const res = await apiGet('cart.php');
  if (!res.success || !res.data?.items?.length) { showToast('Cart is empty.'); return; }
  for (const item of res.data.items) {
    await apiPost('orders.php', { product_id: item.product_id, delivery_type: 'free' });
  }
  await loadCart();
  showToast('Order placed successfully! 🎉');
}

// =====================================================
// MESSAGING / CHAT OVERLAY
// =====================================================
function openChat() {
  if (!currentUser) { showAuthModal(); return; }
  const overlay = document.getElementById('chatOverlay');
  if (overlay) {
    overlay.classList.add('open');
    // Load initial welcome message
    const msgs = document.getElementById('chatMessages');
    if (msgs && msgs.children.length === 0) {
      appendChatMsg('Hi, I am interested', false);
    }
  }
}

function closeChat() {
  document.getElementById('chatOverlay')?.classList.remove('open');
}

async function sendChat() {
  if (!currentUser) { showAuthModal(); return; }
  const input    = document.getElementById('chatInput');
  const msg      = input?.value.trim();
  if (!msg) return;
  appendChatMsg(msg, true);
  input.value = '';
  // API call (seller_id from detail page context)
  const res = await apiPost('messages.php', {
    receiver_id: 0,  // Will be filled by openDetail data-seller attr if extended
    product_id:  currentDetailProductId,
    message:     msg
  });
  if (!res.success) { showToast(res.error||'Message failed.'); }
  else {
    setTimeout(()=> appendChatMsg("Thanks! I'll get back to you shortly.", false), 900);
  }
}

function appendChatMsg(text, isOwn) {
  const msgs = document.getElementById('chatMessages');
  if (!msgs) return;
  const div = document.createElement('div');
  div.className = 'chat-msg' + (isOwn ? ' own' : '');
  div.textContent = text;
  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;
}

// =====================================================
// SELL / POST ITEM
// =====================================================
function openSellModal() {
  if (!currentUser) { showAuthModal(); return; }
  document.getElementById('sellModal')?.classList.add('active');
}

function closeSellModal() { document.getElementById('sellModal')?.classList.remove('active'); }
function closeSellOnBg(e) { if(e.target===document.getElementById('sellModal')) closeSellModal(); }

function previewPhotos(e) {
  const thumbs = document.getElementById('photoThumbs');
  const dropZone = document.querySelector('.sell-drop-zone');
  if (!thumbs) return;
  thumbs.innerHTML = '';
  const files = Array.from(e.target.files);
  if (files.length && dropZone) {
    // Show first image as background of drop zone
    const url = URL.createObjectURL(files[0]);
    dropZone.style.backgroundImage = `url(${url})`;
    dropZone.style.backgroundSize = 'cover';
    dropZone.style.backgroundPosition = 'center';
    document.querySelector('.drop-placeholder').style.display = 'none';
  }
  files.forEach(file => {
    const url2 = URL.createObjectURL(file);
    const img = document.createElement('img');
    img.src = url2; img.className = 'photo-thumb';
    thumbs.appendChild(img);
  });
}

async function postItem() {
  if (!currentUser) { showAuthModal(); return; }
  const title    = document.getElementById('itemTitle')?.value.trim();
  const price    = document.getElementById('itemPrice')?.value;
  const category = document.getElementById('itemCategory')?.value.trim() || 'other';
  const location = document.getElementById('itemLocation')?.value.trim();
  const cond     = document.querySelector('input[name="condition"]:checked')?.value || 'new';
  const desc     = document.getElementById('itemDesc')?.value.trim();
  const files    = document.getElementById('photoUpload')?.files;
  if (!title || !price) { showToast('Please add a title and price!'); return; }
  const formData = new FormData();
  formData.append('title',       title);
  formData.append('price',       price);
  formData.append('category',    category.toLowerCase());
  formData.append('location',    location||'');
  formData.append('cond',        cond);
  formData.append('description', desc||'');
  if (files) Array.from(files).forEach(f => formData.append('images[]', f));
  setTxt('btnPostFree','Posting...');
  const res = await apiPostForm('products.php', formData);
  setTxt('btnPostFree', tr('postFree'));
  if (res.success) {
    closeSellModal();
    showToast(`"${title}" posted successfully!`);
    ['itemTitle','itemPrice','itemDesc'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    const ic = document.getElementById('itemCategory'); if(ic) ic.value = '';
    const pt = document.getElementById('photoThumbs'); if(pt) pt.innerHTML = '';
    const dz = document.querySelector('.sell-drop-zone'); 
    if(dz) { dz.style.backgroundImage=''; }
    const dp = document.querySelector('.drop-placeholder'); if(dp) dp.style.display='';
    if (document.getElementById('page-marketplace').classList.contains('active')) loadProducts();
  } else {
    showToast(res.error||'Failed to post item.');
  }
}

// =====================================================
// AUTH MODAL
// =====================================================
function showAuthModal(mode='login') {
  let modal = document.getElementById('authModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'authModal';
    modal.className = 'modal-overlay';
    modal.innerHTML = `
      <div class="auth-modal-card" style="position:relative;">
        <button class="btn-close-modal" onclick="closeAuthModal()">✕</button>
        <div class="auth-tabs">
          <button class="auth-tab active" id="authTabLogin"  onclick="switchAuthTab('login')"   >${tr('login')}</button>
          <button class="auth-tab"        id="authTabReg"    onclick="switchAuthTab('register')">${tr('register')}</button>
        </div>
        <div id="authLoginForm">
          <div class="auth-form-group"><label>${tr('email')}</label><input type="email" id="loginEmail" placeholder="you@example.com"/></div>
          <div class="auth-form-group"><label>${tr('password')}</label><input type="password" id="loginPass" placeholder="••••••"/></div>
          <button class="btn-post" onclick="doLogin()">${tr('login')}</button>
        </div>
        <div id="authRegForm" style="display:none;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="auth-form-group"><label>${tr('name')}</label><input type="text" id="regName" placeholder="Full Name"/></div>
            <div class="auth-form-group"><label>${tr('phone')}</label><input type="tel" id="regPhone" placeholder="082..."/></div>
          </div>
          <div class="auth-form-group"><label>${tr('email')}</label><input type="email" id="regEmail" placeholder="you@example.com"/></div>
          <div class="auth-form-group"><label>${tr('password')}</label><input type="password" id="regPass" placeholder="Min 6 characters"/></div>
          <div class="auth-form-group"><label>${tr('location')}</label><input type="text" id="regLocation" placeholder="e.g. Midrand"/></div>
          <button class="btn-post" onclick="doRegister()">${tr('register')}</button>
        </div>
      </div>`;
    modal.addEventListener('click', e => { if(e.target===modal) closeAuthModal(); });
    document.body.appendChild(modal);
  }
  setTimeout(()=>modal.classList.add('active'), 10);
  switchAuthTab(mode);
}

function closeAuthModal() {
  document.getElementById('authModal')?.classList.remove('active');
}

function switchAuthTab(tab) {
  document.getElementById('authLoginForm').style.display = tab==='login'    ? 'block' : 'none';
  document.getElementById('authRegForm').style.display   = tab==='register' ? 'block' : 'none';
  document.getElementById('authTabLogin').classList.toggle('active', tab==='login');
  document.getElementById('authTabReg').classList.toggle('active',   tab==='register');
}

async function doLogin() {
  const email = document.getElementById('loginEmail')?.value.trim();
  const pass  = document.getElementById('loginPass')?.value;
  if (!email || !pass) { showToast('Email and password required.'); return; }
  const res = await apiPost('login.php', { email, password: pass });
  if (res.success) {
    currentUser = res.data;
    currentLang = res.data.language_pref || currentLang;
    applyTranslations();
    closeAuthModal();
    showToast(`${tr('welcome')}, ${res.data.name.split(' ')[0]}! 👋`);
    await refreshCartBadge();
  } else {
    showToast(res.error || 'Login failed.');
  }
}

async function doRegister() {
  const name     = document.getElementById('regName')?.value.trim();
  const phone    = document.getElementById('regPhone')?.value.trim();
  const email    = document.getElementById('regEmail')?.value.trim();
  const password = document.getElementById('regPass')?.value;
  const location = document.getElementById('regLocation')?.value.trim();
  if (!name||!email||!password) { showToast('Name, email and password required.'); return; }
  const res = await apiPost('register.php', { name, email, phone, password, location, language_pref: currentLang });
  if (res.success) {
    currentUser = res.data;
    applyTranslations();
    closeAuthModal();
    showToast(`Welcome to SpazaSa, ${name.split(' ')[0]}! 🎉`);
  } else {
    showToast(res.error || 'Registration failed.');
  }
}

async function doLogout() {
  await apiPost('logout.php', {});
  currentUser = null;
  updateAuthUI();
  showToast('Logged out. See you soon! 👋');
  showPage('hero');
}

// =====================================================
// UTILITIES
// =====================================================
function showToast(msg) {
  const t = document.getElementById('toast');
  if(!t) return;
  t.textContent = msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'), 2800);
}

function escHtml(str) {
  return (str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// =====================================================
// INIT
// =====================================================
document.addEventListener('DOMContentLoaded', async () => {
  applyTranslations();
  await checkSession();
  if (currentUser) await refreshCartBadge();
});
