// ═══════════════════ ADMIN MODAL ═══════════════════

import { admins } from './data.js';
import { showToast } from './toast.js';
import { openModal, closeModal } from './modals.js';
import { renderAdmins } from './render.js';

let editAid = null;

export function openAdminModal(id) {
    // Hide unused password rule indicators (Uppercase, Number, Special)
    ['a2', 'a3', 'a4'].forEach(ruleId => {
        const el = document.getElementById(ruleId);
        if (el) el.style.display = 'none';
    });

    editAid = id || null;
    if (id) {
        const a = admins.find(x => x.id === id);
        document.getElementById('adminModalTitle').textContent = 'Edit Admin';
        document.getElementById('aFname').value = a.name.split(' ')[0];
        document.getElementById('aLname').value = a.name.split(' ').slice(1).join(' ');
        document.getElementById('aEmail').value = a.email;
        document.getElementById('aRole').value = a.role;
        document.getElementById('aPass').value = '';
    } else {
        document.getElementById('adminModalTitle').textContent = 'Add Admin User';
        ['aFname', 'aLname', 'aEmail', 'aPass'].forEach(id => document.getElementById(id).value = '');
    }
    openModal('adminModal');
}

export async function saveAdmin() {
    const fn = document.getElementById('aFname').value.trim();
    const ln = document.getElementById('aLname').value.trim();
    const email = document.getElementById('aEmail').value.trim();
    const role = document.getElementById('aRole').value;
    const pass = document.getElementById('aPass').value;

    if (!fn || !email) {
        showToast('Name and email are required.', 'danger');
        return;
    }

    // Basic validation for new accounts
    if (!editAid && (!pass || pass.length < 8)) {
        showToast('Password must be at least 8 characters.', 'danger');
        return;
    }

    const name = `${fn} ${ln}`.trim();
    const action = editAid ? 'update' : 'create';

    const body = {
        action,
        full_name: name,
        email,
        role,
        ...(editAid ? { id: editAid } : {}),
        ...(pass ? { password: pass } : {})
    };

    try {
        const response = await fetch('../db/action/admins_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const data = await response.json();

        if (data.status === 'success') {
            showToast(`"${name}" ${action === 'update' ? 'updated' : 'added'}!`, 'success');
            if (window.fetchDashboardAdmins) await window.fetchDashboardAdmins();
            closeModal('adminModal');
        } else {
            showToast(data.message || 'Error saving admin account.', 'danger');
        }
    } catch (error) {
        console.error('Admin Save Error:', error);
        showToast('Server connection failed.', 'danger');
    }
}