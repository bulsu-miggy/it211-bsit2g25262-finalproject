/**
 * modules/modals.js
 * Provides utility functions for opening and closing modal overlays.
 */

// ═══════════════════ MODALS ═══════════════════

/**
 * Displays a modal by adding the 'active' class.
 */
export function openModal(id) {
    document.getElementById(id).classList.add('active');
}

/**
 * Hides a modal by removing the 'active' class.
 */
export function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

// Automatically close modals if the user clicks the dark background overlay
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal-overlay').forEach(o => {
        o.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });
});