// ═══════════════════ CATEGORY MODAL ═══════════════════

import { categories } from './data.js';
import { showToast } from './toast.js';
import { openModal, closeModal } from './modals.js';
import { renderCategories } from './render.js';

let editCid = null;

export function openCatModal(id) {
    editCid = id || null;
    if (id) {
        const c = categories.find(x => x.id === id);
        document.getElementById('catModalTitle').textContent = 'Edit Category';
        document.getElementById('catName').value = c.name;
        document.getElementById('catSlug').value = c.slug;
    } else {
        document.getElementById('catModalTitle').textContent = 'Add Category';
        document.getElementById('catName').value = '';
        document.getElementById('catSlug').value = '';
    }
    openModal('catModal');
}

document.addEventListener('DOMContentLoaded', function() {
    const catNameInput = document.getElementById('catName');
    if (catNameInput) {
        catNameInput.addEventListener('input', function() {
            if (!editCid) {
                document.getElementById('catSlug').value = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
            }
        });
    }
});

export function saveCat() {
    const name = document.getElementById('catName').value.trim();
    const slug = document.getElementById('catSlug').value.trim();
    if (!name) {
        showToast('Category name required.', 'danger');
        return;
    }
    if (editCid) {
        const i = categories.findIndex(c => c.id === editCid);
        categories[i] = {...categories[i], name, slug};
        showToast(`"${name}" updated!`, 'success');
    } else {
        categories.push({id: categories.length + 1, name, slug, products: 0, created: 'Apr 12, 2025'});
        showToast(`"${name}" added!`, 'success');
    }
    renderCategories();
    closeModal('catModal');
}