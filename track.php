<?php
require_once __DIR__ . '/db/action/dbconfig.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: loginpage.php');
    exit();
}

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function statusRank($status)
{
    $status = strtolower(trim((string)$status));
    if ($status === 'delivered' || $status === 'completed') {
        return 4;
    }
    if ($status === 'shipped') {
        return 3;
    }
    if ($status === 'paid' || $status === 'processing') {
        return 2;
    }
    if ($status === 'pending') {
        return 1;
    }
    return 0;
}

$stmt = $conn->prepare('SELECT l.id, l.username, p.address, p.postal_code FROM login l LEFT JOIN user_profiles p ON p.user_id = l.id WHERE l.username = :username LIMIT 1');
$stmt->execute([':username' => $_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: loginpage.php');
    exit();
}

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
    header('Location: cart/orderHistory.php?status_error=missing_order');
    exit();
}

$stmt = $conn->prepare('SELECT id, order_code, user_id, total_amount, status, created_at FROM orders WHERE id = :oid AND user_id = :uid LIMIT 1');
$stmt->execute([
    ':oid' => $orderId,
    ':uid' => (int)$user['id'],
]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: cart/orderHistory.php?status_error=not_found');
    exit();
}

$stmt = $conn->prepare('SELECT oi.quantity, oi.unit_price, oi.subtotal, p.name, p.size, p.color, p.image FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :oid');
$stmt->execute([':oid' => (int)$order['id']]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$productTotal = 0.00;
foreach ($orderItems as $item) {
    $lineSubtotal = isset($item['subtotal']) ? (float)$item['subtotal'] : ((float)$item['unit_price'] * (int)$item['quantity']);
    $productTotal += $lineSubtotal;
}

$status = trim((string)$order['status']);
$statusNorm = strtolower($status);
$rank = statusRank($statusNorm);

$buyerAddress = trim((string)($user['address'] ?? ''));
$buyerPostal = trim((string)($user['postal_code'] ?? ''));

$steps = [
    ['title' => 'Order Made', 'sub' => 'Create order', 'icon' => 'assets2/order.png', 'step' => 1],
    ['title' => 'Order Paid', 'sub' => 'Payment received', 'icon' => 'assets2/paid.png', 'step' => 2],
    ['title' => 'Shipped', 'sub' => 'On delivery', 'icon' => 'assets2/shipped.png', 'step' => 3],
    ['title' => 'Completed', 'sub' => 'Delivered successfully', 'icon' => 'assets2/completed.png', 'step' => 4],
];

function renderItemImage($rawImage)
{
    $img = trim((string)$rawImage);
    if ($img === '') {
        return 'assets2/adidasblablabla.png';
    }
  $img = str_replace('\\', '/', $img);
  if (preg_match('#^(https?:)?//#i', $img) || strpos($img, 'data:') === 0) {
    return $img;
  }
    if (strpos($img, 'assets2/') === 0 || strpos($img, 'images/') === 0) {
        return $img;
    }
  return 'images/products/' . basename($img);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Laces - Order Tracking</title>
  <link rel="icon" type="image/png" href="assets2/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/master.css">
  <link rel="stylesheet" href="css/track.css">
  <style>
    /* Track-specific dark mode hardening */
    html[data-theme='dark'] .white-container {
      background: #0f172a !important;
      border: 1px solid #334155 !important;
    }

    html[data-theme='dark'] .status-card,
    html[data-theme='dark'] .address-block,
    html[data-theme='dark'] .item-card,
    html[data-theme='dark'] .summary-box {
      background: #1e293b !important;
      border: 1px solid #334155 !important;
      box-shadow: none !important;
    }

    html[data-theme='dark'] .status-card.completed {
      background: #2f2b1d !important;
      border-color: #8b6f2b !important;
    }

    html[data-theme='dark'] .status-title,
    html[data-theme='dark'] .status-sub,
    html[data-theme='dark'] .address-label,
    html[data-theme='dark'] .address-detail,
    html[data-theme='dark'] .item-name,
    html[data-theme='dark'] .item-meta,
    html[data-theme='dark'] .item-price,
    html[data-theme='dark'] .summary-row span,
    html[data-theme='dark'] .total-row,
    html[data-theme='dark'] .timeline-label-large,
    html[data-theme='dark'] .timeline-date {
      color: #e5e7eb !important;
    }

    html[data-theme='dark'] .timeline-vertical::before,
    html[data-theme='dark'] .item-row.border-top,
    html[data-theme='dark'] .total-row {
      border-color: #334155 !important;
    }
  </style>
</head>
<body class="bg-white">
<nav class="navbar bg-white border-bottom py-3">
  <div class="container d-flex align-items-center">
    <a href="index.php" class="d-flex align-items-center text-decoration-none text-dark fw-bold fs-4 me-3">
      <img src="assets2/logo.png" alt="Laces" style="width:35px;" class="me-2">Laces
    </a>
    <form class="flex-grow-1 mx-3 d-flex justify-content-center" role="search">
      <div class="position-relative w-100" style="max-width: 900px;">
        <input class="form-control rounded-pill border-dark ps-3 pe-5" type="search" placeholder="Search...">
        <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
      </div>
    </form>
    <div class="d-flex align-items-center gap-3">
      <a href="cart/cart.php"><button class="btn p-0 border-0 bg-transparent" type="button"><img src="assets2/cart.png" width="20" alt="Cart"></button></a>
      <button class="btn p-0 border-0 bg-transparent" type="button"><img src="assets2/world.png" width="20" alt="Language"></button>
      <button class="btn p-0 border-0 bg-transparent" type="button"><img src="assets2/si--notifications-alt-2-fill.png" width="20" alt="Notifications"></button>
      <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileMenu"><img src="assets2/gg--profile.png" width="20" alt="Profile"></button>
    </div>
  </div>
</nav>

<div class="offcanvas offcanvas-end" tabindex="-1" id="profileMenu" aria-labelledby="profileMenuLabel">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title fw-bold" id="profileMenuLabel">My Account</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="text-center mb-4">
      <img src="assets2/gg--profile.png" width="70" class="mb-2 opacity-75" alt="Profile">
      <h6 class="fw-bold">Welcome back!</h6>
      <p class="small text-muted">Manage your orders and preferences</p>
    </div>
    <div class="list-group list-group-flush">
      <div class="list-group-item border-0 py-3 theme-toggle-item" data-theme-toggle-item>
        <label class="theme-toggle-label mb-0 form-check form-switch">
          <span class="theme-toggle-copy">
            <i class="bi bi-moon-stars me-3"></i> Dark mode
          </span>
          <input class="form-check-input theme-toggle-input" type="checkbox" role="switch" aria-label="Toggle dark mode">
        </label>
      </div>
      <a href="profilepage.php" class="list-group-item list-group-item-action border-0 py-3"><i class="bi bi-person-circle me-3"></i> View Profile</a>
      <a href="cart/orderHistory.php" class="list-group-item list-group-item-action border-0 py-3"><i class="bi bi-box-seam me-3"></i> My Orders</a>
      <a href="db/action/logout.php" class="list-group-item list-group-item-action border-0 py-3 text-danger"><i class="bi bi-box-arrow-right me-3"></i> Sign Out</a>
    </div>
  </div>
</div>

<main>
  <div class="container">
    <div class="order-id-line d-flex justify-content-between align-items-center gap-2 flex-wrap">
      <h1 class="order-id-text mb-0">Order ID: <?php echo escape($order['order_code']); ?></h1>
      <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2"><?php echo escape(ucfirst($statusNorm)); ?></span>
    </div>

    <div class="white-container">
      <div>
        <h1 class="order-class-text">Order Status</h1>
      </div>

      <div class="status-grid">
        <?php foreach ($steps as $step): ?>
        <div class="status-card <?php echo ($rank >= $step['step']) ? 'completed' : ''; ?>">
          <div class="status-icon">
            <img src="<?php echo escape($step['icon']); ?>" alt="<?php echo escape($step['title']); ?>">
          </div>
          <div class="status-title"><?php echo escape($step['title']); ?></div>
          <div class="status-sub"><?php echo escape($step['sub']); ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-4 mb-5">
        <div class="col-md-6">
          <div class="address-block">
            <div class="address-label">Shipping Address (Seller)</div>
            <p class="address-detail">Laces Warehouse<br>Malolos, Bulacan<br>3000 Philippines</p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="address-block">
            <div class="address-label">Shipping Address (Buyer)</div>
            <p class="address-detail">
              <?php if ($buyerAddress === '' && $buyerPostal === ''): ?>
              Address details not set yet.
              <?php else: ?>
              <?php if ($buyerAddress !== ''): ?>
              <?php echo nl2br(escape($buyerAddress)); ?><br>
              <?php endif; ?>
              <?php if ($buyerPostal !== ''): ?>
              <?php echo escape($buyerPostal); ?>
              <?php endif; ?>
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-5">
        <div class="col-md-7">
          <div class="item-card">
            <?php if (empty($orderItems)): ?>
            <div class="item-row">
              <div class="item-detail">
                <div>
                  <div class="item-name">No order items found.</div>
                </div>
              </div>
            </div>
            <?php else: ?>
            <?php foreach ($orderItems as $index => $item): ?>
            <div class="item-row <?php echo $index > 0 ? 'border-top' : ''; ?>">
              <div class="item-detail">
                <div class="item-img">
                  <img src="<?php echo escape(renderItemImage($item['image'] ?? '')); ?>" alt="Product" onerror="this.src='assets2/adidasblablabla.png'">
                </div>
                <div>
                  <div class="item-name"><?php echo escape($item['name'] ?? 'Product'); ?></div>
                  <div class="item-meta">Size: <?php echo escape($item['size'] ?? 'N/A'); ?> | Color: <?php echo escape($item['color'] ?? 'N/A'); ?></div>
                </div>
              </div>
              <span class="item-price"><?php echo (int)$item['quantity']; ?>x ₱<?php echo number_format((float)$item['unit_price'], 2); ?></span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-md-5">
          <div class="summary-box">
            <div class="summary-row">
              <span class="text-secondary">Product Price</span>
              <span>₱<?php echo number_format($productTotal, 2); ?></span>
            </div>
            <div class="summary-row">
              <span class="text-secondary">Shipping Cost Subtotal</span>
              <span>₱0.00</span>
            </div>
            <div class="summary-row">
              <span class="text-secondary">Shipping Discount</span>
              <span class="text-danger">- ₱0.00</span>
            </div>
            <hr class="my-2">
            <div class="total-row">
              <span>TOTAL</span>
              <span>₱<?php echo number_format((float)$order['total_amount'], 2); ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="timeline-vertical mt-5">
      <?php
        $timeline = [
          ['label' => 'Order Placed', 'step' => 1],
          ['label' => 'Payment Confirmed', 'step' => 2],
          ['label' => 'Shipped', 'step' => 3],
          ['label' => 'Delivered', 'step' => 4],
        ];
      ?>
      <?php foreach ($timeline as $t): ?>
      <div class="timeline-step">
        <div class="timeline-marker <?php echo ($rank >= $t['step']) ? 'completed' : ''; ?>"></div>
        <div class="timeline-content">
          <div class="timeline-label-large"><?php echo escape($t['label']); ?></div>
          <div class="timeline-date">
            <?php echo ($rank >= $t['step']) ? escape(date('m/d/Y • g:i A', strtotime($order['created_at']))) : 'Waiting for update'; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/user/root_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets2/js/master.js"></script>
</body>
</html>
