<?php
/**
 * UniMerch Admin — Orders Management
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

requireMerchantAuth();
$merchant = getMerchant();
$pendingOrders = db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders — UniMerch Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/dashboard.css" rel="stylesheet">
</head>

<body>

  <div class="admin-wrapper">
    <?php include __DIR__ . '/_sidebar.php'; ?>


    <div class="admin-content">
      <div class="admin-topbar">
        <div class="admin-topbar-left">
          <button class="sidebar-toggle" id="sidebarToggle" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar"><i class="bi bi-list"></i></button>
          <div class="admin-topbar-mobile-logo">Uni<span>Merch</span></div>
          <div class="ms-3">
            <h1 class="admin-page-title">Orders</h1>
          </div>
        </div>
        <div class="admin-topbar-right">
          <a href="<?= BASE_URL ?>/api/admin/export_orders.php" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-pdf me-1"></i>Export PDF
          </a>
        </div>
      </div>

      <div class="admin-body">
        <!-- Unified Filter Panel -->
        <div class="admin-panel mb-3 shadow-none border-bottom">
          <div class="admin-panel-body py-2 px-3">
            <div class="row g-2 align-items-center">
              <div class="col-lg-4 col-md-5">
                <div class="input-group input-group-sm rounded-pill overflow-hidden border">
                  <span class="input-group-text border-0 bg-white"><i class="bi bi-search text-muted small"></i></span>
                  <input type="text" class="form-control border-0 ps-0" id="orderSearch" placeholder="Search orders..." style="font-size: 0.8rem;">
                </div>
              </div>
              <div class="col-lg-8 col-md-7">
                <!-- Mobile Dropdown Filter -->
                <div class="d-md-none">
                  <div class="input-group input-group-sm rounded border overflow-hidden">
                    <span class="input-group-text border-0 bg-white"><i class="bi bi-filter text-muted"></i></span>
                    <select class="form-select border-0 ps-0" id="mobileStatusFilter">
                      <option value="all">Filter by Status: All</option>
                      <option value="pending">Pending</option>
                      <option value="confirmed">Confirmed</option>
                      <option value="processing">Processing</option>
                      <option value="ready">Ready</option>
                      <option value="completed">Completed</option>
                      <option value="cancelled">Cancelled</option>
                    </select>
                  </div>
                </div>

                <!-- Desktop Horizontal Tabs (Unified Row) -->
                <div class="status-tabs border-0 p-0 bg-transparent justify-content-start d-none d-md-flex">
                  <button class="status-tab active px-3 py-1" data-status="all">All</button>
                  <button class="status-tab px-3 py-1" data-status="pending">Pending</button>
                  <button class="status-tab px-3 py-1" data-status="confirmed">Confirmed</button>
                  <button class="status-tab px-3 py-1" data-status="processing">Processing</button>
                  <button class="status-tab px-3 py-1" data-status="ready">Ready</button>
                  <button class="status-tab px-3 py-1" data-status="completed">Completed</button>
                  <button class="status-tab px-3 py-1" data-status="cancelled">Cancelled</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Orders Table -->
        <div class="admin-panel">
          <div class="admin-panel-body no-pad">
            <div class="table-responsive">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th class="text-start" style="width: 25%;">Order #</th>
                    <th class="text-center">Customer</th>
                    <th class="text-center">Items</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Payment</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody id="ordersTableBody">
                  <tr>
                    <td colspan="8" class="text-center py-4">
                      <div class="spinner mx-auto"></div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Detail Modal -->
  <div class="modal fade admin-modal" id="orderDetailModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Order Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="orderDetailBody">
          <div class="text-center py-4">
            <div class="spinner mx-auto"></div>
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
  <script src="<?= BASE_URL ?>/assets/js/dashboard.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/orders-admin.js"></script>
</body>

</html>