/**
 * modules/auth.js
 * Handles authentication-related logic, including screen switching, 
 * password visibility, and simulated login/logout processes.
 */

// ═══════════════════ AUTH ═══════════════════

import { showToast } from './toast.js';
import { closeModal } from './modals.js';

/**
 * Switches visibility between different auth screens (Login vs Forgot Password).
 */
export function showScreen(id) {
    document.querySelectorAll('.auth-screen').forEach(s => s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}

/**
 * Toggles an input field between 'password' and 'text' types.
 */
export function togglePw(inputId, iconEl) {
    const inp = document.getElementById(inputId);
    const ic = typeof iconEl === 'string' ? document.getElementById(iconEl) : iconEl;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ic.className = inp.type === 'text' ? 'fas fa-eye-slash' : 'fas fa-eye';
}

/**
 * Validates input and performs a simulated login redirect.
 */
export function doLogin() {
    const e = document.getElementById('loginEmail').value.trim();
    const p = document.getElementById('loginPass').value;
    let ok = true;
    document.getElementById('emailErr').style.display = 'none';
    document.getElementById('passErr').style.display = 'none';
    document.getElementById('loginError').style.display = 'none';
    if (!e || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)) {
        document.getElementById('emailErr').style.display = 'block';
        ok = false;
    }
    if (!p) {
        document.getElementById('passErr').style.display = 'block';
        ok = false;
    }
    if (!ok) return;
    if (e === 'admin@solis.com' && p === 'Solis@1') {
        // Redirect to dashboard on successful login
        window.location.href = 'dashboard.php';
    } else {
        document.getElementById('loginError').style.display = 'block';
        document.getElementById('loginErrMsg').textContent = 'Invalid email or password. Please try again.';
    }
}

/**
 * Simulates sending a password reset email.
 */
export function doForgot() {
    const e = document.getElementById('forgotEmail').value.trim();
    if (!e) {
        showToast('Please enter your registered email.', 'danger');
        return;
    }
    document.getElementById('forgotSuccess').style.display = 'block';
    document.getElementById('newPwBlock').style.display = 'block';
    showToast('Reset link sent! Check your inbox.', 'success');
}

/**
 * Logs out the user and clears sensitive input fields.
 */
export function doLogout() {
    closeModal('logoutModal');
    // Clear form values if they exist on this page
    const emailField = document.getElementById('loginEmail');
    const passField = document.getElementById('loginPass');
    if (emailField) emailField.value = '';
    if (passField) passField.value = '';
    // Redirect to logout script
    window.location.href = 'logout.php';
    showToast('You have been signed out. See you soon! 🕯️', 'info');
}

// ═══════════════════ PW RULES ═══════════════════
/**
 * Checks a password string against complexity rules and updates the UI.
 * @param {string} v - The password value
 * @param {string} set - Which rule set to target ('pw' or 'apw')
 */
export function checkRules(v, set) {
    const ids = set === 'pw' ? ['r1', 'r2', 'r3', 'r4'] : ['a1', 'a2', 'a3', 'a4'];
    const tests = [/^.{8,}$/, /[A-Z]/, /[0-9]/, /[!@#$%^&*()\-_+=]/];
    ids.forEach((id, i) => {
        const el = document.getElementById(id);
        const pass = tests[i].test(v);
        el.className = pass ? 'ok' : 'fail';
        el.querySelector('i').className = `fas fa-${pass ? 'check' : 'times'}`;
    });
}