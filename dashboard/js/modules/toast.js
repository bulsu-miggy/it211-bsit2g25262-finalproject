// ═══════════════════ TOAST ═══════════════════

export function showToast(msg, type = 'success') {
    const icons = {success: 'check-circle', danger: 'times-circle', warning: 'exclamation-triangle', info: 'info-circle'};
    const colors = {success: '#6a9664', danger: '#c97070', warning: '#d9a84e', info: '#5a87a8'};
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${icons[type]}" style="color:${colors[type]};font-size:1.05rem;flex-shrink:0;"></i><span style="font-size:.875rem;font-weight:500;color:var(--text);">${msg}</span>`;
    document.getElementById('toastContainer').appendChild(t);
    setTimeout(() => t.remove(), 3600);
}