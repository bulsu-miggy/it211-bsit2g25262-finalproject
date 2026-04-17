<?php
/**
 * UniMerch Admin — Dashboard Home
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
  <title>Dashboard — UniMerch Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/dashboard.css" rel="stylesheet">
</head>
<body>

<div class="admin-wrapper">
  <!-- Sidebar -->
  <?php include __DIR__ . '/_sidebar.php'; ?>
  


  <!-- Main Content -->
  <div class="admin-content">
    <div class="admin-topbar">
      <div class="admin-topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar"><i class="bi bi-list"></i></button>
        <div class="admin-topbar-mobile-logo">Uni<span>Merch</span></div>
        <div class="ms-3">
          <h1 class="admin-page-title">Dashboard</h1>
          <span class="admin-breadcrumb">Welcome back, <?= sanitize($merchant['full_name']) ?></span>
        </div>
      </div>
      <div class="admin-topbar-right">
        <a href="<?= BASE_URL ?>/" target="_blank" class="btn btn-ghost btn-sm">
          <i class="bi bi-shop me-1"></i>View Store
        </a>
      </div>
    </div>

    <!-- Dashboard Body -->
    <div class="admin-body">
      <!-- KPI Cards -->
      <div class="kpi-grid" id="kpiGrid">
        <div class="kpi-card revenue">
          <div class="kpi-card-header">
            <span>Total Revenue</span>
            <div class="kpi-card-icon"><i class="bi bi-currency-dollar"></i></div>
          </div>
          <div class="kpi-card-value" id="kpiRevenue">
            <div class="skeleton" style="height:28px; width:120px;"></div>
          </div>
        </div>
        <div class="kpi-card orders">
          <div class="kpi-card-header">
            <span>Today's Orders</span>
            <div class="kpi-card-icon"><i class="bi bi-bag-check"></i></div>
          </div>
          <div class="kpi-card-value" id="kpiOrders">
            <div class="skeleton" style="height:28px; width:60px;"></div>
          </div>
        </div>
        <div class="kpi-card products">
          <div class="kpi-card-header">
            <span>Active Products</span>
            <div class="kpi-card-icon"><i class="bi bi-box-seam"></i></div>
          </div>
          <div class="kpi-card-value" id="kpiProducts">
            <div class="skeleton" style="height:28px; width:60px;"></div>
          </div>
        </div>
        <div class="kpi-card alerts">
          <div class="kpi-card-header">
            <span>Low Stock</span>
            <div class="kpi-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
          </div>
          <div class="kpi-card-value" id="kpiLowStock">
            <div class="skeleton" style="height:28px; width:40px;"></div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="dashboard-grid">
        <div class="admin-panel">
          <div class="admin-panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="admin-panel-title m-0"><i class="bi bi-graph-up me-2"></i>Revenue Trend</h5>
            <select class="form-select form-select-sm border-0 bg-light shadow-none" style="width: auto; font-size: 0.75rem; min-width: 120px;" id="revenueRange">
              <option value="7">7 Days</option>
              <option value="30" selected>30 Days</option>
              <option value="90">90 Days</option>
            </select>
          </div>
          <div class="admin-panel-body">
            <div class="chart-container">
              <canvas id="revenueChart"></canvas>
            </div>
          </div>
        </div>

        <div class="admin-panel">
          <div class="admin-panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="admin-panel-title m-0"><i class="bi bi-trophy me-2"></i>Top Products</h5>
          </div>
          <div class="admin-panel-body">
            <div class="chart-container">
              <canvas id="topProductsChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Orders Table -->
      <div class="admin-panel">
        <div class="admin-panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h5 class="admin-panel-title m-0"><i class="bi bi-clock-history me-2"></i>Recent Orders</h5>
          <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline-primary btn-sm px-3">View All</a>
        </div>
        <div class="admin-panel-body no-pad">
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th class="text-start">Order #</th>
                  <th class="text-center">Customer</th>
                  <th class="text-center">Items</th>
                  <th class="text-center">Total</th>
                  <th class="text-center">Payment</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Date</th>
                </tr>
              </thead>
              <tbody id="recentOrdersBody">
                <tr><td colspan="7" class="text-center py-4"><div class="spinner mx-auto"></div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script src="<?= BASE_URL ?>/assets/js/dashboard.js"></script>
</body>
</html>
