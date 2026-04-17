/**
 * UniMerch — Cart JavaScript
 * Cart page CRUD operations and badge management
 */

// ============================================================
// Toast (if not already defined by storefront.js)
// ============================================================
if (typeof showToast === 'undefined') {
  window.showToast = function(message, type = 'info') {
    const icons = {
      success: 'check-circle-fill',
      error: 'exclamation-circle-fill',
      warning: 'exclamation-triangle-fill',
      info: 'info-circle-fill'
    };
    const toast = $(`
      <div class="toast toast-${type}">
        <i class="bi bi-${icons[type] || icons.info}" style="color:var(--${type === 'error' ? 'danger' : type});"></i>
        <span>${message}</span>
      </div>
    `);
    $('#toastContainer').append(toast);
    setTimeout(() => toast.fadeOut(300, function() { $(this).remove(); }), 4000);
  };
}

// ============================================================
// Cart Page — Load & Render
// ============================================================
function loadCartPage() {
  $.get(`${BASE_URL}/api/cart.php`, function(res) {
    if (!res.success) return;

    const { data, summary } = res;

    if (data.length === 0) {
      $('#cartItems').hide();
      $('#cartSummaryCol').hide();
      $('#cartEmpty').show();
      $('#cartItemCount').text('0 items in your cart');
      return;
    }

    $('#cartEmpty').hide();
    $('#cartItems').show();
    $('#cartSummaryCol').show();
    $('#cartItemCount').text(`${summary.item_count} item(s) in your cart`);

    // Render items
    let html = '';
    data.forEach(item => {
      const imgSrc = item.image_url;
      const fallback = `https://placehold.co/90x90/e2e8f0/64748b?text=IMG`;
      
      html += `
        <div class="cart-item" data-cart-id="${item.id}">
          <div class="cart-item-img">
            <img src="${imgSrc}" alt="${item.name}" onerror="this.src='${fallback}'">
          </div>
          <div class="cart-item-info">
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-meta">
              ${item.category_code}
              ${item.size ? ' · Size: ' + item.size : ''}
            </div>
            <div class="d-flex align-items-center gap-2 mt-2">
              <div class="qty-selector" style="border-width:1px;">
                <button type="button" class="cart-qty-minus" data-id="${item.id}" style="width:32px;height:32px;font-size:0.9rem;">−</button>
                <input type="number" value="${item.quantity}" min="1" max="${item.stock}" 
                       class="cart-qty-input" data-id="${item.id}" style="width:40px;height:32px;font-size:0.85rem;" readonly>
                <button type="button" class="cart-qty-plus" data-id="${item.id}" style="width:32px;height:32px;font-size:0.9rem;">+</button>
              </div>
            </div>
          </div>
          <div class="cart-item-price">₱${(item.price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
          <div class="cart-item-remove" onclick="removeCartItem(${item.id})" title="Remove">
            <i class="bi bi-trash3"></i>
          </div>
        </div>
      `;
    });

    $('#cartItems').html(html);

    // Update summary
    $('#cartSubtotal').text(`₱${summary.subtotal.toLocaleString('en-PH', {minimumFractionDigits:2})}`);
    $('#cartTotal').text(`₱${summary.total.toLocaleString('en-PH', {minimumFractionDigits:2})}`);
    $('#checkoutBtn').prop('disabled', false);

  }).fail(function() {
    $('#cartItems').html(`
      <div class="text-center py-4">
        <i class="bi bi-wifi-off" style="font-size:2rem; color:var(--gray-300);"></i>
        <p class="text-muted mt-2">Failed to load cart. Please try again.</p>
      </div>
    `);
  });
}

// ============================================================
// Cart Qty Controls
// ============================================================
$(document).on('click', '.cart-qty-minus', function() {
  const id = $(this).data('id');
  const input = $(`.cart-qty-input[data-id="${id}"]`);
  let val = parseInt(input.val());
  if (val > 1) {
    input.val(val - 1);
    updateCartQty(id, val - 1);
  }
});

$(document).on('click', '.cart-qty-plus', function() {
  const id = $(this).data('id');
  const input = $(`.cart-qty-input[data-id="${id}"]`);
  let val = parseInt(input.val());
  let max = parseInt(input.attr('max'));
  if (val < max) {
    input.val(val + 1);
    updateCartQty(id, val + 1);
  }
});

function updateCartQty(cartId, quantity) {
  $.ajax({
    url: `${BASE_URL}/api/cart.php`,
    method: 'PUT',
    contentType: 'application/json',
    data: JSON.stringify({ cart_id: cartId, quantity }),
    success(res) {
      if (res.success) {
        loadCartPage(); // Refresh cart
      } else {
        showToast(res.message, 'error');
      }
    }
  });
}

function removeCartItem(cartId) {
  $.ajax({
    url: `${BASE_URL}/api/cart.php?id=${cartId}`,
    method: 'DELETE',
    success(res) {
      if (res.success) {
        showToast('Item removed', 'success');
        loadCartPage();
        loadCartCount();
      }
    }
  });
}

// ============================================================
// Cart Badge (shared)
// ============================================================
if (typeof updateCartBadge === 'undefined') {
  window.updateCartBadge = function(count) {
    const badge = $('#navCartBadge');
    if (count > 0) {
      badge.text(count).show();
    } else {
      badge.hide();
    }
  };
}

if (typeof loadCartCount === 'undefined') {
  window.loadCartCount = function() {
    $.get(`${BASE_URL}/api/cart.php`, function(res) {
      if (res.success) updateCartBadge(res.summary.item_count);
    });
  };
}

// ============================================================
// Init — only if we're on the cart page
// ============================================================
$(document).ready(function() {
  if ($('#cartItems').length) {
    loadCartPage();
  }
  // Always try to update the cart badge
  loadCartCount();
});
