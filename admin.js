// =====================================================
// SpazaSa — admin.js
// =====================================================

const ADMIN_API = 'api';

// =====================================================
// ADMIN LOGIN
// =====================================================
async function adminLogin() {
  const email = document.getElementById('adminEmail').value.trim();
  const pass  = document.getElementById('adminPass').value;
  const errEl = document.getElementById('loginError');
  errEl.textContent = '';

  if (!email || !pass) {
    errEl.textContent = 'Please enter your email and password.';
    return;
  }

  try {
    const res = await fetch(`${ADMIN_API}/login.php`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password: pass })
    });
    const data = await res.json();

    if (data.success) {
      const user = data.data;
      // Only allow admin roles
      const adminRoles = ['super admin', 'superadmin', 'manager', 'support', 'admin'];
      const role = (user.role || '').toLowerCase();
      if (!adminRoles.includes(role)) {
        errEl.textContent = 'Access denied. Admin accounts only.';
        return;
      }
      document.getElementById('sidebarAdminName').textContent = user.name || email;
      document.getElementById('admin-login').classList.remove('active');
      document.getElementById('admin-app').classList.add('active');
      loadDashboardStats();
      adminToast(`Welcome back, ${(user.name || 'Admin').split(' ')[0]}! 👋`);
    } else {
      errEl.textContent = data.error || 'Invalid credentials.';
    }
  } catch (e) {
    // Fallback: demo login if API not available
    const demoAccounts = [
      { email: 'super@spaza.co.za',   pass: 'admin123', name: 'Mpilwenhle Mtshali', role: 'Super Admin' },
      { email: 'manager@spaza.co.za', pass: 'admin123', name: 'Khensani S',          role: 'Manager' },
      { email: 'support@spaza.co.za', pass: 'admin123', name: 'Elethu S',             role: 'Support' },
    ];
    const match = demoAccounts.find(a => a.email === email && a.pass === pass);
    if (match) {
      document.getElementById('sidebarAdminName').textContent = match.name;
      document.getElementById('admin-login').classList.remove('active');
      document.getElementById('admin-app').classList.add('active');
      loadDashboardStats();
      adminToast(`Welcome back, ${match.name.split(' ')[0]}! 👋`);
    } else {
      errEl.textContent = 'Invalid credentials.';
    }
  }
}

// Allow Enter key on login form
document.addEventListener('DOMContentLoaded', () => {
  ['adminEmail','adminPass'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('keydown', e => { if (e.key === 'Enter') adminLogin(); });
  });
  // On tablet/mobile sidebar starts closed
  if (window.innerWidth <= 900) {
    document.getElementById('sidebar')?.classList.remove('open');
  }
});

// =====================================================
// SIDEBAR TOGGLE
// =====================================================
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const main = document.querySelector('.admin-main');
  if (window.innerWidth <= 900) {
    sb.classList.toggle('open');
  } else {
    sb.classList.toggle('hidden');
    main.classList.toggle('full');
  }
}

// =====================================================
// PAGE NAVIGATION
// =====================================================
const PAGE_TITLES = {
  dashboard: 'Dashboard',
  users:     'User Management',
  listings:  'Listing Management',
  orders:    'Orders',
  roles:     'Roles',
  reports:   'Reports',
  audit:     'Audit Log',
};

function showAdminPage(name, btnEl) {
  // Hide all pages
  document.querySelectorAll('.apage').forEach(p => p.classList.remove('active'));
  // Show target page
  const page = document.getElementById(`apage-${name}`);
  if (page) page.classList.add('active');

  // Update topbar title
  document.getElementById('topbarTitle').textContent = PAGE_TITLES[name] || name;

  // Update sidebar active state
  document.querySelectorAll('.snav-btn, .snav-btn-lower').forEach(b => b.classList.remove('active'));
  if (btnEl) btnEl.classList.add('active');

  // Close sidebar on mobile after nav
  if (window.innerWidth <= 900) {
    document.getElementById('sidebar')?.classList.remove('open');
  }

  // Load data for the page
  if (name === 'dashboard') loadDashboardStats();
  if (name === 'users')     loadUsers();
  if (name === 'listings')  loadListings();
  if (name === 'orders')    loadOrders();
}

// =====================================================
// DASHBOARD — load stats from API or use demo data
// =====================================================
async function loadDashboardStats() {
  try {
    const [usersRes, productsRes, ordersRes] = await Promise.all([
      fetch(`${ADMIN_API}/users.php?count=1`,    { credentials: 'include' }).then(r => r.json()).catch(() => null),
      fetch(`${ADMIN_API}/products.php?count=1`, { credentials: 'include' }).then(r => r.json()).catch(() => null),
      fetch(`${ADMIN_API}/orders.php?count=1`,   { credentials: 'include' }).then(r => r.json()).catch(() => null),
    ]);
    if (usersRes?.success)    setTxt('scTotalUsers',    usersRes.data?.total    || '79');
    if (productsRes?.success) setTxt('scActiveListings',productsRes.data?.total || '56');
    if (ordersRes?.success)   setTxt('scOrdersToday',   ordersRes.data?.today   || '9');
  } catch (e) {
    // Demo data already in HTML — no action needed
  }
}

// =====================================================
// USERS — load from API or show demo table
// =====================================================
async function loadUsers() {
  try {
    const res = await fetch(`${ADMIN_API}/users.php`, { credentials: 'include' });
    const data = await res.json();
    if (!data.success || !data.data) return;
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) return;
    tbody.innerHTML = data.data.map(u => `
      <tr>
        <td>${esc(u.name)}</td>
        <td>${esc(u.email)}</td>
        <td>${esc(u.location||'')}</td>
        <td>${esc(u.role||'User')}</td>
        <td><span class="status-badge ${u.status==='banned'?'banned':'active'}">●</span></td>
        <td><button class="tbl-action-btn" onclick="manageUser(this,'${esc(u.name)}')">${u.status==='banned'?'Unban':'Ban'}</button></td>
      </tr>
    `).join('');
  } catch (e) { /* keep demo HTML */ }
}

// =====================================================
// LISTINGS — load from API or show demo table
// =====================================================
async function loadListings() {
  try {
    const res = await fetch(`${ADMIN_API}/products.php?limit=50`, { credentials: 'include' });
    const data = await res.json();
    if (!data.success || !data.data?.products) return;
    const tbody = document.querySelector('#listingsTable tbody');
    if (!tbody) return;
    tbody.innerHTML = data.data.products.map(p => `
      <tr>
        <td>${esc(p.title)}</td>
        <td>${esc(p.seller_name||'')}</td>
        <td>${p.price ? 'R'+parseFloat(p.price).toLocaleString() : ''}</td>
        <td>${esc((p.category||'').toUpperCase())}</td>
        <td><span class="status-badge ${p.status==='pending'?'banned':'active'}">●</span></td>
        <td>
          ${p.status==='pending'
            ? `<button class="tbl-approve-btn" onclick="approveListing(this,${p.id})">approve</button>`
            : `<button class="tbl-remove-btn"  onclick="removeListing(this,${p.id})">X remove</button>`
          }
        </td>
      </tr>
    `).join('');
  } catch (e) { /* keep demo HTML */ }
}

// =====================================================
// ORDERS — load from API or show demo table
// =====================================================
async function loadOrders() {
  try {
    const res = await fetch(`${ADMIN_API}/orders.php`, { credentials: 'include' });
    const data = await res.json();
    if (!data.success || !data.data) return;
    const tbody = document.querySelector('#ordersTable tbody');
    if (!tbody) return;
    tbody.innerHTML = data.data.map(o => `
      <tr>
        <td>${esc(o.product_title||'')}</td>
        <td>${esc(o.seller_name||'')}</td>
        <td>${o.price ? 'R'+parseFloat(o.price).toLocaleString() : ''}</td>
        <td>${esc(o.delivery_type||'')}</td>
        <td><span class="status-pill ${o.status==='delivered'?'delivered':'pending'}">${o.status||'pending'}</span></td>
        <td>
          ${o.status==='delivered'
            ? `<button class="tbl-remove-btn" onclick="removeOrder(this,${o.id})">X remove</button>`
            : `<button class="tbl-approve-btn" onclick="approveOrder(this,${o.id})">approve</button>`
          }
        </td>
        <td>${esc(o.buyer_name||'')}</td>
      </tr>
    `).join('');
  } catch (e) { /* keep demo HTML */ }
}

// =====================================================
// USER ACTIONS
// =====================================================
function manageUser(btn, name) {
  const row = btn.closest('tr');
  const statusCell = row.querySelector('.status-badge');
  if (statusCell.classList.contains('active')) {
    statusCell.classList.replace('active', 'banned');
    btn.textContent = 'Unban';
    addActivity(`User banned: ${name}`);
    adminToast(`${name} has been banned.`);
  } else {
    statusCell.classList.replace('banned', 'active');
    btn.textContent = 'Ban';
    addActivity(`User unbanned: ${name}`);
    adminToast(`${name} has been unbanned.`);
  }
}

// =====================================================
// LISTING ACTIONS
// =====================================================
function removeListing(btn, id) {
  const row = btn.closest('tr');
  const title = row.cells[0].textContent;
  if (!confirm(`Remove listing "${title}"?`)) return;
  if (id) {
    fetch(`${ADMIN_API}/products.php?id=${id}`, { method: 'DELETE', credentials: 'include' }).catch(() => {});
  }
  row.style.transition = 'opacity 0.3s';
  row.style.opacity = '0';
  setTimeout(() => row.remove(), 300);
  addActivity(`Listing removed: ${title}`);
  adminToast(`"${title}" removed.`);
  updateStatCard('scActiveListings', -1);
}

function approveListing(btn, id) {
  const row = btn.closest('tr');
  const title = row.cells[0].textContent;
  if (id) {
    fetch(`${ADMIN_API}/products.php`, {
      method: 'PATCH',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, status: 'active' })
    }).catch(() => {});
  }
  const statusBadge = row.querySelector('.status-badge');
  if (statusBadge) statusBadge.className = 'status-badge active';
  btn.className = 'tbl-remove-btn';
  btn.textContent = 'X remove';
  btn.onclick = () => removeListing(btn, id);
  addActivity(`Listing approved: ${title}`);
  adminToast(`"${title}" approved!`);
}

// =====================================================
// ORDER ACTIONS
// =====================================================
function removeOrder(btn, id) {
  const row = btn.closest('tr');
  const product = row.cells[0].textContent;
  row.style.transition = 'opacity 0.3s';
  row.style.opacity = '0';
  setTimeout(() => row.remove(), 300);
  addActivity(`Order removed: ${product}`);
  adminToast(`Order for "${product}" removed.`);
}

function approveOrder(btn, id) {
  const row = btn.closest('tr');
  const product = row.cells[0].textContent;
  const pill = row.querySelector('.status-pill');
  if (pill) { pill.className = 'status-pill delivered'; pill.textContent = 'delivered'; }
  btn.className = 'tbl-remove-btn';
  btn.textContent = 'X remove';
  btn.onclick = () => removeOrder(btn, id);
  addActivity(`Order delivered: ${product}`);
  adminToast(`Order for "${product}" marked as delivered!`);
}

// =====================================================
// ROLES / RBAC
// =====================================================
function assignRole() {
  const user = document.getElementById('rbacUserSelect').value;
  const role = document.getElementById('rbacRoleSelect').value;
  if (!user) { adminToast('Please select a user.'); return; }

  // Map role → list element IDs
  const roleMap = {
    'super admin': 'superAdminList',
    'manager':     'managerList',
    'support':     'supportList',
  };

  // Remove user from all lists first
  document.querySelectorAll('.rbac-user-list li').forEach(li => {
    if (li.textContent === user) li.remove();
  });

  // Add to correct list
  const listId = roleMap[role] || 'managerList';
  const list = document.getElementById(listId);
  if (list) {
    const li = document.createElement('li');
    li.textContent = user;
    list.appendChild(li);
  }

  addActivity(`Role assigned: ${user} → ${role}`);
  adminToast(`${user} assigned as ${role}.`);
  document.getElementById('rbacUserSelect').value = '';
}

// =====================================================
// TABLE SEARCH FILTER
// =====================================================
function filterTable(tableId, query) {
  const table = document.getElementById(tableId);
  if (!table) return;
  const q = query.toLowerCase();
  table.querySelectorAll('tbody tr').forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(q) ? '' : 'none';
  });
}

// =====================================================
// TOPBAR SEARCH — filters across current active page
// =====================================================
document.getElementById('topbarSearch')?.addEventListener('input', function () {
  const activeTable = document.querySelector('.apage.active .admin-table');
  if (activeTable) filterTable(activeTable.id, this.value);
});

// =====================================================
// ACTIVITY LOG
// =====================================================
function addActivity(text) {
  const list = document.getElementById('activityList');
  if (!list) return;
  const li = document.createElement('li');
  li.innerHTML = `<span class="act-dot"></span> ${text}`;
  list.insertBefore(li, list.firstChild);
  // Keep max 10 items
  while (list.children.length > 10) list.removeChild(list.lastChild);
}

// =====================================================
// STAT CARD HELPER
// =====================================================
function updateStatCard(id, delta) {
  const el = document.getElementById(id);
  if (!el) return;
  const current = parseInt(el.textContent.replace(/[^0-9]/g, '')) || 0;
  el.textContent = Math.max(0, current + delta);
}

// =====================================================
// UTILITIES
// =====================================================
function setTxt(id, val) {
  const el = document.getElementById(id);
  if (el && val !== undefined) el.textContent = val;
}

function esc(str) {
  return (str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function adminToast(msg) {
  const t = document.getElementById('adminToast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}
