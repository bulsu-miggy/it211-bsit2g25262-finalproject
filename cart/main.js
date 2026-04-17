document.addEventListener("DOMContentLoaded", function () {

    const actionUrl = '../db/action/cart_action.php';
    const checkoutBtn = document.getElementById('checkout-btn');

    const queryParams = new URLSearchParams(window.location.search);
    if (queryParams.get('checkout_error') === 'missing_selection') {
        Swal.fire({ icon: 'warning', title: 'Select items first', text: 'Please select at least one cart item before checkout.' });
    } else if (queryParams.get('checkout_error') === 'invalid_selection') {
        Swal.fire({ icon: 'error', title: 'Invalid checkout selection', text: 'We could not load those items. Please try again.' });
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-checkbox')) {
            recalcSummary();
        }
    });

    function recalcSummary() {
        let total = 0, count = 0;

        document.querySelectorAll('.cart-item').forEach(item => {
            const qty     = parseInt(item.querySelector('.quantity').textContent) || 0;
            const price   = parseFloat(item.dataset.price) || 0;
            const pid     = item.dataset.productId;
            const checked = item.querySelector('.item-checkbox')?.checked ?? false;
            const row     = document.querySelector(`.summary-row[data-product-id="${pid}"]`);

            // Always keep the qty label in sync
            if (row) {
                row.querySelector('.qty-label').textContent = qty;
                row.querySelector('.item-subtotal').textContent =
                    '₱' + (qty * price).toLocaleString('en-PH', { minimumFractionDigits: 2 });
            }

            // Only count and show if checked
            if (checked) {
                total += qty * price;
                count++;
                if (row) row.style.setProperty('display', 'flex', 'important');
            } else {
                if (row) row.style.setProperty('display', 'none', 'important');
            }
        });

        document.getElementById('summary-count').textContent = count;
        document.getElementById('summary-total').textContent =
            '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
        document.getElementById('cart-count').textContent =
            document.querySelectorAll('.cart-item').length;
    }

    function postAction(data) {
        return fetch(actionUrl, {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams(data).toString()
        }).then(r => r.json());
    }

    function lockButton(button) {
        if (!button || button.dataset.cartBusy === '1') {
            return false;
        }

        button.dataset.cartBusy = '1';
        return true;
    }

    function unlockButton(button) {
        if (!button) {
            return;
        }

        window.setTimeout(() => {
            delete button.dataset.cartBusy;
        }, 150);
    }

    document.addEventListener('click', function(e) {
        const button = e.target.closest('.increase-btn');
        if (!button || !lockButton(button)) return;
        e.preventDefault();
        e.stopImmediatePropagation();

        const pid  = button.dataset.productId;
        const item = document.querySelector(`.cart-item[data-product-id="${pid}"]`);
        if (!item) {
            unlockButton(button);
            return;
        }

        const qEl  = item.querySelector('.quantity');
        const currentQty = parseInt(qEl.textContent) || 0;
        const stock = parseInt(item.dataset.stock) || 0;
        if (stock > 0 && currentQty >= stock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stock limit reached',
                text: 'You have reached the available stock for this item.'
            });
            unlockButton(button);
            return;
        }

        const newQ = currentQty + 1;

        postAction({action:'update', product_id:pid, quantity:newQ}).then(r => {
            if (r.success) {
                qEl.textContent = newQ;
                recalcSummary();
            }
        }).finally(() => unlockButton(button));
    });

    document.addEventListener('click', function(e) {
        const button = e.target.closest('.decrease-btn');
        if (!button || !lockButton(button)) return;
        e.preventDefault();
        e.stopImmediatePropagation();

        const pid  = button.dataset.productId;
        const item = document.querySelector(`.cart-item[data-product-id="${pid}"]`);
        if (!item) {
            unlockButton(button);
            return;
        }

        const qEl  = item.querySelector('.quantity');
        const newQ = (parseInt(qEl.textContent) || 0) - 1;

        if (newQ <= 0) {
            postAction({action:'remove', product_id:pid}).then(r => {
                if (r.success) {
                    item.remove();
                    document.querySelector(`.summary-row[data-product-id="${pid}"]`)?.remove();
                    recalcSummary();
                }
            }).finally(() => unlockButton(button));
        } else {
            postAction({action:'update', product_id:pid, quantity:newQ}).then(r => {
                if (r.success) {
                    qEl.textContent = newQ;
                    recalcSummary();
                }
            }).finally(() => unlockButton(button));
        }
    });

    document.querySelectorAll('.cart-item').forEach(item => {
        const qEl = item.querySelector('.quantity');
        const increaseBtn = item.querySelector('.increase-btn');
        const stock = parseInt(item.dataset.stock) || 0;
        const quantity = parseInt(qEl?.textContent || '0') || 0;

        if (increaseBtn && stock > 0 && quantity >= stock) {
            increaseBtn.disabled = true;
            increaseBtn.classList.add('disabled');
            increaseBtn.title = 'Out of stock limit reached';
        }
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-btn');
        if (!btn || !lockButton(btn)) return;
        e.preventDefault();
        e.stopImmediatePropagation();

        const pid  = btn.dataset.productId;
        const item = document.querySelector(`.cart-item[data-product-id="${pid}"]`);
        if (!item) {
            unlockButton(btn);
            return;
        }

        Swal.fire({
            title: 'Remove item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it'
        }).then(result => {
            if (result.isConfirmed) {
                postAction({action:'remove', product_id:pid}).then(r => {
                    if (r.success) {
                        item.remove();
                        document.querySelector(`.summary-row[data-product-id="${pid}"]`)?.remove();
                        recalcSummary();
                    }
                }).finally(() => unlockButton(btn));
            }
        });
    });

    const paybtn = document.getElementById('pay-now-btn');
    if (paybtn) {
        paybtn.addEventListener('click', function() {
            Swal.fire({
                icon: "success",
                iconColor: "#eab543",
                title: "Payment Successful!",
                text: "Your payment has been processed successfully.",
                confirmButtonText: "OK",
                footer: '<a href="orderHistory.php">Order details here!</a>',
                customClass: {
                    popup: 'swal-popup',
                    icon: 'swal-icon',
                    title: 'swal-title',
                    text: 'swal-text',
                    footer: 'swal-footer',
                    confirmButton: 'swal-confirm-button'
                },
                buttonsStyling: false
            });
        });
    }

    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function () {
            const selected = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                .map(cb => parseInt(cb.dataset.productId, 10))
                .filter(Number.isInteger)
                .filter(id => id > 0);

            const uniqueIds = [...new Set(selected)];

            if (uniqueIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No items selected',
                    text: 'Please select at least one item to continue to checkout.'
                });
                return;
            }

            window.location.href = `checkout.php?items=${encodeURIComponent(uniqueIds.join(','))}`;
        });
    }

    // Auto-check buy now item FIRST, then calculate summary
    const buyNowPid = new URLSearchParams(window.location.search).get('buynow');
    if (buyNowPid) {
        const checkbox = document.querySelector(`.item-checkbox[data-product-id="${buyNowPid}"]`);
        if (checkbox) {
            checkbox.checked = true;
            checkbox.closest('.cart-item')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    document.querySelectorAll('.summary-row').forEach(row => {
    row.style.setProperty('display', 'none', 'important');
    });
    // recalcSummary LAST so buynow checkbox is already set before calculating
    recalcSummary();

});