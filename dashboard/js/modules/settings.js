/**
 * modules/settings.js
 * Manages administrative settings, data deletion confirmations, and reporting.
 */

// ═══════════════════ SETTINGS ═══════════════════

import { products, categories, admins } from './data.js';
import { orders } from './orders.js';
import { showToast } from './toast.js';
import { closeModal, openModal } from './modals.js';
import { renderProducts, renderCategories, renderAdmins } from './render.js';

/**
 * Switches between different panels in the settings page.
 */
export function switchSettings(key, el) {
    document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
    document.getElementById('sp-' + key).classList.add('active');
    el.classList.add('active');
}

// ═══════════════════ DELETE ═══════════════════

/**
 * Triggers a confirmation modal before deleting an item.
 * @param {string} type - 'product', 'category', or 'admin'
 * @param {number} id - The ID of the item
 * @param {string} name - The display name for the confirmation message
 */
export function confirmDel(type, id, name) {
    document.getElementById('confirmTitle').textContent = `Delete ${type.charAt(0).toUpperCase() + type.slice(1)}?`;
    document.getElementById('confirmMsg').textContent = `Are you sure you want to delete "${name}"? This cannot be undone.`;
    document.getElementById('confirmBtn').onclick = async () => {
        if (type === 'product') {
            products.splice(products.findIndex(p => p.id === id), 1);
            renderProducts();
        } else if (type === 'category') {
            categories.splice(categories.findIndex(c => c.id === id), 1);
            renderCategories();
        } else if (type === 'admin') {
            try {
                const response = await fetch('../db/action/admins_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', id })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    showToast(`"${name}" has been removed.`, 'success');
                    if (window.fetchDashboardAdmins) await window.fetchDashboardAdmins();
                } else {
                    showToast(data.message || 'Failed to delete admin.', 'danger');
                }
            } catch (error) {
                console.error('Admin Delete Error:', error);
                showToast('Server error while deleting admin.', 'danger');
            }
        }
        closeModal('confirmModal');
    };
    openModal('confirmModal');
}

// ═══════════════════ EXPORT ═══════════════════

/**
 * Generates a downloadable CSV or opens a print dialog for sales reports.
 * @param {string} fmt - 'csv' or 'pdf'
 */
export function exportReport(fmt) {
    if (fmt === 'csv') {
        const h = ['Order ID', 'Customer', 'Items', 'Total (PHP)', 'Status'];
        // Defensive check: If orders is undefined, use an empty array to prevent .map() crash
        const orderList = orders || [];
        const rows = orderList.map(o => [o.id, o.customer, o.items, o.total, o.status]);
        const csv = [h, ...rows].map(r => r.join(',')).join('\n');
        const a = document.createElement('a');
        a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
        a.download = 'solis_orders.csv';
        a.click();
        showToast('Spreadsheet exported! 📊', 'success');
    } else {
        showToast('Opening PDF print dialog…', 'info');
        setTimeout(() => window.print(), 500);
    }
}