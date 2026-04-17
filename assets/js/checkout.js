/**
 * UniMerch — Checkout JavaScript
 */

$(document).ready(function() {
  loadCheckoutSummary();
  
  // Payment method selection
  $(document).on('click', '.payment-method-card', function() {
    $('.payment-method-card').removeClass('selected');
    $(this).addClass('selected');
    
    const method = $(this).data('method');
    if (method === 'gcash' || method === 'bank_transfer') {
      $('#paymentProofSection').slideDown(200);
    } else {
      $('#paymentProofSection').slideUp(200);
    }
  });

  // Payment proof preview
  $('#paymentProof').on('change', function() {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        $('#paymentProofZone').addClass('has-image')
          .html(`<img src="${e.target.result}" alt="Payment Proof">`);
      };
      reader.readAsDataURL(file);
    }
  });

  // Place order
  $('#placeOrderBtn').on('click', placeOrder);
  
  // Enable/disable checkout button based on form validity
  $('#checkoutForm input[required]').on('input', validateCheckoutForm);
});

function loadCheckoutSummary() {
  $.get(`${BASE_URL}/api/cart.php`, function(res) {
    if (!res.success || res.data.length === 0) {
      window.location.href = BASE_URL + '/cart.php';
      return;
    }

    let itemsHTML = '';
    res.data.forEach(item => {
      const fallback = `https://placehold.co/50x50/e2e8f0/64748b?text=IMG`;
      itemsHTML += `
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="${item.image_url}" alt="" 
               style="width:50px; height:50px; border-radius:8px; object-fit:cover;"
               onerror="this.src='${fallback}'">
          <div class="flex-grow-1">
            <div class="fw-semibold" style="font-size:0.85rem;">${item.name}</div>
            <small class="text-muted">Qty: ${item.quantity}${item.size ? ' · ' + item.size : ''}</small>
          </div>
          <div class="fw-bold" style="font-size:0.9rem;">₱${item.line_total.toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
        </div>
      `;
    });

    $('#checkoutItems').html(itemsHTML);
    $('#checkoutSubtotal').text(`₱${res.summary.subtotal.toLocaleString('en-PH', {minimumFractionDigits:2})}`);
    $('#checkoutTotal').text(`₱${res.summary.total.toLocaleString('en-PH', {minimumFractionDigits:2})}`);

    validateCheckoutForm();
  });
}

function validateCheckoutForm() {
  const firstName = $('#firstName').val().trim();
  const lastName = $('#lastName').val().trim();
  const email = $('#email').val().trim();
  const phone = $('#phone').val().trim();
  
  const isValid = firstName && lastName && email && phone;
  $('#placeOrderBtn').prop('disabled', !isValid);
}

function placeOrder() {
  const btn = $('#placeOrderBtn');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

  const selectedPayment = $('.payment-method-card.selected').data('method') || 'cash';

  $.ajax({
    url: `${BASE_URL}/api/orders.php`,
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      customer_name: `${$('#firstName').val().trim()} ${$('#lastName').val().trim()}`,
      customer_email: $('#email').val().trim(),
      customer_phone: $('#phone').val().trim(),
      payment_method: selectedPayment,
      notes: $('#notes').val().trim()
    }),
    success(res) {
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => {
          window.location.href = res.redirect;
        }, 800);
      } else {
        showToast(res.message, 'error');
        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Place Order');
      }
    },
    error() {
      showToast('Failed to place order. Please try again.', 'error');
      btn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Place Order');
    }
  });
}
