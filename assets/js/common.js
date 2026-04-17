/**
 * UniMerch — Common Utilities
 */
console.log('UniMerch Common JS v1.1 Active');

// General Click Interceptor: Handled by window.handleLogout at the bottom of this file

// ============================================================
// Toast Utility
// ============================================================
window.showToast = function(message, type = 'info', duration = 4000) {
  console.log('showToast triggered:', type, message);
  const icons = {
    success: 'check-circle-fill',
    error: 'exclamation-circle-fill',
    warning: 'exclamation-triangle-fill',
    info: 'info-circle-fill'
  };
  
  const toastId = 'toast_' + Math.random().toString(36).substr(2, 9);
  const toast = $(`
    <div class="toast toast-${type}" id="${toastId}">
      <div class="d-flex align-items-center gap-2 w-100">
        <i class="bi bi-${icons[type] || icons.info} fs-5" style="color:var(--${type === 'error' ? 'danger' : type});"></i>
        <div class="toast-content flex-grow-1">${message}</div>
      </div>
    </div>
  `);
  
  let container = $('#toastContainer');
  if (container.length === 0) {
    console.warn('#toastContainer not found, appending to body');
    container = $('<div class="toast-container" id="toastContainer"></div>').appendTo('body');
  }
  
  container.append(toast);
  
  if (duration !== Infinity) {
    setTimeout(() => {
      $(`#${toastId}`).fadeOut(300, function() { $(this).remove(); });
    }, duration);
  }
  
  return toastId;
};

/**
 * Definitive Logout Handler with Activity Check
 * Used by both Admin and Customer sides.
 */
window.handleLogout = function(e, context = null) {
  if (e) {
    if (typeof e.preventDefault === 'function') e.preventDefault();
    if (typeof e.stopPropagation === 'function') e.stopPropagation();
  }
  
  const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/unimerch';
  
  // Auto-detect context if not provided
  if (!context) {
    context = window.location.pathname.includes('/admin') ? 'merchant' : 'customer';
  }

  const isMerchant = context === 'merchant';
  const logoutUrl = isMerchant ? `${baseUrl}/admin/logout.php` : `${baseUrl}/logout.php`;
  const checkoutUrl = isMerchant ? `${baseUrl}/admin/orders.php` : `${baseUrl}/cart.php`;
  
  const primaryText = isMerchant ? 'Attention: Pending Orders' : 'Attention: Active Cart';
  const actionText = isMerchant ? 'Manage Orders' : 'Checkout Now';
  const descText = isMerchant ? 'pending orders' : 'items in your cart';
  const subTextStyle = isMerchant ? 'text-white-50' : 'text-muted';
  const btnPrimary = isMerchant ? 'btn-light' : 'btn-primary-gradient';
  const btnSecondary = isMerchant ? 'btn-outline-light' : 'btn-ghost';

  console.log('[Logout] Checking status for:', context, 'at', logoutUrl);

  $.ajax({
    url: logoutUrl,
    data: { action: 'check' },
    method: 'GET',
    dataType: 'json',
    cache: false,
    timeout: 4000,
    success: function(res) {
      if (res && res.has_items) {
        // Redirect to the server-side confirmation gate
        console.log('[Logout] Activity detected, redirecting to confirmation gate');
        window.location.href = logoutUrl;
      } else {
        // No items, proceed to immediate logout
        console.log('[Logout] No items, proceeding to direct logout');
        window.location.href = logoutUrl;
      }
    },
    error: function() {
      // Direct jump on any failure
      window.location.href = logoutUrl;
    }
  });

  return false;
};

// Global interceptor for all logout links
$(document).on('click', 'a[href*="logout.php"]', function(e) {
  if (this.href.includes('force=true')) return true;
  return window.handleLogout(e);
});
