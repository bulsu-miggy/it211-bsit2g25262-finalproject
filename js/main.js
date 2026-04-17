

        // Update total display
        function updateTotals() {
            let total = subtotal + shippingFee;
            
            document.getElementById('shippingFeeDisplay').innerText = '₱' + shippingFee;
            document.getElementById('totalPayment').innerHTML = '₱' + total.toFixed(2);
        }

        // Select payment method
        function selectPayment(element, method) {
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.classList.remove('selected');
                opt.querySelector('input').checked = false;
            });
            element.classList.add('selected');
            element.querySelector('input').checked = true;
        }

        // Select shipping option
        function selectShipping(element, fee, name) {
            document.querySelectorAll('.shipping-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            element.classList.add('selected');
            shippingFee = fee;
            updateTotals();
        }

        // Update address
        function updateAddress() {
            let newName = document.getElementById('newName').value;
            let newContact = document.getElementById('newContact').value;
            let newAddress = document.getElementById('newAddress').value;
            
            document.querySelector('.address-name').innerHTML = newName + ' <span class="badge bg-success ms-2">Default</span>';
            document.querySelector('.address-details').innerHTML = 
                '<i class="bi bi-telephone me-1"></i> ' + newContact + '<br>' +
                '<i class="bi bi-envelope me-1"></i> <?= $user_email ?><br>' +
                '<i class="bi bi-house-door me-1"></i> ' + newAddress;
            
            bootstrap.Modal.getInstance(document.getElementById('addressModal')).hide();
            showNotification('Address updated successfully!');
        }

        // Show notification
        function showNotification(message) {
            let notif = document.createElement('div');
            notif.className = 'alert alert-success position-fixed bottom-0 end-0 m-3';
            notif.style.zIndex = '9999';
            notif.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }


        /* ============================================
        MAIN JAVASCRIPT - Homepage & Product Pages
        =========================================== */

        // ============================================================
        // 1. UTILITY FUNCTIONS
        // Simple functions to handle quantity and color changes.
        // ============================================================

        // Updates the quantity input (plus or minus)
        function updateQty(change) {
            const qtyInput = document.getElementById('qtyInput');
            if (!qtyInput) return;
            let val = (parseInt(qtyInput.value) || 1) + change;
            qtyInput.value = val < 1 ? 1 : val;
        }

        // Swaps the main image and the product title text
        function changeColor(folder, img, name) {
            document.getElementById('displayFlask').src = `images/${folder}/${img}`;
            document.getElementById('productName').innerText = name;
        }

        // Shows/Hides items on the Listings page based on size
        function applyFilter(size) {
            document.querySelectorAll('.filter-item').forEach(item => {
                item.style.display = (size === 'all' || item.classList.contains('size-' + size)) ? 'block' : 'none';
            });
        }

        // Function to handle image loading errors
        function handleImageError(imgElement) {
            imgElement.onerror = null;
            imgElement.src = 'images/placeholder.png';
            console.warn('Image failed to load: ' + imgElement.src);
        }

        // ============================================================
        // 2. DATA CONFIGURATION
        // One place to manage all your product details.
        // ============================================================
        const productData = {
            '16oz': { 
                price: '₱850.00', 
                spec: '16oz / 473ml', 
                collection: '16oz Collection', 
                defaultImg: 'grapejuice.png',
                colors: [
                    { img: 'grapejuice.png', name: 'Grape Juice Flask 16oz', hex: '#7D92E3' },
                    { img: 'Pistachio.png', name: 'Pistachio Flask 16oz', hex: '#D7D9B1' },
                    { img: 'plum.png', name: 'Plum Flask 16oz', hex: '#7C5D7C' },
                    { img: 'Sage.png', name: 'Sage Flask 16oz', hex: '#8FA160' },
                    { img: 'Lilac.png', name: 'Soft Lilac 16oz', hex: '#E3D7E9' }
                ]
            },
            '25oz': { 
                price: '₱890.00', 
                spec: '25oz / 739ml', 
                collection: '25oz Collection', 
                defaultImg: 'keylime.png',
                colors: [
                    { img: 'keylime.png', name: 'Key Lime Flask 25oz', hex: '#E2E48E' },
                    { img: 'Khaki.png', name: 'Khaki Flask 25oz', hex: '#D7D9B1' },
                    { img: 'RoyalBlue.png', name: 'Royal Blue Flask 25oz', hex: '#6271D1' },
                    { img: 'Plum.png', name: 'Plum Flask 25oz', hex: '#8F7996' },
                    { img: 'Lavender.png', name: 'Lavender Flask 25oz', hex: '#D8CDE0' }
                ]
            },
            '32oz': { 
                price: '₱950.00', 
                spec: '32oz / 946ml', 
                collection: '32oz Collection', 
                defaultImg: 'noriflask.png',
                colors: [
                    { img: 'noriflask.png', name: 'Nori Flask 32oz', hex: '#1A2421' },
                    { img: 'DarkMoss.png', name: 'Dark Moss Flask 32oz', hex: '#3D4429' },
                    { img: 'SlateGray.png', name: 'Slate Gray Flask 32oz', hex: '#6B8077' },
                    { img: 'MutedGold.png', name: 'Muted Gold Flask 32oz', hex: '#A68E34' },
                    { img: 'Magenta.png', name: 'Magenta Flask 32oz', hex: '#e83e8c' }
                ]
            }
        };

        // ============================================================
        // 3. HOMEPAGE FUNCTIONS
        // ============================================================

        // Add smooth scrolling to anchor links
        function addSmoothScrolling() {
            const links = document.querySelectorAll('a[href^="#"]');
            
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        }

        // Add fade-in animation to products when they come into view
        function addFadeInAnimation() {
            const products = document.querySelectorAll('.product-item');
            
            // Create an intersection observer
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            // Set initial styles and observe each product
            products.forEach(product => {
                product.style.opacity = '0';
                product.style.transform = 'translateY(20px)';
                product.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(product);
            });
        }

        // Initialize product card interactions
        function initProductCards() {
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });
        }

        // Initialize button animations and click tracking
        function initButtons() {
            const buttons = document.querySelectorAll('.view-details-btn, .shop-more-btn');
            
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Optional: Add click tracking or animation
                    console.log('Button clicked: ' + this.innerText);
                });
            });
        }

        // Initialize homepage features
        function initHomepage() {
            console.log('Homepage loaded successfully');
            
            // Add smooth scrolling for anchor links
            addSmoothScrolling();
            
            // Add fade-in animation to products
            addFadeInAnimation();
        }

        // Initialize image error handling for product images
        function initImageErrorHandling() {
            const productImages = document.querySelectorAll('.product-img-container img');
            productImages.forEach(img => {
                img.addEventListener('error', function() {
                    handleImageError(this);
                });
            });
        }

        // ============================================================
        // 4. PRODUCT PAGE FUNCTIONS
        // ============================================================

        // Initialize product details page
        function initProductDetailsPage() {
            const params = new URLSearchParams(window.location.search);
            const size = params.get('size');
            const imgParam = params.get('img');

            if (!size || !productData[size]) return;
            
            const data = productData[size];

            // Find the current color object based on the URL image
            const currentColor = data.colors.find(c => c.img === imgParam) || data.colors[0];

            // Update Text and Images
            const productNameEl = document.getElementById('productName');
            const productPriceEl = document.getElementById('productPrice');
            const displayFlaskEl = document.getElementById('displayFlask');
            const breadcrumbSizeEl = document.getElementById('breadcrumbSize');
            const specSizeEl = document.getElementById('specSize');
            
            if (productNameEl) productNameEl.innerText = currentColor.name;
            if (productPriceEl) productPriceEl.innerText = data.price;
            if (displayFlaskEl) displayFlaskEl.src = `images/${size}/${currentColor.img}`;
            if (breadcrumbSizeEl) breadcrumbSizeEl.innerText = data.collection;
            if (specSizeEl) specSizeEl.innerText = data.spec;

            // Generate Swatches automatically
            const colorDiv = document.getElementById('colorOptionsContainer');
            if (colorDiv) {
                colorDiv.innerHTML = data.colors.map(c => `
                    <span class="color-swatch-large" 
                        style="background-color: ${c.hex};" 
                        onclick="changeColor('${size}', '${c.img}', '${c.name}')">
                    </span>
                `).join('');
            }
        }

        // Initialize listings page filter
        function initListingsPage() {
            const params = new URLSearchParams(window.location.search);
            const size = params.get('size');
            const filterDropdown = document.getElementById('sizeFilter');
            
            if (filterDropdown) {
                if (size) { 
                    filterDropdown.value = size; 
                    applyFilter(size); 
                }
                filterDropdown.onchange = (e) => applyFilter(e.target.value);
            }
        }

        // ============================================================
        // 5. MAIN INITIALIZATION
        // Runs when the page opens
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize homepage features (if on homepage)
            if (document.querySelector('.product-card, .product-item')) {
                initHomepage();
                initProductCards();
                initButtons();
                initImageErrorHandling();
            }
            
            // Initialize listings page filter (if on listings page)
            if (document.getElementById('sizeFilter')) {
                initListingsPage();
            }
        });

        // Auto-loader for product details page
        window.addEventListener('load', function() {
            // Initialize product details page (if on product page)
            if (document.getElementById('colorOptionsContainer') || document.getElementById('productName')) {
                initProductDetailsPage();
            }
        });

