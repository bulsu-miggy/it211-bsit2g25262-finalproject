// ═══════════════════ CUSTOMER VIEW ═══════════════════

import { customers } from './data.js';
import { openModal } from './modals.js';

export function viewCustomer(id) {
    const c = customers.find(x => x.id === id);
    document.getElementById('customerModalBody').innerHTML = `
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid var(--border);">
            <div class="avatar" style="width:60px;height:60px;font-size:1.3rem;">${c.name.split(' ').map(w => w[0]).join('')}</div>
            <div><div style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:700;color:var(--espresso);">${c.name}</div><div style="color:var(--muted);font-size:.85rem;">🌸 Customer since ${c.joined}</div></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);">
                <div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">Email</div><div style="font-size:.875rem;">${c.email}</div>
            </div>
            <div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);">
                <div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">Phone</div><div style="font-size:.875rem;">${c.phone}</div>
            </div>
            <div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);">
                <div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">Total Orders</div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;font-weight:700;color:var(--caramel);">${c.orders}</div>
            </div>
            <div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);">
                <div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">Total Spent</div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;font-weight:700;color:var(--success);">₱${c.spent.toLocaleString()}</div>
            </div>
            <div style="background:var(--cream);border-radius:10px;padding:14px;border:1px solid var(--border);grid-column:1/-1;">
                <div style="font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">Saved Address</div>
                <div><i class="fas fa-map-marker-alt" style="color:var(--caramel);margin-right:6px;"></i>${c.address}</div>
            </div>
        </div>`;
    openModal('customerModal');
}