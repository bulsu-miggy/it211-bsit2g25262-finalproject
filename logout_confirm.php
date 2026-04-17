<?php
/**
 * UniMerch — Logout Confirmation (Customer)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';


// Ensure user is actually logged in or has a cart
$db = db();
$sessionId = session_id();
$customerId = $_SESSION['customer_id'] ?? null;

// Query both session_id and customer_id
$query = "SELECT COUNT(*) as count FROM cart WHERE session_id = :sid";
$params = [':sid' => $sessionId];
if ($customerId) {
    $query .= " OR customer_id = :cid";
    $params[':cid'] = $customerId;
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// If no items, no need for confirmation
if ($result['count'] == 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Active Cart Identified — UniMerch</title>
  <!-- UI Dependencies -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- UniMerch Design System -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/storefront.css">
  <style>
    body {
      background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-100) 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--space-6);
    }

    .confirm-card {
      max-width: 480px;
      width: 100%;
      padding: var(--space-10);
      text-align: center;
      animation: slideInUp 0.6s var(--ease-out);
    }

    .warning-icon-wrapper {
      width: 90px;
      height: 90px;
      background: var(--primary-100);
      color: var(--primary-600);
      border-radius: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      margin: 0 auto var(--space-8);
      border: 1px solid rgba(37, 99, 235, 0.1);
      box-shadow: var(--shadow-glow);
    }

    .alert-message {
      background: rgba(255, 255, 255, 0.5);
      border-radius: var(--radius-lg);
      padding: var(--space-4);
      margin-bottom: var(--space-8);
      border: 1px solid var(--white);
    }

    .btn-checkout {
      min-height: 52px; /* Compliance: >44px touch target */
      font-size: 1.05rem;
    }

    .btn-logout {
      min-height: 52px; /* Compliance: >44px touch target */
      border: 1px solid var(--gray-300);
      color: var(--gray-500);
    }

    .btn-logout:hover {
      background: rgba(239, 68, 68, 0.05);
      border-color: var(--danger);
      color: var(--danger);
    }

    .cart-count-accent {
      color: var(--primary-700);
      font-weight: 800;
    }
  </style>
</head>
<body>

  <div class="confirm-card glass-card">
    <div class="warning-icon-wrapper">
      <i class="bi bi-cart-x"></i>
    </div>
    
    <h1 class="h2 mb-2">Active Cart Identified</h1>
    <p class="text-muted mb-8">Pending Customer Actions Identified</p>

    <div class="alert-message">
      <p class="mb-0">
        You still have <span class="cart-count-accent"><?= $result['count'] ?> item<?= $result['count'] > 1 ? 's' : '' ?></span> in your cart. 
        Would you like to check out before logging out?
      </p>
    </div>

    <div class="d-grid gap-3">
      <a href="<?= BASE_URL ?>/cart.php" class="btn btn-primary-gradient btn-checkout d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-bag-check"></i> Proceed to Checkout
      </a>
      <a href="<?= BASE_URL ?>/logout.php?force=true" class="btn btn-ghost btn-logout d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-box-arrow-right"></i> Logout Anyway
      </a>
    </div>

    <div class="mt-8">
      <a href="<?= BASE_URL ?>/index.php" class="text-muted text-decoration-none small hover-primary">
        <i class="bi bi-arrow-left me-1"></i> Continue Shopping
      </a>
    </div>
  </div>

</body>
</html>
