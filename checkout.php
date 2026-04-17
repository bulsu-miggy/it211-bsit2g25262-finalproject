<?php
/**
 * UniMerch — Checkout Page
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$customer = getCustomer();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout — UniMerch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/storefront.css" rel="stylesheet">
</head>
<body>

  <!-- Navbar -->
  <nav class="um-navbar navbar navbar-expand-lg scrolled">
    <div class="container-fluid px-md-5">
      <a class="navbar-brand" href="<?= BASE_URL ?>/">
        <i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span>
      </a>
      
      <div class="d-flex align-items-center gap-2 order-lg-last">
        <a href="<?= BASE_URL ?>/cart.php" class="btn btn-ghost btn-sm cart-link" id="navCartBtn">
          <i class="bi bi-bag"></i>
          <span class="cart-badge" id="navCartBadge" style="display:none;">0</span>
        </a>
        <?php if ($customer): ?>
          <div class="dropdown">
            <button class="btn btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i>
              <span class="hide-mobile ms-1"><?= sanitize($customer['first_name']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php?tab=orders"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php" onclick="handleLogout(event, 'customer'); return false;"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login.php" class="btn btn-ghost">
            <i class="bi bi-person"></i>
            <span class="hide-mobile ms-1">Login</span>
          </a>
        <?php endif; ?>
      </div>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/#categories-section">Colleges</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Checkout -->
  <section class="checkout-section">
    <div class="container">
      <h1 class="cart-page-title mb-4"><i class="bi bi-shield-check me-2"></i>Checkout</h1>

      <div class="row g-4">
        <!-- Checkout Form -->
        <div class="col-lg-7">
          <!-- Customer Info -->
          <div class="checkout-form-card mb-4">
            <h5><i class="bi bi-person-vcard text-primary"></i> Contact Information</h5>
            <?php if (!$customer): ?>
              <div class="alert alert-info d-flex align-items-center gap-2" style="font-size:0.85rem; border-radius: var(--radius-lg);">
                <i class="bi bi-info-circle"></i>
                <span>Already have an account? <a href="<?= BASE_URL ?>/login.php">Log in</a> for a faster checkout.</span>
              </div>
            <?php endif; ?>
            <form id="checkoutForm">
              <div class="row g-3">
                <div class="col-6">
                  <label class="form-label fw-semibold">First Name *</label>
                  <input type="text" class="form-control" id="firstName" name="first_name" 
                         value="<?= $customer ? sanitize($customer['first_name']) : '' ?>" required>
                </div>
                <div class="col-6">
                  <label class="form-label fw-semibold">Last Name *</label>
                  <input type="text" class="form-control" id="lastName" name="last_name"
                         value="<?= $customer ? sanitize($customer['last_name']) : '' ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email *</label>
                  <input type="email" class="form-control" id="email" name="email"
                         value="<?= $customer ? sanitize($customer['email']) : '' ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Phone Number *</label>
                  <input type="tel" class="form-control" id="phone" name="phone" 
                         placeholder="09XX-XXX-XXXX"
                         value="<?= $customer ? sanitize($customer['phone']) : '' ?>" required>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Order Notes <span class="text-muted">(optional)</span></label>
                  <textarea class="form-control" id="notes" name="notes" rows="2" 
                            placeholder="e.g., Pickup after 3PM, size notes..."></textarea>
                </div>
              </div>
            </form>
          </div>

          <!-- Payment Method -->
          <div class="checkout-form-card">
            <h5><i class="bi bi-credit-card text-primary"></i> Payment Method</h5>
            <div class="row g-3" id="paymentMethods">
              <div class="col-4">
                <div class="payment-method-card selected" data-method="cash">
                  <i class="bi bi-cash-stack d-block"></i>
                  <h6>Cash</h6>
                  <p>Pay on pickup</p>
                </div>
              </div>
              <div class="col-4">
                <div class="payment-method-card" data-method="gcash">
                  <i class="bi bi-phone d-block"></i>
                  <h6>GCash</h6>
                  <p>Upload screenshot</p>
                </div>
              </div>
              <div class="col-4">
                <div class="payment-method-card" data-method="bank_transfer">
                  <i class="bi bi-bank d-block"></i>
                  <h6>Bank Transfer</h6>
                  <p>Upload receipt</p>
                </div>
              </div>
            </div>

            <!-- Payment Proof Upload (shown for gcash/bank) -->
            <div id="paymentProofSection" style="display:none;" class="mt-4">
              <label class="form-label fw-semibold">Upload Payment Proof *</label>
              <div class="image-preview-zone" id="paymentProofZone" onclick="document.getElementById('paymentProof').click()">
                <i class="bi bi-cloud-arrow-up" style="font-size:2rem; color:var(--gray-400);"></i>
                <p class="text-muted mb-0 mt-2" style="font-size:0.85rem;">Click to upload screenshot / receipt</p>
              </div>
              <input type="file" id="paymentProof" accept="image/*" style="display:none;">
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-5">
          <div class="cart-summary">
            <h5 class="cart-summary-title">Order Summary</h5>
            <div id="checkoutItems">
              <!-- Loaded via JS -->
            </div>
            <div class="cart-summary-row mt-3">
              <span>Subtotal</span>
              <span id="checkoutSubtotal">₱0.00</span>
            </div>
            <div class="cart-summary-row">
              <span>Shipping</span>
              <span class="text-success">Free Pickup</span>
            </div>
            <div class="cart-summary-total">
              <span>Total</span>
              <span id="checkoutTotal">₱0.00</span>
            </div>
            <div class="d-grid mt-4">
              <button class="btn btn-accent btn-lg" id="placeOrderBtn" disabled>
                <i class="bi bi-check-circle me-2"></i>Place Order
              </button>
            </div>
            <div class="text-center mt-3">
              <small class="text-muted">
                <i class="bi bi-shield-lock me-1"></i>
                Your information is secure and encrypted
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="toast-container" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>const BASE_URL = '<?= BASE_URL ?>';</script>
  <script src="<?= BASE_URL ?>/assets/js/common.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/storefront.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/cart.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/checkout.js"></script>
</body>
</html>
