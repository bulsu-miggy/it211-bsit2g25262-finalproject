// ═══════════════════ ORDERS MODULE ═══════════════════

import { showToast } from './toast.js';
import { openModal, closeModal } from './modals.js';
import { renderOrders, sBadge } from './render.js';
 
let orders = [];

async function loadOrders() {
    try {
        const resp = await fetch('../db/action/orders_api.php');
        const data = await resp.json();
        if (data.status === 'success') {
            orders = data.orders;
            renderOrders();
        }
    } catch (e) {
        showToast('Failed to load orders', 'error');
    }
}

export async function viewOrder(id) {
    const o = orders.find(x => x.id === parseInt(x.id) === id || x.order_number === id);
    document.getElementById('orderModalBody').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
            <div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);">
                <div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Order ID</div>
                <div style="font-weight:700;color:var(--mocha);font-size:1rem;">${o.id}</div>
            </div>
            <div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);">
                <div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Status</div>
                ${sBadge(o.status)}
            </div>
            <div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);">
                <div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Customer</div>
                <div>${o.customer}</div>
            </div>
            <div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);">
                <div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Total</div>
                <div style="font-weight:700;color:var(--caramel);font-size:1.1rem;">₱${o.total.toLocaleString()}</div>
            </div>
        </div>
        <div style="background:var(--primary-lt);border-radius:12px;padding:16px;">
            <div style="font-size:.75rem;font-weight:700;color:var(--muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.08em;">Update Order Status</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="btn btn-warning btn-sm" onclick="window.ordersModule.updateOrderStatus(${o.id},'Pending')">Pending</button>
                <button class="btn btn-success btn-sm" onclick="window.ordersModule.updateOrderStatus(${o.id},'Processing')">Processing</button>
                <button class="btn btn-info btn-sm" onclick="window.ordersModule.updateOrderStatus(${o.id},'Completed')">Completed</button>
                <button class="btn btn-danger btn-sm" onclick="window.ordersModule.updateOrderStatus(${o.id},'Cancelled')">Cancelled</button>


            </div>
        </div>`;
    openModal('orderModal');
}

export async function updateOrderStatus(id, status) {
    try {
        const resp = await fetch('../db/action/orders_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=update_status&order_id=${id}&status=${status}`
        });
        const data = await resp.json();
        if (data.status === 'success') {
            showToast(`Order #${id} → ${status}`, 'success');
            closeModal('orderModal');
            loadOrders();
        } else {
            showToast(data.message, 'error');
        }
    } catch (e) {
        showToast('Update failed', 'error');
    }
}

export { loadOrders };
export { orders }; // Export the orders array itself
