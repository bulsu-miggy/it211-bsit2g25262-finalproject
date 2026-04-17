<?php 
session_start();
require_once 'db/connection.php'; 

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

if (!$is_logged_in) {
    header("Location: login.php");
    exit();
}

include $is_logged_in ? 'includes/member_header.php' : 'guest_header/guest_header.php';

$saved_addresses_stmt = $conn->prepare(
    "SELECT address_id, label, full_name, street_address, city, zip_code, phone_number, is_default
     FROM user_addresses
     WHERE user_id = ?
     ORDER BY is_default DESC, address_id DESC"
);
$saved_addresses_stmt->execute([$user_id]);
$saved_addresses = $saved_addresses_stmt->fetchAll(PDO::FETCH_ASSOC);

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

if (isset($_GET['cancel']) && $_GET['cancel'] === '1') {
    unset($_SESSION['checkout_shipping'], $_SESSION['checkout_payment']);
    header('Location: basket.php');
    exit();
}

if (isset($_GET['cancel_order']) && $_GET['cancel_order'] === '1' && isset($_GET['order_id'])) {
    $cancel_id = (int) $_GET['order_id'];
    try {
        $conn->beginTransaction();

        $items_stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $items_stmt->execute([$cancel_id]);
        $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

        $cancel_stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND user_id = ? AND status = 'Pending'");
        $cancel_stmt->execute([$cancel_id, $user_id]);

        if ($cancel_stmt->rowCount() > 0) {
            $stock_update = $conn->prepare("UPDATE candles SET stock_qty = stock_qty + ? WHERE product_id = ?");
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $stock_update->execute([(int)$item['quantity'], (int)$item['product_id']]);
                }
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
    }

    unset($_SESSION['checkout_shipping'], $_SESSION['checkout_payment']);
    header('Location: basket.php');
    exit();
}

if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') { $_SESSION['checkout_shipping'] = $_POST; }
if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') { $_SESSION['checkout_payment'] = $_POST; }

$subtotal = 0;
$cart_items = $_SESSION['cart'] ?? []; 

if (empty($cart_items) && $step < 4) {
    $pk = "product_id";
    $check = $conn->query("SHOW COLUMNS FROM candles LIKE 'product_id'");
    if ($check->rowCount() == 0) $pk = "id";

    $basketHasSize = $conn->query("SHOW COLUMNS FROM basket LIKE 'size'")->rowCount() > 0;
    $sizeField = $basketHasSize ? ", b.size" : "";

    $stmt = $conn->prepare("
        SELECT b.quantity$sizeField, p.name, p.price, p.scent_notes 
        FROM basket b 
        JOIN candles p ON b.product_id = p.$pk
        WHERE b.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cart_items as &$item) {
        if (($item['size'] ?? 'Small') === 'Medium') {
            $item['price'] += 20;
        } elseif (($item['size'] ?? 'Small') === 'Large') {
            $item['price'] += 40;
        }
    }
    unset($item);
}

foreach ($cart_items as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
$grand_total = $subtotal; 
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var cancelButton = document.getElementById('cancel-checkout');
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                Swal.fire({
                    title: 'Cancel this order?',
                    text: 'This will return you to your basket and clear checkout progress.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#888',
                    confirmButtonText: 'Yes, cancel it'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = 'checkout.php?cancel=1';
                    }
                });
            });
        }

        var finalCancelButton = document.getElementById('final-cancel-order');
        if (finalCancelButton) {
            $(finalCancelButton).on('click', function() {
                var orderId = $(this).data('order-id');
                Swal.fire({
                    title: 'Cancel this order?',
                    text: 'This will cancel the pending order and return you to your basket.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#888',
                    confirmButtonText: 'Yes, cancel order'
                }).then(function(result) {
                    if (result.isConfirmed && orderId) {
                        window.location.href = 'checkout.php?cancel_order=1&order_id=' + encodeURIComponent(orderId);
                    }
                });
            });
        }

        var savedAddresses = <?= json_encode($saved_addresses, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var shippingForm = $('#shipping-form');

        function showSavedAddressPopup() {
            if (!savedAddresses.length || !shippingForm.length) {
                return;
            }

            Swal.fire({
                title: 'Use saved address?',
                text: 'Do you want to choose one of your saved addresses to auto-fill the shipping fields?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var addressHtml = savedAddresses.map(function(addr, index) {
                        var label = addr.label ? addr.label : 'Saved Address';
                        var defaultTag = addr.is_default ? ' <span style="font-weight:600; color:#8a6b42;">(Default)</span>' : '';
                        return '<label class="saved-address-option" data-index="' + index + '">' +
                            '<input type="radio" name="saved_address" value="' + index + '">' +
                            '<div class="saved-address-content">' +
                                '<div class="saved-address-label">' + label + defaultTag + '</div>' +
                                '<div class="saved-address-line">' + addr.full_name + '</div>' +
                                '<div class="saved-address-line">' + addr.street_address + '</div>' +
                                '<div class="saved-address-line">' + addr.city + ' • ' + addr.zip_code + '</div>' +
                                '<div class="saved-address-line">' + addr.phone_number + '</div>' +
                            '</div>' +
                        '</label>';
                    }).join('');

                    Swal.fire({
                        title: 'Choose a saved address',
                        html: '<div class="saved-address-popup">' + addressHtml + '</div>',
                        showCancelButton: true,
                        confirmButtonText: 'Use Address',
                        width: 640,
                        focusConfirm: false,
                        didOpen: function() {
                            $('.saved-address-option').on('click', function() {
                                $('.saved-address-option').removeClass('selected');
                                $(this).addClass('selected');
                                $(this).find('input[name="saved_address"]').prop('checked', true);
                            });
                        },
                        preConfirm: function() {
                            var selected = $('.saved-address-option input[name="saved_address"]:checked');
                            if (!selected.length) {
                                Swal.showValidationMessage('Please select one saved address.');
                                return false;
                            }
                            return savedAddresses[parseInt(selected.val(), 10)];
                        }
                    }).then(function(result) {
                        if (result.isConfirmed && result.value) {
                            shippingForm.find('input[name="full_name"]').val(result.value.full_name || '');
                            shippingForm.find('input[name="address"]').val(result.value.street_address || '');
                            shippingForm.find('input[name="city"]').val(result.value.city || '');
                            shippingForm.find('input[name="postal_code"]').val(result.value.zip_code || '');
                            shippingForm.find('input[name="phone"]').val(result.value.phone_number || '');
                        }
                    });
                }
            });
        }

        showSavedAddressPopup();

        $('#shipping-form').on('submit', function(e) {
            var missing = [];
            $(this).find('input[required]').each(function() {
                if (!$.trim($(this).val())) {
                    var label = $(this).closest('.form-group').find('label').text() || $(this).attr('name');
                    missing.push(label);
                }
            });

            if (missing.length) {
                e.preventDefault();
                Swal.fire({
                    title: 'Please complete the form',
                    html: 'Fill in: <strong>' + missing.join(', ') + '</strong>',
                    icon: 'error'
                });
            }
        });
    });
</script>

<div class="checkout-page-container">
    <div class="checkout-progress">
        <div class="progress-step <?= ($step >= 1) ? 'active' : '' ?>"><span class="step-circle">1</span> Shipping</div>
        <div class="progress-line"></div>
        <div class="progress-step <?= ($step >= 2) ? 'active' : '' ?>"><span class="step-circle">2</span> Payment</div>
        <div class="progress-line"></div>
        <div class="progress-step <?= ($step >= 3) ? 'active' : '' ?>"><span class="step-circle">3</span> Review</div>
    </div>

    <?php if ($step === 1): ?>
        <div class="checkout-box">
            <div class="step-header">
                <h1>1. Shipping Details</h1>
                <p>Where should we deliver your light?</p>
            </div>
            <form action="checkout.php?step=2" method="POST" id="shipping-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required value="<?= htmlspecialchars($_SESSION['checkout_shipping']['full_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" name="address" required value="<?= htmlspecialchars($_SESSION['checkout_shipping']['address'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" required value="<?= htmlspecialchars($_SESSION['checkout_shipping']['city'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code" required value="<?= htmlspecialchars($_SESSION['checkout_shipping']['postal_code'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" id="phone_input" required value="<?= htmlspecialchars($_SESSION['checkout_shipping']['phone'] ?? '') ?>">
                </div>
                <div class="checkout-footer">
                    <a href="basket.php" class="btn-back">Back to Basket</a>
                    <button type="submit" class="btn-continue">Continue Payment</button>
                </div>
            </form>
        </div>

    <?php elseif ($step === 2): ?>
        <div class="checkout-box">
            <div class="step-header">
                <h1>2. Payment Method</h1>
                <p>Choose how you'd like to complete your purchase</p>
            </div>
            <form action="checkout.php?step=3" method="POST" id="payment-form">
                <div class="payment-grid">
                    <div class="method-card" id="card-wallet">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><path d="M12 18h.01"></path></svg>
                        <h3>E-Wallet</h3>
                        <p id="wallet-subtitle">Gcash / PayMaya</p>
                        <input type="radio" name="method" value="ewallet" id="radio-wallet" class="hidden-radio">
                    </div>
                    <div class="method-card" id="card-cod">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle></svg>
                        <h3>COD</h3>
                        <p>Cash On Delivery</p>
                        <input type="radio" name="method" value="cod" id="radio-cod" class="hidden-radio">
                    </div>
                </div>
                <input type="hidden" name="wallet_provider" id="wallet_provider" value="">
                <div class="payment-info-box" id="payment-desc">Choose a payment method to continue.</div>
                
                <div class="checkout-footer">
                    <a href="checkout.php?step=1" class="btn-back">Back to Shipping</a>
                    <button type="submit" class="btn-continue">Review Order</button>
                </div>
            </form>
        </div>

    <?php elseif ($step === 3): ?>
        <div class="checkout-box review-box">
            <div class="step-header">
                <h1>3. Review Your Order</h1>
                <p>Please confirm all details before placing your order</p>
            </div>
            <div class="review-grid">
                <div class="review-left">
                <div class="review-section">
                    <a href="checkout.php?step=1" class="edit-link">Edit</a>
                    <h2>Shipping Address</h2>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($_SESSION['checkout_shipping']['full_name'] ?? 'N/A') ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($_SESSION['checkout_shipping']['address'] ?? '') ?></p>
                    <p><strong>City:</strong> <?= htmlspecialchars($_SESSION['checkout_shipping']['city'] ?? '') ?></p>
                    <p><strong>Postal Code:</strong> <?= htmlspecialchars($_SESSION['checkout_shipping']['postal_code'] ?? '') ?></p>
                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($_SESSION['checkout_shipping']['phone'] ?? '') ?></p>
                </div>
                
                <div class="review-section">
                    <a href="checkout.php?step=2" class="edit-link">Edit</a>
                    <h2>Payment Method</h2>
                    <p>
                        <?php 
                            $method = $_SESSION['checkout_payment']['method'] ?? '';
                            echo ($method === 'ewallet') ? "E-Wallet (" . htmlspecialchars($_SESSION['checkout_payment']['wallet_provider'] ?? '') . ")" : "Cash on Delivery";
                        ?>
                    </p>
                </div>

                <div class="review-section">
                    <h2>Order Items</h2>
                    <?php if (empty($cart_items)): ?>
                        <p class="text-muted">No items found in your basket.</p>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="review-item">
                                <div>
                                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                                    <p><?= htmlspecialchars($item['scent_notes'] ?? 'Signature Scent') ?> | <?= htmlspecialchars($item['size'] ?? 'Standard') ?> | Qty: <?= $item['quantity'] ?></p>
                                </div>
                                <span>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="review-right">
                <div class="summary-card">
                    <h2>Payment Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₱<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span class="summary-note">FREE</span>
                    </div>
                    <div class="summary-total">
                        <div class="summary-row">
                            <span>TOTAL</span>
                            <span>₱<?= number_format($grand_total, 2) ?></span>
                        </div>
                    </div>
                    <form action="db/action/save_order.php" method="POST" id="place-order-form">
                        <button type="submit" class="btn-continue btn-full">Place Order</button>
                    </form>
                    <a href="checkout.php?step=2" class="btn-back btn-full btn-back-small">Back to Payment</a>
                </div>
            </div>
        </div>

    <?php elseif ($step === 4): ?>
        <?php 
            $final_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
            $order_number = 'SOLIS-XXXX';
            $order_status = 'Pending';
            $order_amount = 0;
            $customer_name = $_SESSION['full_name'] ?? '';

            if ($final_id) {
                $order_stmt = $conn->prepare(
                    "SELECT o.order_number, o.status, o.total_amount, l.full_name 
                     FROM orders o 
                     LEFT JOIN login l ON l.user_id = o.user_id 
                     WHERE o.order_id = ? AND o.user_id = ?"
                );
                $order_stmt->execute([$final_id, $user_id]);
                $order_data = $order_stmt->fetch(PDO::FETCH_ASSOC);
                if ($order_data) {
                    $order_number = htmlspecialchars($order_data['order_number'] ?? $order_number);
                    $order_status = htmlspecialchars($order_data['status'] ?? $order_status);
                    $order_amount = (float) ($order_data['total_amount'] ?? 0);
                    $customer_name = htmlspecialchars($order_data['full_name'] ?? $customer_name);
                }
            } elseif (isset($_GET['order_no'])) {
                $order_number = htmlspecialchars($_GET['order_no']);
            }
        ?>
        <div class="checkout-box success-container">
            <div class="success-icon">✓</div>
            <h1 class="success-heading">Your light is on its way.</h1>
            <p>Order <strong>#<?= $order_number ?></strong> confirmed.</p>
            <div class="success-panel">
                <div class="summary-row"><span>Customer</span><span><?= htmlspecialchars($customer_name ?: 'Guest') ?></span></div>
                <div class="summary-row"><span>Total Amount</span><span>₱<?= number_format($order_amount, 2) ?></span></div>
                <div class="summary-row"><span>Order Reference</span><span>#<?= $order_number ?></span></div>
                <div class="summary-row"><span>Order ID</span><span><?= $final_id ?></span></div>
                <div class="summary-row"><span>Estimated Delivery</span><span>5-7 Business Days</span></div>
                <div class="summary-row font-semibold"><span>Status</span><span class="order-status-note"><?= $order_status ?></span></div>
            </div>
            <div class="success-actions">
                <a href="index.php" class="btn-continue continue-shopping-button">Continue Shopping</a>
                <?php if ($order_status === 'Pending' && $final_id): ?>
                    <button type="button" id="final-cancel-order" data-order-id="<?= $final_id ?>" class="btn-cancel-order">Cancel Order</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="js/checkout_process.js"></script>

<?php 
$footer_file = $is_logged_in ? 'includes/member_footer.php' : 'guest_header/guestfooter.php';
include $footer_file;
?>