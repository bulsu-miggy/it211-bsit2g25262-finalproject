document.addEventListener('DOMContentLoaded', function() {
    // --- 1. PRICE UPDATE LOGIC ---
    const dropdown = document.getElementById('size-dropdown');
    const priceSpan = document.getElementById('display-price');

    if (dropdown && priceSpan) {
        dropdown.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const newPrice = parseFloat(selected.getAttribute('data-price'));

            // Updates the ₱ price on screen
            priceSpan.innerText = newPrice.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        });
    }

    // --- 2. QUANTITY COUNTER LOGIC ---
    const minusBtn = document.getElementById('minus-btn');
    const plusBtn = document.getElementById('plus-btn');
    const qtyInput = document.getElementById('qty-input');

    if (plusBtn && minusBtn && qtyInput) {
        // Increases value on plus click
        plusBtn.addEventListener('click', function() {
            let currentVal = parseInt(qtyInput.value);
            qtyInput.value = currentVal + 1;
        });

        // Decreases value on minus click (stops at 1)
        minusBtn.addEventListener('click', function() {
            let currentVal = parseInt(qtyInput.value);
            if (currentVal > 1) {
                qtyInput.value = currentVal - 1;
            }
        });
    }
});