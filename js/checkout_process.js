/**
 * SOLIS - Checkout & Basket Process Logic
 * Handles cart interactions, multi-step checkout validation, and UI updates.
 */

// --- GLOBAL UTILITIES ---

/**
 * Updates the quantity of an item via PHP redirect
 * @param {string} key - The product key/ID
 * @param {number} change - Positive or negative integer
 */
function updateQty(key, change) {
    if (!key) return;
    const encodedKey = encodeURIComponent(key);
    window.location.href = `db/action/update_cart_qty.php?key=${encodedKey}&change=${encodeURIComponent(change)}`;
}

$(document).ready(function() {

    // ==========================================
    // 1. BASKET PAGE INTERACTIONS
    // ==========================================

    /**
     * Prevents checkout if the basket is empty.
     */
    $('#btn-proceed-checkout').on('click', function(e) {
        e.preventDefault();
        const itemCount = parseInt($(this).attr('data-count'));

        if (itemCount <= 0 || isNaN(itemCount)) {
            Swal.fire({
                title: 'Basket Empty',
                text: 'Your shopping basket is currently empty. Add some Solis candles before checking out!',
                icon: 'info',
                confirmButtonColor: '#2A2A2A',
                confirmButtonText: 'Browse Shop'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'shop.php';
                }
            });
        } else {
            window.location.href = 'checkout.php';
        }
    });

    /**
     * Confirms item removal with a SweetAlert2 popup.
     */
    $('.js-remove-item').on('click', function() {
        const itemKey = $(this).data('key');
        const targetUrl = `db/action/remove_from_cart.php?key=${itemKey}`;

        Swal.fire({
            title: 'Remove item?',
            text: "Are you sure you want to remove this product from your basket?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2A2A2A',
            cancelButtonColor: '#888',
            confirmButtonText: 'Yes, remove',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = targetUrl;
            }
        });
    });


    // ==========================================
    // 2. CHECKOUT STEP 1: SHIPPING
    // ==========================================

    const $phoneInput = $('#phone_input');
    const $shippingForm = $('#shipping-form');

    if ($phoneInput.length) {
        $phoneInput.on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 11);
        });
    }

    $shippingForm.on('submit', function(e) {
        if ($phoneInput.val().length !== 11) {
            e.preventDefault();
            Swal.fire({
                title: 'Invalid Phone Number',
                text: 'Please enter a valid 11-digit phone number (e.g., 09123456789).',
                icon: 'error',
                confirmButtonColor: '#2A2A2A'
            });
            return false;
        }
        showLoadingState($(this));
    });


    // ==========================================
    // 3. CHECKOUT STEP 2: PAYMENT
    // ==========================================

    const $cardWallet = $('#card-wallet');
    const $cardCOD = $('#card-cod');
    const $radioWallet = $('#radio-wallet');
    const $radioCOD = $('#radio-cod');
    const $walletProviderInput = $('#wallet_provider');
    const $walletSubtitle = $('#wallet-subtitle');
    const $paymentDesc = $('#payment-desc');

    if ($cardWallet.length) {
        $cardWallet.on('click', function() {
            Swal.fire({
                title: 'Select E-Wallet',
                text: 'Choose your preferred provider:',
                showDenyButton: true,
                confirmButtonText: 'GCash',
                denyButtonText: 'PayMaya',
                confirmButtonColor: '#2A2A2A',
                denyButtonColor: '#2A2A2A'
            }).then((result) => {
                let provider = "";
                if (result.isConfirmed) provider = "GCash";
                else if (result.isDenied) provider = "PayMaya";

                if (provider) {
                    $radioWallet.prop('checked', true);
                    $walletProviderInput.val(provider);
                    $walletSubtitle.text("Selected: " + provider);
                    $cardWallet.addClass('selected');
                    $cardCOD.removeClass('selected');
                    $paymentDesc.html(`You will be redirected to your provider.<br><strong>${provider}</strong>`);
                }
            });
        });
    }

    if ($cardCOD.length) {
        $cardCOD.on('click', function() {
            $radioCOD.prop('checked', true);
            $cardCOD.addClass('selected');
            $cardWallet.removeClass('selected');
            $walletSubtitle.text("Gcash / PayMaya");
            $walletProviderInput.val("");
            $paymentDesc.text("Pay with cash when your order arrives.");
        });
    }

    $('#payment-form').on('submit', function(e) {
        if (!$radioWallet.is(':checked') && !$radioCOD.is(':checked')) {
            e.preventDefault();
            Swal.fire({
                title: 'Payment Required',
                text: 'Please select a payment method to continue.',
                icon: 'warning',
                confirmButtonColor: '#2A2A2A'
            });
            return false;
        }
        showLoadingState($(this));
    });


    // ==========================================
    // 4. CHECKOUT STEP 3: REVIEW & ORDER
    // ==========================================

    /**
     * Intercepts "Place Order" to show confirmation popup
     */
    $('#place-order-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);

        Swal.fire({
            title: 'Confirm Your Order',
            text: "Ready to light up your space? This will finalize your purchase.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2A2A2A',
            cancelButtonColor: '#888',
            confirmButtonText: 'Yes, Place Order',
            cancelButtonText: 'Wait, let me check'
        }).then((result) => {
            if (result.isConfirmed) {
                // Trigger visual processing state
                showLoadingState($form);
                // Submit the form for real
                $form.off('submit').submit();
            }
        });
    });


    // ==========================================
    // HELPER FUNCTIONS
    // ==========================================

    /**
     * Disables the submit button and shows a loading text
     */
    function showLoadingState($form) {
        const $btn = $form.find('.btn-continue');
        if ($btn.length) {
            $btn.html("Processing...").css({
                'opacity': '0.7',
                'pointer-events': 'none'
            });
        }
    }
});