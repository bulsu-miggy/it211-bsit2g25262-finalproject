/**
 * modules/navigation.js
 * Handles page switching and sidebar behavior within the dashboard.
 */

// ═══════════════════ NAV ═══════════════════

/**
 * Switches between different dashboard pages by toggling 'active' classes.
 * @param {string} page - The ID suffix of the page to show (e.g., 'products')
 * @param {HTMLElement} el - The navigation element that was clicked
 */
export function goTo(page, el) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById('page-' + page).classList.add('active');
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    if (el) el.classList.add('active');
    else document.querySelectorAll('.nav-item').forEach(n => {
        if ((n.getAttribute('onclick') || '').includes(`'${page}'`)) n.classList.add('active');
    });
}

/**
 * Collapses or expands the dashboard sidebar.
 */
export function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
}