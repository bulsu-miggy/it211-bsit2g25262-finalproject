<?php
/**
 * UniMerch — Cart Page
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
  <title>Shopping Cart — UniMerch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/storefront.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="um-navbar navbar navbar-expand-lg scrolled">
    <div class="container-fluid px-md-5">
      <a class="navbar-brand" href="<?= BASE_URL ?>/">
        <i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span>
      </a>
      
      <div class="d-flex align-items-center gap-2 order-lg-last">
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

  <!-- Cart Section -->
  <section class="cart-section flex-grow-1">
    <div class="container">
      <div class="mb-4">
        <h1 class="cart-page-title"><i class="bi bi-bag me-2"></i>Shopping Cart</h1>
        <p class="text-muted" id="cartItemCount">Loading...</p>
      </div>

      <div class="row g-4">
        <!-- Cart Items -->
        <div class="col-lg-8">
          <div id="cartItems">
            <!-- Loading skeleton -->
            <div class="placeholder-glow">
              <div class="cart-item">
                <div class="skeleton" style="width:90px; height:90px; border-radius:12px;"></div>
                <div class="flex-grow-1">
                  <div class="skeleton mb-2" style="height:18px; width:60%;"></div>
                  <div class="skeleton" style="height:14px; width:30%;"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty Cart -->
          <div class="cart-empty" id="cartEmpty" style="display:none;">
            <i class="bi bi-bag-x"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added anything yet.</p>
            <div class="mt-2">
              <a href="<?= BASE_URL ?>/" class="btn btn-primary-gradient px-4">
                <i class="bi bi-arrow-left me-2"></i>Start Shopping
              </a>
            </div>
          </div>
        </div>

        <!-- Cart Summary -->
        <div class="col-lg-4" id="cartSummaryCol">
          <div class="cart-summary">
            <h5 class="cart-summary-title">Order Summary</h5>
            <div class="cart-summary-row">
              <span>Subtotal</span>
              <span id="cartSubtotal">₱0.00</span>
            </div>
            <div class="cart-summary-row">
              <span>Shipping</span>
              <span class="text-success">Free</span>
            </div>
            <div class="cart-summary-total">
              <span>Total</span>
              <span id="cartTotal">₱0.00</span>
            </div>
            <div class="d-grid mt-4">
              <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-accent btn-lg" id="checkoutBtn">
                <i class="bi bi-shield-check me-2"></i>Proceed to Checkout
              </a>
            </div>
            <p class="text-center text-muted mt-3" style="font-size:0.78rem;">
              <i class="bi bi-lock me-1"></i>Secure checkout · Free campus pickup
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="um-footer mt-auto">
    <div class="container-fluid px-md-5">
      <div class="footer-bottom" style="border:none; padding:0; margin:0;">
        <p class="mb-0">© <?= date('Y') ?> UniMerch × BulSU. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <div class="toast-container" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>const BASE_URL = '<?= BASE_URL ?>';</script>
  <script src="<?= BASE_URL ?>/assets/js/common.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/storefront.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/cart.js"></script>
</body>
</html>
