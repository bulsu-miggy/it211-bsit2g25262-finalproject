/**
 * modules/products.js
 * Handles CRUD operations for product management in the dashboard.
 */

// ═══════════════════ PRODUCT MODAL ═══════════════════

import { products } from './data.js';
import { showToast } from './toast.js';
import { openModal, closeModal } from './modals.js';
import { renderProducts } from './render.js';

let editPid = null;

/**
 * Opens the product editor modal.
 * @param {number|null} id - If provided, loads existing product data for editing.
 */
export function openProductModal(id) {
    editPid = id || null;
    if (id) {
        const p = products.find(x => x.id === id);
        document.getElementById('pModalTitle').textContent = 'Edit Candle';
        document.getElementById('pTitle').value = p.title;
        document.getElementById('pScent').value = p.scent;
        document.getElementById('pCat').value = p.category;
        document.getElementById('pPrice').value = p.price;
        document.getElementById('pStock').value = p.stock;
    } else {
        document.getElementById('pModalTitle').textContent = 'Add Candle Product';
        ['pTitle', 'pScent', 'pDesc', 'pPrice', 'pStock'].forEach(id => document.getElementById(id).value = '');
    }
    openModal('productModal');
}

/**
 * Validates and saves product changes to the local data array.
 */
export function saveProduct() {
    const title = document.getElementById('pTitle').value.trim();
    const scent = document.getElementById('pScent').value.trim() || 'Signature Blend';
    const price = parseFloat(document.getElementById('pPrice').value);
    const stock = parseInt(document.getElementById('pStock').value);
    const category = document.getElementById('pCat').value;
    if (!title || isNaN(price) || isNaN(stock)) {
        showToast('Please fill all required fields.', 'danger');
        return;
    }
    const status = stock === 0 ? 'Out of Stock' : stock <= 10 ? 'Low Stock' : 'In Stock';
    if (editPid) {
        const i = products.findIndex(p => p.id === editPid);
        products[i] = {...products[i], title, scent, price, stock, category, status};
        showToast(`"${title}" updated!`, 'success');
    } else {
        products.push({id: products.length + 1, title, category, scent, price, stock, status});
        showToast(`"${title}" added to catalog!`, 'success');
    }
    renderProducts();
    closeModal('productModal');
}

/**
 * Generates a thumbnail preview for uploaded images.
 */
export function previewImgs(e) {
    const c = document.getElementById('imgPreview');
    Array.from(e.target.files).forEach(f => {
        const r = new FileReader();
        r.onload = ev => {
            const d = document.createElement('div');
            d.className = 'img-thumb';
            d.innerHTML = `<img src="${ev.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;"/>
                <div class="rm" onclick="this.parentNode.remove()">✕</div>`;
            c.appendChild(d);
        };
        r.readAsDataURL(f);
    });
}

/**
 * Filters the product list by a search query.
 */
export function filterProducts(q) {
    renderProducts(q ? products.filter(p => p.title.toLowerCase().includes(q.toLowerCase()) || p.scent.toLowerCase().includes(q.toLowerCase())) : products);
}

/**
 * Filters the product list by category.
 */
export function filterByCategory(cat) {
    renderProducts(cat ? products.filter(p => p.category === cat) : products);
}