$(document).ready(function() {
    let currentSlide = 0;
    const totalSlides = $('.slider-wrapper img').length;

    function updateSlider() {
        const offset = currentSlide * -100;
        $('#sliderWrapper').css('transform', `translateX(${offset}%)`);
        $('.dot').removeClass('active');
        $(`.dot[data-index="${currentSlide}"]`).addClass('active');
    }

    $('#nextSlide').on('click', function() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSlider();
    });

    $('#prevSlide').on('click', function() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        updateSlider();
    });

    $('.dot').on('click', function() {
        currentSlide = $(this).data('index');
        updateSlider();
    });

    let stockQty = parseInt($('#stock-qty').val() || '0', 10);

    function updateStockBadge(remaining) {
        const $badge = $('.stock-tag');
        const isAvailable = remaining > 0;
        const message = isAvailable ? (remaining <= 5 ? 'Only ' + remaining + ' left in stock' : remaining + ' in stock') : 'Out of stock';

        $badge.toggleClass('stock-available', isAvailable);
        $badge.toggleClass('stock-unavailable', !isAvailable);
        $badge.text(message);
        $('#stock-qty').val(remaining);
        stockQty = remaining;

        $('#plus-btn').prop('disabled', remaining <= 0);
        if (!isAvailable && $('button.add-to-cart-btn').length) {
            $('button.add-to-cart-btn').prop('disabled', true).text('Out of Stock');
        }
    }

    window.updateStockBadge = updateStockBadge;

    $('#plus-btn').on('click', function() {
        let qty = parseInt($('#qty-input').val());
        if (isNaN(stockQty) || stockQty <= 0) {
            Swal.fire({ icon: 'warning', title: 'Out of Stock', text: 'This product is not available right now.' });
            return;
        }
        if (qty >= stockQty) {
            Swal.fire({ icon: 'warning', title: 'Stock Limit Reached', text: 'You cannot add more than the available stock.' });
            return;
        }
        $('#qty-input').val(qty + 1);
    });

    $('#minus-btn').on('click', function() {
        let qty = parseInt($('#qty-input').val());
        if (qty > 1) { $('#qty-input').val(qty - 1); }
    });

    $('#size-dropdown').on('change', function() {
        let selectedPrice = $(this).find(':selected').data('price');
        $('#display-price').text(parseFloat(selectedPrice).toLocaleString(undefined, { minimumFractionDigits: 2 }));
    });

    $('.slider-image').each(function() {
        const placeholder = $(this).data('error-src');
        $(this).on('error', function() {
            if (placeholder) {
                $(this).attr('src', placeholder);
            }
        });
    });
});
