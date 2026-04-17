// ═══════════════════ RENDER ═══════════════════

import { products, categories, customers, admins } from './data.js'; // Remove orders from data.js import
import { orders } from './orders.js'; // Import orders from orders.js
import { showToast } from './toast.js';
import { openModal } from './modals.js';

export function renderAll() {
    renderProducts();
    renderCategories();
    renderOrders();
    renderCustomers();
    renderAdmins();
}

export function sBadge(s) {
    const m = {'In Stock': 'success', 'Low Stock': 'warning', 'Out of Stock': 'danger', 'Paid': 'success', 'Pending': 'warning', 'Cancelled': 'danger', 'Shipped': 'info'};
    return `<span class="badge badge-${m[s] || 'muted'}">${s}</span>`;
}

export function renderProducts(data) {
    // Defensive check for products array
    const rows = data || products || [];
    document.getElementById('productsTbody').innerHTML = rows.map(p => `
        <tr>
            <td><div class="product-thumb"><i class="fas fa-fire"></i></div></td>
            <td>
                <div style="font-weight:600;color:var(--espresso);">${p.title}</div>
                <div style="font-size:.72rem;color:var(--muted);">ID #${p.id}</div>
            </td>
            <td>${p.scent.split(',').map(s => `<span class="scent-tag">${s.trim()}</span>`).join('')}</td>
            <td>${p.category}</td>
            <td><b style="color:var(--caramel);">₱${p.price.toLocaleString()}</b></td>
            <td>${p.stock} pcs ${p.stock <= 5 ? '<i class="fas fa-exclamation-circle" style="color:var(--danger);font-size:.8rem;"></i>' : ''}</td>
            <td>${sBadge(p.status)}</td>
            <td><div class="actions">
                <button class="btn btn-outline btn-sm" onclick="openProductModal(${p.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-danger btn-sm" onclick="confirmDel('product',${p.id},'${p.title}')"><i class="fas fa-trash"></i></button>
            </div></td>
        </tr>`).join('');
}

export function renderCategories() {
    document.getElementById('catTbody').innerHTML = categories.map(c => `
        <tr>
            <td style="color:var(--muted);">${c.id}</td>
            <td><b style="color:var(--espresso);">${c.name}</b></td>
            <td><code style="background:var(--cream);padding:2px 8px;border-radius:6px;font-size:.78rem;color:var(--mocha);">${c.slug}</code></td>
            <td>${c.products} items</td>
            <td style="color:var(--muted);font-size:.82rem;">${c.created}</td>
            <td><div class="actions">
                <button class="btn btn-outline btn-sm" onclick="openCatModal(${c.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-danger btn-sm" onclick="confirmDel('category',${c.id},'${c.name}')"><i class="fas fa-trash"></i></button>
            </div></td>
        </tr>`).join('');
}

export function renderOrders() {
    // Defensive check for orders array
    const orderList = orders || [];
    document.getElementById('ordersTbody').innerHTML = orderList.map(o => `
        <tr>
            <td><b style="color:var(--mocha);">${o.id}</b></td>
            <td>${o.customer}</td>
            <td>${o.items} item${o.items > 1 ? 's' : ''}</td>
            <td><b style="color:var(--caramel);">₱${o.total.toLocaleString()}</b></td>
            <td style="color:var(--muted);font-size:.82rem;">${o.date}</td>
            <td>${sBadge(o.status)}</td>
            <td><button class="btn btn-outline btn-sm" onclick="window.ordersModule.viewOrder('${o.id}')"><i class="fas fa-eye"></i> View</button></td>
        </tr>`).join('');
}

export function renderCustomers() {
    // Defensive check for customers array
    const customerList = customers || [];
    document.getElementById('customersTbody').innerHTML = customerList.map(c => `
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="avatar" style="width:32px;height:32px;font-size:.7rem;">${c.name.split(' ').map(w => w[0]).join('')}</div>
                    <b>${c.name}</b>
                </div>
            </td>
            <td style="color:var(--muted);font-size:.82rem;">${c.email}</td>
            <td style="font-size:.82rem;">${c.phone}</td>
            <td>${c.orders}</td>
            <td><b style="color:var(--caramel);">₱${c.spent.toLocaleString()}</b></td>
            <td style="color:var(--muted);font-size:.82rem;">${c.joined}</td>
            <td><button class="btn btn-outline btn-sm" onclick="viewCustomer(${c.id})"><i class="fas fa-eye"></i></button></td>
        </tr>`).join('');
}

export function renderAdmins() {
    // Defensive check for admins array
    const adminList = admins || [];
    document.getElementById('adminsTbody').innerHTML = adminList.map(a => `
        <tr>
            <td><b>${a.name}</b></td>
            <td style="color:var(--muted);font-size:.82rem;">${a.email}</td>
            <td><span class="badge badge-purple">${a.role}</span></td>
            <td style="color:var(--muted);font-size:.78rem;">${a.lastLogin}</td>
            <td><div class="actions">
                <button class="btn btn-outline btn-sm" onclick="openAdminModal(${a.id})"><i class="fas fa-edit"></i></button>
                ${a.id !== 1 ? `<button class="btn btn-danger btn-sm" onclick="confirmDel('admin',${a.id},'${a.name}')"><i class="fas fa-trash"></i></button>` : '<span style="font-size:.72rem;color:var(--muted);">Protected</span>'}
            </div></td>
        </tr>`).join('');
}