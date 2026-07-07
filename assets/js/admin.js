
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}


function adminToast(msg) {
    let t = document.getElementById('adminToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'adminToast';
        t.className = 'admin-toast';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._timeout);
    t._timeout = setTimeout(() => t.classList.remove('show'), 2800);
}

function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'banned' ? 'active' : 'banned';
    if (!confirm('Change user status to ' + newStatus + '?')) return;
    
    fetch('admin.php?action=update-user-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            adminToast('User status updated!');
            window.location.reload();
        } else {
            adminToast(data.error || 'Failed to update user');
        }
    })
    .catch(() => adminToast('Network error'));
}

function approveProduct(productId) {
    if (!confirm('Approve this product?')) return;
    
    window.location = 'admin.php?action=approve-product&id=' + productId;
}

function rejectProduct(productId) {
    if (!confirm('Reject this product?')) return;
    
    window.location = 'admin.php?action=reject-product&id=' + productId;
}

function deleteProduct(productId) {
    if (!confirm('Delete this product?')) return;
    
    window.location = 'admin.php?action=delete-product-admin&id=' + productId;
}
function assignRole() {
    const userSelect = document.getElementById('rbacUserSelect');
    const roleSelect = document.getElementById('rbacRoleSelect');
    const user = userSelect?.value;
    const role = roleSelect?.value;
    
    if (!user) {
        adminToast('Please select a user.');
        return;
    }
    
    fetch('admin.php?action=assign-role', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user, role: role })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            adminToast('Role assigned successfully!');
            window.location.reload();
        } else {
            adminToast(data.error || 'Failed to assign role');
        }
    })
    .catch(() => adminToast('Network error'));
}

function filterTable(tableId, query) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const q = query.toLowerCase();
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const menuBtn = document.querySelector('.topbar-menu-btn');
        if (window.innerWidth <= 900 && sidebar && menuBtn) {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
    const searchInput = document.querySelector('.topbar-search');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const table = document.querySelector('.admin-table');
                if (table) {
                    filterTable(table.id, this.value);
                }
            }
        });
    }
});

function adminToast(msg) {
    let t = document.getElementById('adminToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'adminToast';
        t.className = 'admin-toast';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._timeout);
    t._timeout = setTimeout(() => t.classList.remove('show'), 2800);
}
