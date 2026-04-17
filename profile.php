<?php
/**
 * UniMerch — Customer Profile
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

requireCustomerAuth();
$customer = getCustomer();

// Fetch customer orders
$orderStmt = db()->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE o.customer_id = ? 
    ORDER BY o.created_at DESC
");
$orderStmt->execute([$customer['id']]);
$orders = $orderStmt->fetchAll();

$activeTab = $_GET['tab'] ?? 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile — UniMerch</title>
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
        <a href="<?= BASE_URL ?>/cart.php" class="btn btn-ghost cart-link" id="navCartBtn">
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
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
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

  <section class="profile-section">
    <div class="container" style="max-width: 900px;">
      
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="d-flex align-items-center gap-3">
          <div class="profile-avatar">
            <?= strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)) ?>
          </div>
          <div>
            <h4 class="mb-0"><?= sanitize($customer['first_name'] . ' ' . $customer['last_name']) ?></h4>
            <p class="mb-0" style="color: rgba(255,255,255,0.6); font-size:0.9rem;">
              <i class="bi bi-envelope me-1"></i><?= sanitize($customer['email']) ?>
            </p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <ul class="nav profile-tabs mb-4" id="profileTabs">
        <li class="nav-item">
          <a class="nav-link <?= $activeTab === 'profile' ? 'active' : '' ?>" href="#profileTab" data-bs-toggle="tab">
            <i class="bi bi-person me-1"></i>Profile
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $activeTab === 'orders' ? 'active' : '' ?>" href="#ordersTab" data-bs-toggle="tab">
            <i class="bi bi-bag-check me-1"></i>My Orders
            <?php if (count($orders) > 0): ?>
              <span class="badge bg-primary ms-1"><?= count($orders) ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Profile Tab -->
        <div class="tab-pane fade <?= $activeTab === 'profile' ? 'show active' : '' ?>" id="profileTab">
          <div class="checkout-form-card">
            <h5><i class="bi bi-pencil-square text-primary"></i> Edit Profile</h5>
            <form id="profileForm">
              <div class="row g-3">
                <div class="col-6">
                  <label class="form-label fw-semibold">First Name</label>
                  <input type="text" class="form-control" id="profileFirstName" 
                         value="<?= sanitize($customer['first_name']) ?>" required>
                </div>
                <div class="col-6">
                  <label class="form-label fw-semibold">Last Name</label>
                  <input type="text" class="form-control" id="profileLastName" 
                         value="<?= sanitize($customer['last_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email</label>
                  <input type="email" class="form-control" value="<?= sanitize($customer['email']) ?>" disabled>
                  <small class="text-muted">Email cannot be changed</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Phone</label>
                  <input type="tel" class="form-control" id="profilePhone" 
                         value="<?= sanitize($customer['phone']) ?>">
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary-gradient">
                    <i class="bi bi-check-lg me-2"></i>Save Changes
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Orders Tab -->
        <div class="tab-pane fade <?= $activeTab === 'orders' ? 'show active' : '' ?>" id="ordersTab">
          <?php if (empty($orders)): ?>
            <div class="text-center py-5">
              <i class="bi bi-bag-x" style="font-size:3rem; color:var(--gray-300);"></i>
              <h4 class="mt-3" style="color:var(--gray-600);">No orders yet</h4>
              <p class="text-muted">Start shopping to see your orders here.</p>
              <a href="<?= BASE_URL ?>/" class="btn btn-primary-gradient">Browse Products</a>
            </div>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <div class="checkout-form-card mb-3 order-card" data-order-id="<?= $order['id'] ?>">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <h6 class="mb-0" style="font-family:var(--font-heading); font-weight:700; color:var(--primary-700);">
                    <?= sanitize($order['order_number']) ?>
                  </h6>
                  <small class="text-muted">
                    <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?> · <?= $order['item_count'] ?> item(s)
                  </small>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge-status badge-<?= $order['status'] ?> order-status-badge" data-current-status="<?= $order['status'] ?>">
                    <?= ucfirst($order['status']) ?>
                  </span>
                  <span class="fw-bold"><?= formatPrice($order['total_amount']) ?></span>
                </div>
                <button class="btn btn-ghost btn-sm" onclick="viewCustomerOrderDetail(<?= $order['id'] ?>)">
                  <i class="bi bi-eye me-1"></i>View
                </button>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Order Detail Modal -->
  <div class="modal fade admin-modal" id="customerOrderModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Order Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="customerOrderBody">
          <!-- Loaded via AJAX -->
          <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>const BASE_URL = '<?= BASE_URL ?>';</script>
  <script src="<?= BASE_URL ?>/assets/js/common.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/storefront.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/cart.js"></script>

  <script>
  // Profile form submission
  $('#profileForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: BASE_URL + '/api/auth.php',
      method: 'PUT',
      contentType: 'application/json',
      data: JSON.stringify({
        action: 'update_profile',
        first_name: $('#profileFirstName').val(),
        last_name: $('#profileLastName').val(),
        phone: $('#profilePhone').val()
      }),
      success: function(res) {
        if (res.success) {
          showToast('Profile updated successfully!', 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          showToast(res.message || 'Update failed', 'error');
        }
      }
    });
  });

  // View Order Detail
  function viewCustomerOrderDetail(id) {
    $('#customerOrderBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
    const modal = new bootstrap.Modal('#customerOrderModal');
    modal.show();

    $.get(BASE_URL + '/api/orders.php', { id: id }, function(res) {
      if (!res.success) {
        showToast(res.message, 'error');
        modal.hide();
        return;
      }

      const order = res.data;
      const date = new Date(order.created_at).toLocaleDateString('en-PH', { 
        month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' 
      });

      let itemsHtml = '';
      order.items.forEach(item => {
        itemsHtml += `
          <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
            <img src="${item.image_url}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
            <div class="flex-1">
              <h6 class="mb-0 fw-bold">${item.product_name}</h6>
              <small class="text-muted">${item.category_code} · Qty: ${item.quantity} ${item.size ? '· Size: '+item.size : ''}</small>
            </div>
            <div class="fw-bold">₱${parseFloat(item.price * item.quantity).toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
          </div>
        `;
      });

      $('#customerOrderBody').html(`
        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h4 class="fw-bold text-primary mb-1">${order.order_number}</h4>
              <p class="text-muted small">${date}</p>
            </div>
            <span class="badge-status badge-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
          </div>
        </div>

        <div class="order-detail-meta mb-4 p-3 bg-light rounded-3">
          <div class="row g-3">
            <div class="col-6">
              <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.65rem;">Payment Method</small>
              <span class="text-capitalize">${order.payment_method.replace('_', ' ')}</span>
            </div>
            <div class="col-6">
              <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.65rem;">Payment Status</small>
              <span class="badge-status badge-${order.payment_status === 'paid' ? 'paid' : (order.payment_status === 'unpaid' ? 'unpaid' : 'pending')}">${order.payment_status.replace('_', ' ')}</span>
            </div>
          </div>
        </div>

        <div class="mb-4">
          <h6 class="fw-bold mb-3">Order Items</h6>
          ${itemsHtml}
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <h5 class="fw-bold mb-0">Total</h5>
          <h4 class="fw-bold text-primary mb-0">₱${parseFloat(order.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</h4>
        </div>

        ${order.notes ? `
          <div class="mt-4 pt-3 border-top">
            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size:0.65rem;">Notes</small>
            <p class="mb-0 small">${order.notes}</p>
          </div>
        ` : ''}
      `);
    });
  }

  // Task 2: Customer-Side Polling (The Subscriber)
  function syncOrderStatus() {
    const orderCards = $('.order-card');
    if (orderCards.length === 0) return;

    let orderIds = [];
    orderCards.each(function() {
      orderIds.push($(this).data('order-id'));
    });

    $.post(BASE_URL + '/api/get_order_sync.php', {
      order_ids: orderIds
    }, function(res) {
      if (res.success && res.data) {
        res.data.forEach(function(update) {
          const card = $(`.order-card[data-order-id="${update.id}"]`);
          const badge = card.find('.order-status-badge');
          const currentStatus = badge.data('current-status');

          if (currentStatus !== update.status) {
            // Fade out, update content & class, fade back in
            badge.fadeOut(200, function() {
              badge.removeClass(`badge-${currentStatus}`)
                   .addClass(`badge-${update.status}`)
                   .data('current-status', update.status)
                   .text(update.status.charAt(0).toUpperCase() + update.status.slice(1))
                   .fadeIn(300);
            });
          }
        });
      }
    });
  }

  // Poll every 10 seconds
  setInterval(syncOrderStatus, 10000);
  </script>
</body>
</html>
