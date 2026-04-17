<?php
/**
 * UniMerch — Order Success Page
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$orderNumber = $_GET['order'] ?? '';
$order = null;
$orderItems = [];

if ($orderNumber) {
    $stmt = db()->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
    
    if ($order) {
        $itemStmt = db()->prepare("
            SELECT oi.*, p.name AS product_name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $itemStmt->execute([$order['id']]);
        $orderItems = $itemStmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Confirmed — UniMerch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/storefront.css" rel="stylesheet">
</head>
<body>

  <!-- Navbar -->
  <nav class="um-navbar navbar">
    <div class="container">
      <a class="navbar-brand" href="<?= BASE_URL ?>/">
        <i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span>
      </a>
    </div>
  </nav>

  <?php if ($order): ?>
  <section class="order-success-section">
    <div class="container" style="max-width: 600px;">
      <div class="success-icon">
        <i class="bi bi-check-lg"></i>
      </div>
      <h2 style="font-family: var(--font-heading); font-weight: 800;">Order Confirmed!</h2>
      <p class="text-muted mb-4">Thank you for your purchase. Your order has been placed successfully.</p>

      <div class="glass-card p-4 text-start mb-4" style="background: white;">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <small class="text-muted">Order Number</small>
            <h5 class="mb-0" style="font-family: var(--font-heading); font-weight: 700; color: var(--primary-700);">
              <?= sanitize($order['order_number']) ?>
            </h5>
          </div>
          <span class="badge-status badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
        </div>

        <hr>

        <?php foreach ($orderItems as $item): ?>
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="<?= BASE_URL ?>/uploads/<?= $item['image'] ?>" alt="" 
               style="width:50px; height:50px; border-radius:8px; object-fit:cover;"
               onerror="this.src='https://placehold.co/50x50/e2e8f0/64748b?text=IMG'">
          <div class="flex-grow-1">
            <div class="fw-semibold" style="font-size:0.9rem;"><?= sanitize($item['product_name']) ?></div>
            <small class="text-muted">
              Qty: <?= $item['quantity'] ?>
              <?= $item['size'] ? '· Size: ' . $item['size'] : '' ?>
            </small>
          </div>
          <div class="fw-bold"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
        </div>
        <?php endforeach; ?>

        <hr>

        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Payment Method</span>
          <span class="fw-semibold"><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="fw-bold" style="font-size:1.1rem;">Total</span>
          <span class="fw-bold" style="font-size:1.1rem; color:var(--primary-700);"><?= formatPrice($order['total_amount']) ?></span>
        </div>
      </div>

      <div class="d-flex gap-3 justify-content-center">
        <a href="<?= BASE_URL ?>/" class="btn btn-primary-gradient">
          <i class="bi bi-bag-heart me-2"></i>Continue Shopping
        </a>
        <?php if (isCustomerLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/profile.php?tab=orders" class="btn btn-ghost">
          <i class="bi bi-clock-history me-2"></i>View Orders
        </a>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php else: ?>
  <section class="order-success-section">
    <div class="container" style="max-width: 500px;">
      <i class="bi bi-exclamation-triangle" style="font-size:3rem; color:var(--warning);"></i>
      <h3 class="mt-3">Order not found</h3>
      <p class="text-muted">The order you're looking for doesn't exist or has been removed.</p>
      <a href="<?= BASE_URL ?>/" class="btn btn-primary-gradient">Go to Shop</a>
    </div>
  </section>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
