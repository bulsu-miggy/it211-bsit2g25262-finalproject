<?php
/**
 * UniMerch Admin — Logout Confirmation (Merchant)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';


// Ensure user is actually an admin and has pending orders
$db = db();
$stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['count'] == 0) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pending Actions — UniMerch Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css">
  <style>
    body {
      background: radial-gradient(circle at top right, #1e3a8a 0%, #020617 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--space-6);
      color: white;
    }
    .confirm-card {
      max-width: 500px;
      width: 100%;
      padding: var(--space-10);
      text-align: center;
      animation: slideInUp 0.5s var(--ease-out);
    }
    .warning-icon-wrapper {
      width: 90px;
      height: 90px;
      background: rgba(245, 158, 11, 0.15);
      color: var(--accent-400);
      border-radius: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      margin: 0 auto var(--space-8);
      border: 1px solid rgba(245, 158, 11, 0.3);
      box-shadow: var(--shadow-gold);
    }
    .alert-message {
      background: rgba(255, 255, 255, 0.05);
      border-radius: var(--radius-lg);
      padding: var(--space-4);
      margin-bottom: var(--space-8);
    }
    .btn-manage {
      min-height: 52px;
      font-size: 1.05rem;
    }
    .btn-finish {
      min-height: 52px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .btn-finish:hover {
      background: rgba(239, 68, 68, 0.1);
      border-color: var(--danger);
      color: var(--danger);
    }
  </style>
</head>
<body>

  <div class="confirm-card glass-card-dark">
    <div class="warning-icon-wrapper">
      <i class="bi bi-clipboard-data"></i>
    </div>
    
    <h1 class="h2 mb-2">Attention Required</h1>
    <p class="text-white-50 mb-8">Pending Merchant Responsibilities Identified</p>

    <div class="alert-message">
      <p class="mb-0">
        There are <strong class="text-accent"><?= $result['count'] ?> Pending Orders</strong> requiring attention. 
        Are you sure you want to finish your session?
      </p>
    </div>

    <div class="d-grid gap-3">
      <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-accent btn-manage d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-box-seam"></i> Manage Orders Now
      </a>
      <a href="<?= BASE_URL ?>/admin/logout.php?force=true" class="btn btn-finish text-white d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-power"></i> Finish Session Anyway
      </a>
    </div>

    <div class="mt-8">
      <a href="<?= BASE_URL ?>/admin/index.php" class="text-white-50 text-decoration-none small hover-white">
        <i class="bi bi-arrow-left me-1"></i> Return to Dashboard
      </a>
    </div>
  </div>

</body>
</html>
