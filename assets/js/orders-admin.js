/**
 * UniMerch — Admin Orders Management
 */

$(document).ready(function() {
  loadAdminOrders();

  // Filters
  $('#orderSearch').on('input', debounce(loadAdminOrders, 350));
  $('.status-tab').on('click', function() {
    $('.status-tab').removeClass('active');
    $(this).addClass('active');
    // Sync mobile filter if visible
    $('#mobileStatusFilter').val($(this).data('status'));
    loadAdminOrders();
  });

  $('#mobileStatusFilter').on('change', function() {
    const status = $(this).val();
    // Sync desktop tabs
    $('.status-tab').removeClass('active');
    $(`.status-tab[data-status="${status}"]`).addClass('active');
    loadAdminOrders();
  });

  // Global event delegation for status changes
  $(document).on('change', '.payment-status-select', function() {
    updatePaymentStatus($(this).data('order-id'), $(this));
  });

  $(document).on('change', '.order-status-select', function() {
    updateOrderStatus($(this).data('order-id'), $(this));
  });
});

function loadAdminOrders() {
  const search = $('#orderSearch').val() || '';
  let status = 'all';
  
  if ($('#mobileStatusFilter').is(':visible')) {
    status = $('#mobileStatusFilter').val();
  } else {
    status = $('.status-tab.active').data('status') || 'all';
  }

  $.get(`${BASE_URL}/api/admin/orders.php`, { search, status }, function(res) {
    if (!res.success) return;
    renderOrders(res.data);
  });
}

function renderOrders(orders) {
  if (!orders || orders.length === 0) {
    $('#ordersTableBody').html('<tr><td colspan="8" class="text-center text-muted py-4">No orders found</td></tr>');
    return;
  }

  let html = '';
  orders.forEach(order => {
    const date = new Date(order.created_at);
    const formattedDate = date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });      const orderStatusOptions = ['pending','confirmed','processing','ready','completed','cancelled'].map(s => 
        `<option value="${s}" ${order.status === s ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`
      ).join('');

      html += `
      <tr class="mobile-order-row full-bleed-row">
        <!-- Mobile Card View -->
        <td colspan="8" class="p-0 d-md-none border-0 text-start">
          <div class="mobile-order-card p-3 border shadow-sm bg-white" style="width: 100%; border-radius: var(--radius-lg); box-sizing: border-box;">
            <!-- Header: Order # & Action -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="fw-bold text-primary fs-5">#${order.order_number}</span>
              <button class="btn btn-sm btn-outline-primary rounded-circle" onclick="viewOrderDetail(${order.id})">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            
            <!-- Body: Status Group -->
            <div class="row g-3">
              <div class="col-6 text-start">
                <label class="small text-muted fw-bold text-uppercase mb-1 d-block" style="font-size:0.65rem; letter-spacing:0.05em;">Payment</label>
                <select class="form-select form-select-sm payment-status-select badge-status-select badge-${order.payment_status} w-100" 
                        data-order-id="${order.id}">
                  <option value="unpaid" ${order.payment_status === 'unpaid' ? 'selected' : ''}>Unpaid</option>
                  <option value="pending_verification" ${order.payment_status === 'pending_verification' ? 'selected' : ''}>Pending</option>
                  <option value="paid" ${order.payment_status === 'paid' ? 'selected' : ''}>Paid</option>
                </select>
              </div>
              <div class="col-6 text-start">
                <label class="small text-muted fw-bold text-uppercase mb-1 d-block" style="font-size:0.65rem; letter-spacing:0.05em;">Status</label>
                <select class="form-select form-select-sm order-status-select w-100" 
                        data-order-id="${order.id}">
                  ${orderStatusOptions}
                </select>
              </div>
            </div>
          </div>
        </td>

        <!-- Order # (Desktop Only) -->
        <td class="d-none d-md-table-cell text-start">
          <div class="d-flex align-items-center">
             <div class="merchant-avatar me-2" style="width:32px; height:32px; background: var(--gray-50); color: var(--primary-600); font-size: 0.8rem; border: 1px solid var(--gray-100);">
               <i class="bi bi-receipt"></i>
             </div>
             <div>
               <div class="fw-bold" style="color:var(--primary-700);">${order.order_number}</div>
               <div class="small text-muted" style="font-size:0.75rem;">${formattedDate}</div>
             </div>
          </div>
        </td>
        
        <td data-label="Customer" class="d-none d-md-table-cell text-md-center">
          <div><strong>${order.customer_name}</strong></div>
          <div style="font-size: 0.75rem; color: var(--gray-500);">${order.customer_email}</div>
        </td>

        <td data-label="Items" class="d-none d-md-table-cell text-center text-md-center">${order.item_count} items</td>
        
        <td data-label="Total" class="d-none d-md-table-cell fw-bold text-end text-md-center">
          ₱${parseFloat(order.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}
        </td>
        
        <td data-label="Payment" class="d-none d-md-table-cell text-md-center">
          <div class="d-flex justify-content-md-center">
            <select class="form-select form-select-sm payment-status-select badge-status-select badge-${order.payment_status}" 
                    data-order-id="${order.id}" style="max-width: 150px;">
              <option value="unpaid" ${order.payment_status === 'unpaid' ? 'selected' : ''}>Unpaid</option>
              <option value="pending_verification" ${order.payment_status === 'pending_verification' ? 'selected' : ''}>Pending Verification</option>
              <option value="paid" ${order.payment_status === 'paid' ? 'selected' : ''}>Paid</option>
            </select>
          </div>
        </td>

        <td data-label="Status" class="d-none d-md-table-cell text-md-center">
          <div class="d-flex justify-content-md-center">
            <select class="form-select form-select-sm order-status-select" 
                    data-order-id="${order.id}" style="width:auto; min-width:130px; font-size:0.75rem;">
              ${orderStatusOptions}
            </select>
          </div>
        </td>

        <td class="d-none d-md-table-cell text-md-center" style="font-size:0.8rem; color:var(--gray-500);">${formattedDate}</td>
        
        <td class="text-center d-none d-md-table-cell">
          <button class="btn btn-ghost btn-sm d-flex align-items-center justify-content-center mx-auto" onclick="viewOrderDetail(${order.id})" title="View Details" style="width:36px; height:36px; border:1px solid var(--gray-200);">
            <i class="bi bi-eye"></i>
          </button>
        </td>
      </tr>
    `;
  });

  $('#ordersTableBody').html(html);
}

function updatePaymentStatus(id, select) {
  const status = select.val();
  // Only remove classes that are state-specific (unpaid, paid, etc.), NOT the core styling class
  const classList = (select.attr('class') || '').split(' ');
  const stateClass = classList.find(c => c.startsWith('badge-') && c !== 'badge-status-select');
  
  $.ajax({
    url: `${BASE_URL}/api/admin/orders.php`,
    method: 'PUT',
    contentType: 'application/json',
    data: JSON.stringify({ id, payment_status: status }),
    success(res) {
      if (res.success) {
        showToast(res.message, 'success');
        if (stateClass) select.removeClass(stateClass);
        select.addClass(`badge-${status}`);
      } else {
        showToast(res.message, 'error');
        loadAdminOrders();
      }
    }
  });
}

function updateOrderStatus(id, select) {
  const status = select.val();
  $.ajax({
    url: `${BASE_URL}/api/admin/orders.php`,
    method: 'PUT',
    contentType: 'application/json',
    data: JSON.stringify({ id, status }),
    success(res) {
      if (res.success) {
        showToast(res.message, 'success');
        loadAdminOrders();
      } else {
        showToast(res.message, 'error');
        loadAdminOrders();
      }
    }
  });
}

function viewOrderDetail(id) {
  const modalElement = document.getElementById('orderDetailModal');
  const modal = new bootstrap.Modal(modalElement);
  $('#orderDetailBody').html('<div class="text-center py-5"><div class="spinner mx-auto"></div><p class="mt-2 text-muted">Retrieving order data...</p></div>');
  modal.show();

  $.get(`${BASE_URL}/api/admin/orders.php`, { id }, function(res) {
    if (!res.success) {
      $('#orderDetailBody').html(`<div class="alert alert-danger mx-3 my-4"><i class="bi bi-exclamation-circle me-2"></i>${res.message}</div>`);
      return;
    }
    
    const o = res.data;
    let itemsHtml = '';
    o.items.forEach(item => {
      itemsHtml += `
        <tr>
          <td class="py-3">
            <div class="fw-bold text-dark">${item.product_name}</div>
            ${item.size ? `<span class="badge bg-light text-secondary fw-normal border mt-1">Size: ${item.size}</span>` : ''}
          </td>
          <td class="text-center py-3">x${item.quantity}</td>
          <td class="text-end py-3 fw-bold">₱${parseFloat(item.price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
        </tr>
      `;
    });

    const html = `
      <div class="order-detail-view px-2">
        <!-- Header Info -->
        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <div class="p-3 rounded-4 bg-light h-100">
              <h6 class="text-muted text-uppercase small ls-wide mb-3">Customer Information</h6>
              <div class="d-flex align-items-center gap-3">
                <div class="merchant-avatar" style="width:40px; height:40px; font-size:1rem; background:var(--primary-100); color:var(--primary-700);">
                  ${o.customer_name.charAt(0)}
                </div>
                <div>
                  <div class="fw-bold">${o.customer_name}</div>
                  <div class="small text-muted">${o.customer_email}</div>
                  <div class="small text-muted">${o.customer_phone || 'No phone'}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 text-md-end">
            <div class="p-3 rounded-4 border h-100">
              <h6 class="text-muted text-uppercase small ls-wide mb-3">Order Metadata</h6>
              <p class="mb-1">Number: <strong class="text-primary">${o.order_number}</strong></p>
              <p class="mb-1">Date: <strong>${new Date(o.created_at).toLocaleString()}</strong></p>
              <p class="mb-0">Payment: <span class="badge bg-info-subtle text-info text-capitalize">${o.payment_method.replace('_', ' ')}</span></p>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <div class="admin-panel mb-4 shadow-none border">
          <div class="admin-panel-header py-2 px-3 bg-light">
            <span class="small fw-bold text-uppercase text-muted">Itemized Receipt</span>
          </div>
          <div class="admin-panel-body p-0">
            <div class="table-responsive">
              <table class="table table-borderless align-middle mb-0">
                <thead class="border-bottom small">
                  <tr>
                    <th class="ps-3 text-muted">Product Description</th>
                    <th class="text-center text-muted">Quantity</th>
                    <th class="text-end pe-3 text-muted">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="border-bottom">${itemsHtml}</tbody>
                <tfoot>
                  <tr>
                    <td colspan="2" class="text-end py-4 px-3">
                      <div class="text-muted small">Total Billing</div>
                      <div class="fw-bold fs-5">Grand Total</div>
                    </td>
                    <td class="text-end py-4 pe-3">
                      <div class="text-muted small">PHP</div>
                      <div class="fw-black fs-4 text-primary">₱${parseFloat(o.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        ${o.notes ? `
          <div class="mt-4">
            <h6 class="text-muted text-uppercase small ls-wide mb-2">Customer Notes</h6>
            <div class="px-3 py-2 bg-light rounded-3 border-start border-4 border-primary small italic text-muted">
              "${o.notes}"
            </div>
          </div>
        ` : ''}
      </div>
    `;
    $('#orderDetailBody').html(html);
  });
}

function debounce(func, wait) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

// Global Toast utility (in case not loaded)
if (typeof showToast !== 'function') {
  function showToast(message, type = 'info') {
    alert(`${type.toUpperCase()}: ${message}`);
  }
}
