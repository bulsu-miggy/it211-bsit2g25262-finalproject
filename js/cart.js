function addToBasket(event, form) {
    event.preventDefault();
    
    // 1. Setup jQuery form object and validation
    const $form = $(form);
    const quantityInput = $form.find('input[name="quantity"]');
    const quantity = parseInt(quantityInput.val());

    // Basic Validation: Stop if quantity is less than 1
    if (isNaN(quantity) || quantity < 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Selection Required',
            text: 'Please select a quantity of 1 or more.',
            confirmButtonColor: '#2A2A2A'
        });
        return;
    }

    // 2. AJAX Submission
    $.ajax({
        url: 'db/action/add_to_cart.php', // Ensure path is correct relative to the page
        type: 'POST',
        dataType: 'json',
        data: $form.serialize(),
        success: function (data) {
            if (!data || typeof data !== 'object') {
                console.error('Invalid JSON response');
                return;
            }
            if (data.status === 'success') {
                
                // 3. SweetAlert2 Success with Navigation Choice
                Swal.fire({
                    title: 'Added to Basket',
                    text: data.message, // Uses your PHP "Scent added to your basket"
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#2A2A2A', // Solis Black
                    cancelButtonColor: '#E0D0B6',  // Solis Gold
                    confirmButtonText: 'View Basket',
                    cancelButtonText: 'Continue Shopping',
                    reverseButtons: true, 
                    background: '#FFFDF8',
                    iconColor: '#2A2A2A',
                    fontFamily: 'Montserrat, sans-serif'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'basket.php'; 
                    }
                });

                // 4. Update Header Cart Count Bubble
                // This targets your .cart-count element and uses the count returned from PHP
                if ($('.cart-count').length) {
                    $('.cart-count')
                        .removeClass('hidden')
                        .text(data.count)
                        .css('display', 'flex')
                        .hide()
                        .fadeIn(400);
                }

                if (typeof window.updateStockBadge === 'function' && typeof data.stock_qty !== 'undefined') {
                    window.updateStockBadge(parseInt(data.stock_qty, 10));
                    $('#qty-input').val(1);
                }

            } else {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Notice', 
                    text: data.message, 
                    confirmButtonColor: '#2A2A2A' 
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error Response:", xhr.responseText || status || error);
            let alertText = 'Unable to add this item to your basket right now.';

            try {
                const data = JSON.parse(xhr.responseText);
                if (data && data.status === 'error' && data.message) {
                    alertText = data.message;
                }
            } catch (e) {
                // Ignore parse errors and show the generic message.
            }

            Swal.fire({ 
                icon: 'error', 
                title: 'Basket Error', 
                text: alertText, 
                confirmButtonColor: '#2A2A2A' 
            });
        }
    });
}