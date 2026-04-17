<?php
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: loginpage.php');
    exit();
}

require_once __DIR__ . '/ProductManager.php';
require_once '../db/conn.php';

$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];

// Get user data including address
$stmt = $conn->prepare("SELECT full_name, email, address, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$productManager = new ProductManager();

$items = [];
$subtotal = 0;
$delivery_fee = 2.99;
$total = 0;
$is_single_product = false;

if (isset($_GET['product_id'])) {
    $product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
    if ($product_id === false || $product_id === null) {
        echo '<p style="padding: 2rem; font-family: sans-serif;">Invalid product selected. Please go back and try again.</p>';
        exit();
    }
    $is_single_product = true;
    $product = $productManager->getProductById($product_id);
    if ($product) {
        $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
        $items[] = [
            'product_id' => $product['id'],
            'title' => $product['name'],
            'brand' => $product['brand'] ?? '',
            'price' => $product['price'],
            'quantity' => $quantity
        ];
        $subtotal = $product['price'] * $quantity;
    } else {
        echo '<p style="padding: 2rem; font-family: sans-serif;">Product not found. Please return to the menu.</p>';
        exit();
    }
} else {
    $items = $productManager->getCartItems();
    if (empty($items)) {
        echo '<p style="padding: 2rem; font-family: sans-serif;">Your cart is empty. Add products before checking out.</p>';
        exit;
    }
    $subtotal = $productManager->getCartTotal();
}

$total = $subtotal + $delivery_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Lasa Filipina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(145deg, #f7f3e9 0%, #f0ece1 100%);
            padding: 2rem;
        }
        .checkout-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .checkout-header {
            background: #1e1e2a;
            color: white;
            padding: 1.5rem;
        }
        .checkout-item {
            border-bottom: 1px solid #eee;
            padding: 1rem;
        }
        .confirm-btn {
            background: #c97e2a;
            color: white;
            width: 100%;
            padding: 1rem;
            font-size: 1.2rem;
            border: none;
        }
        .confirm-btn:hover {
            background: #b0681c;
        }
        .form-control:focus {
            border-color: #c97e2a;
            box-shadow: 0 0 0 0.2rem rgba(201, 126, 42, 0.25);
        }
    </style>
</head>
<body>
    <div class="checkout-card">
        <div class="checkout-header">
            <h1>🛒 Checkout</h1>
            <p>Lasa Filipina</p>
        </div>

        <div class="checkout-items p-4">
            <h4>Order Summary</h4>
            <?php foreach ($items as $item): ?>
            <div class="checkout-item">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5><?= htmlspecialchars($item['title']) ?></h5>
                        <small class="text-muted"><?= htmlspecialchars($item['brand']) ?></small>
                    </div>
                    <div class="col-md-2">
                        $<?= number_format($item['price'], 2) ?>
                    </div>
                    <div class="col-md-2">
                        <?php if ($is_single_product): ?>
                        <input type="number" class="form-control quantity-input" value="<?= $item['quantity'] ?>" min="1" onchange="updateQuantity(this)">
                        <?php else: ?>
                        <?= $item['quantity'] ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-2">
                        $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="summary mt-4">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span id="subtotal">$<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Delivery Fee:</span>
                    <span>$<?= number_format($delivery_fee, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 fw-bold">
                    <span>Total:</span>
                    <span id="total">$<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="checkout-form p-4 bg-light">
            <h4>Payment & Delivery Details</h4>
            
            <!-- Delivery Address Section -->
            <div class="mb-3">
                <label class="form-label">Delivery Address</label>
                <div class="address-display p-3 border rounded bg-white">
                    <p class="mb-2"><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></p>
                    <p class="mb-0"><?php echo htmlspecialchars($user['address']); ?></p>
                    <p class="mb-0"><?php echo htmlspecialchars($user['phone']); ?></p>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addressModal">
                    Change Address
                </button>
            </div>
            
            <div class="mb-3">
                <label for="payment_mode" class="form-label">Mode of Payment</label>
                <select class="form-control" id="payment_mode" required onchange="showPaymentForm()">
                    <option value="">Select Payment Method</option>
                    <option value="Cash">Cash</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            
            <!-- Hidden delivery location for form submission -->
            <input type="hidden" id="location" value="<?php echo htmlspecialchars($user['address']); ?>">
            
            <div class="d-flex flex-column flex-md-row gap-3">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="window.history.back()">Browse More</button>
                <button class="confirm-btn w-100" onclick="confirmOrder()">Confirm Order 🍕</button>
            </div>
        </div>
    </div>

    <!-- Address Change Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">Change Delivery Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="address_option" id="use_profile" value="profile" checked>
                            <label class="form-check-label" for="use_profile">
                                Use Profile Address
                            </label>
                        </div>
                        <div class="address-display p-3 border rounded bg-light ms-4">
                            <p class="mb-1"><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></p>
                            <p class="mb-1"><?php echo htmlspecialchars($user['address']); ?></p>
                            <p class="mb-0"><?php echo htmlspecialchars($user['phone']); ?></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="address_option" id="new_address" value="new">
                            <label class="form-check-label" for="new_address">
                                Add New Address
                            </label>
                        </div>
                        <div class="new-address-form ms-4" style="display: none;">
                            <div class="mb-3">
                                <label for="new_full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="new_full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="new_address_text" class="form-label">Address</label>
                                <textarea class="form-control" id="new_address_text" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="new_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="new_phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateAddress()">Update Address</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Forms Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Cash Payment Form -->
                    <div id="cashForm" style="display: none;">
                        <h6>Cash on Delivery</h6>
                        <p>You will pay in cash when your order is delivered.</p>
                        <div class="alert alert-info">
                            <small>Please have the exact amount ready for the delivery person.</small>
                        </div>
                    </div>
                    
                    <!-- Debit Card Form -->
                    <div id="debitCardForm" style="display: none;">
                        <h6>Debit Card Payment</h6>
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiry_date" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" placeholder="123" maxlength="4">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="card_name" class="form-label">Name on Card</label>
                            <input type="text" class="form-control" id="card_name" placeholder="John Doe">
                        </div>
                    </div>
                    
                    <!-- Others Payment Form -->
                    <div id="othersForm" style="display: none;">
                        <h6>Other Payment Methods</h6>
                        <div class="mb-3">
                            <label for="payment_type" class="form-label">Payment Type</label>
                            <select class="form-control" id="payment_type">
                                <option value="">Select Type</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Online Banking">Online Banking</option>
                                <option value="Digital Wallet">Digital Wallet</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div id="additional_fields"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="proceedPaymentBtn" onclick="proceedPayment()">Proceed with Payment</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle payment type change for "Others"
        document.getElementById('payment_type').addEventListener('change', function() {
            const paymentType = this.value;
            const additionalFields = document.getElementById('additional_fields');
            
            additionalFields.innerHTML = '';
            
            if (paymentType === 'Credit Card') {
                additionalFields.innerHTML = `
                    <div class="mb-3">
                        <label for="cc_number" class="form-label">Card Number</label>
                        <input type="text" class="form-control" id="cc_number" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cc_expiry" class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" id="cc_expiry" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cc_cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cc_cvv" placeholder="123" maxlength="4">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="cc_name" class="form-label">Name on Card</label>
                        <input type="text" class="form-control" id="cc_name" placeholder="John Doe">
                    </div>
                `;
            } else if (paymentType === 'Online Banking') {
                additionalFields.innerHTML = `
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Bank Name</label>
                        <select class="form-control" id="bank_name">
                            <option value="">Select Bank</option>
                            <option value="BDO">BDO</option>
                            <option value="BPI">BPI</option>
                            <option value="Metrobank">Metrobank</option>
                            <option value="PNB">PNB</option>
                            <option value="UnionBank">UnionBank</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="account_number" class="form-label">Account Number</label>
                        <input type="text" class="form-control" id="account_number" placeholder="Enter account number">
                    </div>
                `;
            } else if (paymentType === 'Digital Wallet') {
                additionalFields.innerHTML = `
                    <div class="mb-3">
                        <label for="wallet_type" class="form-label">Wallet Type</label>
                        <select class="form-control" id="wallet_type">
                            <option value="">Select Wallet</option>
                            <option value="GCash">GCash</option>
                            <option value="PayMaya">PayMaya</option>
                            <option value="Coins.ph">Coins.ph</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="wallet_number" class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" id="wallet_number" placeholder="09XX XXX XXXX">
                    </div>
                `;
            } else if (paymentType === 'Bank Transfer') {
                additionalFields.innerHTML = `
                    <div class="mb-3">
                        <label for="transfer_bank" class="form-label">Bank Name</label>
                        <select class="form-control" id="transfer_bank">
                            <option value="">Select Bank</option>
                            <option value="BDO">BDO</option>
                            <option value="BPI">BPI</option>
                            <option value="Metrobank">Metrobank</option>
                            <option value="PNB">PNB</option>
                            <option value="UnionBank">UnionBank</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <small>Bank details will be provided after order confirmation.</small>
                    </div>
                `;
            }
        });

        // Handle address option change
        document.querySelectorAll('input[name="address_option"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const newAddressForm = document.querySelector('.new-address-form');
                if (this.value === 'new') {
                    newAddressForm.style.display = 'block';
                } else {
                    newAddressForm.style.display = 'none';
                }
            });
        });

        let selectedPaymentMethod = null;
        let paymentDetails = {};

        function updateAddress() {
            const selectedOption = document.querySelector('input[name="address_option"]:checked').value;
            let addressText = '';
            
            if (selectedOption === 'profile') {
                addressText = `<?php echo htmlspecialchars($user['full_name']); ?>\n<?php echo htmlspecialchars($user['address']); ?>\n<?php echo htmlspecialchars($user['phone']); ?>`;
            } else {
                const fullName = document.getElementById('new_full_name').value;
                const address = document.getElementById('new_address_text').value;
                const phone = document.getElementById('new_phone').value;
                addressText = `${fullName}\n${address}\n${phone}`;
            }
            
            // Update the hidden location field
            document.getElementById('location').value = addressText;
            
            // Update the display
            const addressDisplay = document.querySelector('.address-display');
            const lines = addressText.split('\n');
            addressDisplay.innerHTML = `
                <p class="mb-2"><strong>${lines[0]}</strong></p>
                <p class="mb-0">${lines[1]}</p>
                <p class="mb-0">${lines[2] || ''}</p>
            `;
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addressModal'));
            modal.hide();
        }

        function showPaymentForm() {
            const paymentMode = document.getElementById('payment_mode').value;
            if (!paymentMode) return;
            
            selectedPaymentMethod = paymentMode;
            
            // Hide all forms
            document.getElementById('cashForm').style.display = 'none';
            document.getElementById('debitCardForm').style.display = 'none';
            document.getElementById('othersForm').style.display = 'none';
            
            // Show selected form
            if (paymentMode === 'Cash') {
                document.getElementById('cashForm').style.display = 'block';
            } else if (paymentMode === 'Debit Card') {
                document.getElementById('debitCardForm').style.display = 'block';
            } else if (paymentMode === 'Others') {
                document.getElementById('othersForm').style.display = 'block';
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        }

        function proceedPayment() {
            paymentDetails = {};
            
            if (selectedPaymentMethod === 'Cash') {
                paymentDetails.type = 'Cash';
                paymentDetails.notes = 'Cash on Delivery';
            } else if (selectedPaymentMethod === 'Debit Card') {
                const cardNumber = document.getElementById('card_number').value;
                const expiryDate = document.getElementById('expiry_date').value;
                const cvv = document.getElementById('cvv').value;
                const cardName = document.getElementById('card_name').value;
                
                if (!cardNumber || !expiryDate || !cvv || !cardName) {
                    Swal.fire('Error', 'Please fill in all card details', 'error');
                    return;
                }
                
                paymentDetails = {
                    type: 'Debit Card',
                    cardNumber: cardNumber.replace(/\d(?=\d{4})/g, '*'), // Mask card number
                    expiryDate: expiryDate,
                    cardName: cardName
                };
            } else if (selectedPaymentMethod === 'Others') {
                const paymentType = document.getElementById('payment_type').value;
                if (!paymentType) {
                    Swal.fire('Error', 'Please select a payment type', 'error');
                    return;
                }
                
                paymentDetails = {
                    type: 'Others',
                    subType: paymentType
                };
                
                // Add specific details based on payment type
                if (paymentType === 'Credit Card') {
                    const ccNumber = document.getElementById('cc_number').value;
                    const ccExpiry = document.getElementById('cc_expiry').value;
                    const ccCvv = document.getElementById('cc_cvv').value;
                    const ccName = document.getElementById('cc_name').value;
                    
                    if (!ccNumber || !ccExpiry || !ccCvv || !ccName) {
                        Swal.fire('Error', 'Please fill in all credit card details', 'error');
                        return;
                    }
                    
                    paymentDetails.cardNumber = ccNumber.replace(/\d(?=\d{4})/g, '*');
                    paymentDetails.expiryDate = ccExpiry;
                    paymentDetails.cardName = ccName;
                } else if (paymentType === 'Online Banking') {
                    const bankName = document.getElementById('bank_name').value;
                    const accountNumber = document.getElementById('account_number').value;
                    
                    if (!bankName || !accountNumber) {
                        Swal.fire('Error', 'Please fill in all banking details', 'error');
                        return;
                    }
                    
                    paymentDetails.bankName = bankName;
                    paymentDetails.accountNumber = accountNumber.replace(/\d(?=\d{4})/g, '*');
                } else if (paymentType === 'Digital Wallet') {
                    const walletType = document.getElementById('wallet_type').value;
                    const walletNumber = document.getElementById('wallet_number').value;
                    
                    if (!walletType || !walletNumber) {
                        Swal.fire('Error', 'Please fill in all wallet details', 'error');
                        return;
                    }
                    
                    paymentDetails.walletType = walletType;
                    paymentDetails.walletNumber = walletNumber;
                } else if (paymentType === 'Bank Transfer') {
                    const transferBank = document.getElementById('transfer_bank').value;
                    
                    if (!transferBank) {
                        Swal.fire('Error', 'Please select a bank', 'error');
                        return;
                    }
                    
                    paymentDetails.transferBank = transferBank;
                }
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            modal.hide();
            
            // Update payment mode display
            document.getElementById('payment_mode').value = selectedPaymentMethod;
        }

        function updateQuantity(input) {
            const quantity = parseInt(input.value);
            if (quantity < 1) {
                input.value = 1;
                return;
            }
            // Update URL parameter
            const url = new URL(window.location);
            url.searchParams.set('quantity', quantity);
            window.history.replaceState(null, null, url);
            // Recalculate totals
            location.reload();
        }

        async function confirmOrder() {
            const paymentMode = selectedPaymentMethod || document.getElementById('payment_mode').value;
            const location = document.getElementById('location').value.trim();

            if (!paymentMode) {
                Swal.fire('Error', 'Please select and configure a payment method', 'error');
                return;
            }

            if (!location) {
                Swal.fire('Error', 'Please set a delivery address', 'error');
                return;
            }

            const result = await Swal.fire({
                title: 'Confirm Order',
                text: 'Are you sure you want to place this order?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c97e2a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, place order!'
            });

            if (result.isConfirmed) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'place_order');
                    formData.append('payment_mode', paymentMode);
                    formData.append('payment_details', JSON.stringify(paymentDetails));
                    formData.append('location', location);
                    <?php if ($is_single_product): ?>
                    formData.append('product_id', '<?= $product_id ?>');
                    formData.append('quantity', document.querySelector('.quantity-input').value);
                    <?php endif; ?>

                    const response = await fetch('cart-handler.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        await Swal.fire({
                            title: 'Order Placed!',
                            text: `Your order #${data.order_number} has been placed successfully!`,
                            icon: 'success',
                            confirmButtonColor: '#c97e2a'
                        });

                        await Swal.fire({
                            title: 'Out for Delivery',
                            text: 'Great news! Your order is now out for delivery and will arrive soon.',
                            icon: 'info',
                            confirmButtonText: 'Track My Order',
                            cancelButtonText: 'Continue Shopping',
                            showCancelButton: true,
                            confirmButtonColor: '#c97e2a',
                            cancelButtonColor: '#6c757d'
                        }).then((deliveryResult) => {
                            if (deliveryResult.isConfirmed) {
                                window.location.href = 'myorders.php';
                            } else {
                                window.location.href = 'home.php';
                            }
                        });
                    } else {
                        Swal.fire('Error', data.error || 'Failed to place order', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An error occurred while placing the order', 'error');
                }
            }
        }
    </script>
</body>
</html>