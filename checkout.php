<?php
/**
 * ==========================================
 * CHECKOUT PAGE - PURCHASE CONFIRMATION
 * ==========================================
 * 
 * Purpose: Display order summary and collect purchase information before finalization
 * 
 * Process:
 * 1. Verify user is logged in (requireLogin)
 * 2. Check that shopping cart has items
 * 3. Calculate subtotal, shipping fee, and total
 * 4. Display order summary with product breakdown
 * 5. Collect payment method and optional seller message
 * 6. Submit to process_order.php for order creation
 * 
 * Access: Logged-in customers only
 */

// Start session to access cart and user data
session_start();

// Include authentication functions
require_once 'auth.php';

// Verify user is logged in before proceeding
requireLogin();

// ==========================================
// CART VALIDATION
// ==========================================
// Redirect if user tries to checkout with empty cart

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// ==========================================
// CALCULATE ORDER TOTALS
// ==========================================
// Sum up all items in cart, add shipping fee

// Initialize subtotal to 0
$subtotal = 0;

// Loop through each item in cart and add to subtotal
foreach ($_SESSION['cart'] as $item) {
    $subtotal += floatval($item['price']) * intval($item['quantity']);
}

// Fixed shipping fee amount
$shipping_fee = 50;

// Calculate grand total (subtotal + shipping)
$total = $subtotal + $shipping_fee;

// ==========================================
// RETRIEVE USER INFORMATION
// ==========================================
// Get logged-in user's details from session

// User's full name (set during login)
$user_name = $_SESSION['user_name'] ?? 'Guest User';

// User's email address (set during login)
$user_email = $_SESSION['user_email'] ?? 'user@example.com';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- ==========================================
         Navigation Bar
         ========================================== -->
    <nav class="navbar navbar-expand navbar-light bg-none sticky-top shadow-sm" style="background-color: #39afaf;">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                <img src="images/white.png" alt="SipFlask" height="49">
            </a>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse justify-content-end" id="sipNavbar">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item px-0"><a class="nav-link fw-bold text-white" href="index.php">Home</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold text-white" href="listings.php">Listings</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold text-white" href="contactUs.php">Contact Us</a></li>
                    <li class="nav-item ms-tight px-0">
                        <a class="nav-link p-0 text-white" href="cart.php"><i class="bi bi-cart4 fs-5"></i></a>
                    </li>
                    <li class="nav-item px-0">
                        <a class="nav-link fw-bold text-white" href="#">Hello, <?= htmlspecialchars($user_name) ?></a>
                    </li>
                    <li class="nav-item px-0">
                        <a class="nav-link fw-bold text-white" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container checkout-container my-4">
        <div class="row">
            <!-- Left Column: Address, Products, Payment -->
            <div class="col-lg-8">
                
                <!-- Delivery Address Section -->
                <div class="address-card">
                    <div class="card-header-custom">
                        <i class="bi bi-geo-alt-fill me-2" style="color: #1b739a;"></i> Delivery Address
                    </div>
                    <div class="address-display">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="address-name">
                                    <?= htmlspecialchars($user_name) ?> 
                                    <span class="badge bg-success ms-2">Default</span>
                                </div>
                                <div class="address-details mt-2">
                                    <i class="bi bi-telephone me-1"></i> +63 908 629 7002<br>
                                    <i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($user_email) ?><br>
                                    <i class="bi bi-house-door me-1"></i> 24 J.P. Rizal Street, Barangay Sto. Niño, Marikina City, 1800 
                                </div>
                            </div>
                            <button class="btn-change btn btn-link" data-bs-toggle="modal" data-bs-target="#addressModal">
                                <i class="bi bi-pencil"></i> Change
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Ordered Section -->
                <div class="product-card-checkout">
                    <div class="card-header-custom">
                        <i class="bi bi-box-seam me-2" style="color: #1b739a;"></i> Products Ordered
                    </div>
                    <div style="padding: 20px;">
                        <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-auto">
                                    <img src="images/<?= $item['size'] ?>/<?= $item['img'] ?>" alt="Product" class="product-img-checkout">
                                </div>
                                <div class="col">
                                    <div class="product-name-checkout"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="product-variation">Size: <?= strtoupper($item['size']) ?></div>
                                </div>
                                <div class="col-3 text-end">
                                    <div class="fw-bold">₱<?= number_format($item['price'], 2) ?></div>
                                    <div class="small text-muted">Qty: <?= $item['quantity'] ?></div>
                                </div>
                                <div class="col-2 text-end">
                                    <div class="fw-bold text-primary">₱<?= number_format(floatval($item['price']) * intval($item['quantity']), 2) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Payment Method Section -->
                <div class="payment-card">
                    <div class="card-header-custom">
                        <i class="bi bi-credit-card me-2" style="color: #1b739a;"></i> Payment Method
                    </div>
                    <div style="padding: 20px;">
                        <div class="payment-option selected" onclick="selectPayment(this, 'COD')">
                            <input type="radio" name="payment_method" value="COD" checked class="me-2"> 
                            <strong>Cash on Delivery (COD)</strong>
                            <span class="text-muted ms-2">Pay when you receive your order</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'GCASH')">
                            <input type="radio" name="payment_method" value="GCASH" class="me-2"> 
                            <strong>GCash</strong>
                            <span class="text-muted ms-2">Scan to pay via GCash</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'CARD')">
                            <input type="radio" name="payment_method" value="CARD" class="me-2"> 
                            <strong>Credit/Debit Card</strong>
                            <span class="text-muted ms-2">Visa, Mastercard, JCB</span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Option Section -->
                <div class="payment-card">
                    <div class="card-header-custom">
                        <i class="bi bi-truck me-2" style="color: #1b739a;"></i> Shipping Option
                    </div>
                    <div style="padding: 20px;">
                        <div class="shipping-option selected" onclick="selectShipping(this, 50, 'Standard Local')">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>📦 Standard Local Delivery</strong>
                                    <div class="small text-muted">Guaranteed delivery within 3-5 days</div>
                                </div>
                                <div class="fw-bold text-primary">₱50</div>
                            </div>
                        </div>
                        <div class="shipping-option" onclick="selectShipping(this, 90, 'Express Delivery')">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>⚡ Express Delivery</strong>
                                    <div class="small text-muted">Get it within 1-2 days</div>
                                </div>
                                <div class="fw-bold text-primary">₱90</div>
                            </div>
                        </div>
                        <div class="shipping-option" onclick="selectShipping(this, 0, 'Pickup')">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>🏪 Store Pickup</strong>
                                    <div class="small text-muted">Pick up at SipFlask Store - FREE</div>
                                </div>
                                <div class="fw-bold text-success">FREE</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="col-lg-4">
                <div class="summary-card" style="position: sticky; top: 20px;">
                    <div class="card-header-custom">
                        <i class="bi bi-receipt me-2" style="color: #1b739a;"></i> Order Summary
                    </div>
                    <div style="padding: 20px;">
                        <div class="price-breakdown">
                            <div class="price-row">
                                <span>Merchandise Subtotal</span>
                                <span id="merchandiseSubtotal">₱<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="price-row">
                                <span>Shipping Fee</span>
                                <span id="shippingFeeDisplay">₱50</span>
                            </div>
                            <div class="total-row">
                                <span>Total Payment</span>
                                <span id="totalPayment" class="text-primary">₱<?= number_format($total, 2) ?></span>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Message for Seller (Optional)</label>
                            <textarea class="form-control" id="sellerMessage" rows="2" placeholder="Leave a message for the seller..."></textarea>
                        </div>

                        <button class="btn-place-order" onclick="placeOrder()">
                            <i class="bi bi-check2-circle me-2"></i> PLACE ORDER
                        </button>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check"></i> Your order is protected
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Address Change Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Delivery Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="newName" value="<?= htmlspecialchars($user_name) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="newContact" value="+63 908 629 7002">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="newAddress" rows="3">Purok 6 Tikay Malolos Bulacan, Tikay, Malolos City, Bulacan 3005</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateAddress()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="custom-footer mt-5">
        <div class="container text-center">
            <div class="footer-logo text-white border-bottom pb-1 mb-5 fw-bold d-flex justify-content-center align-items-center">
                <img src="images/exactlogo.png" alt="SipFlask" height="55" class="me-2"> SipFlask
            </div>
            <div class="footer-bottom border-top pt-3 d-flex justify-content-between small opacity-75">
                <div>All Rights Reserved © 2026 SipFlask</div>
                <div>Website - SipFlask Website</div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        // Global variables for calculations
        let subtotal = <?= $subtotal ?>;
        let shippingFee = 50;

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

        // Place order
        function placeOrder() {
            let selectedPayment = document.querySelector('input[name="payment_method"]:checked');
            let paymentMethod = selectedPayment ? selectedPayment.value : 'COD';
            let sellerMessage = document.getElementById('sellerMessage').value;
            
            let total = subtotal + shippingFee;
            
            // Confirm order
            if (!confirm('Are you sure you want to place this order?\n\nTotal: ₱' + total.toFixed(2) + '\nPayment Method: ' + paymentMethod)) {
                return;
            }
            
            // Send order data to server
            let formData = new FormData();
            formData.append('payment_method', paymentMethod);
            formData.append('seller_message', sellerMessage);
            formData.append('shipping_fee', shippingFee);
            
            fetch('process_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = 'index.php';
                } else {
                    alert('Failed to place order. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>