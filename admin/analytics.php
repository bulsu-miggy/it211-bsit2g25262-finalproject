<?php
/**
 * UniMerch Admin — Analytics
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
  <title>Analytics — UniMerch Admin</title>
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
          <h1 class="admin-page-title">Analytics</h1>
        </div>
      </div>
      <div class="admin-topbar-right">
        <select class="form-select form-select-sm" id="analyticsPeriod" style="width:auto;">
          <option value="7">Last 7 days</option>
          <option value="30" selected>Last 30 days</option>
          <option value="90">Last 90 days</option>
          <option value="365">This Year</option>
        </select>
      </div>
    </div>

    <div class="admin-body">
      <!-- Revenue Chart (Full Width) -->
      <div class="admin-panel mb-4">
        <div class="admin-panel-header">
          <h5 class="admin-panel-title"><i class="bi bi-graph-up-arrow me-2"></i>Revenue Over Time</h5>
        </div>
        <div class="admin-panel-body">
          <div class="chart-container tall">
            <canvas id="analyticsRevenueChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Distribution Insights Shared Panel -->
      <div class="admin-panel mb-4">
        <div class="admin-panel-header">
          <h5 class="admin-panel-title"><i class="bi bi-pie-chart me-2"></i>Distribution Insights</h5>
        </div>
        <div class="admin-panel-body">
          <div class="row g-4 text-center">
            <div class="col-md-6 border-md-end">
              <h6 class="text-muted small text-uppercase mb-3">Orders by Status</h6>
              <div class="chart-container" style="height: 300px;">
                <canvas id="orderStatusChart"></canvas>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted small text-uppercase mb-3">Sales by College</h6>
              <div class="chart-container" style="height: 300px;">
                <canvas id="categoryChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Products -->
      <div class="admin-panel mt-4">
        <div class="admin-panel-header">
          <h5 class="admin-panel-title"><i class="bi bi-trophy me-2"></i>Top Selling Products</h5>
        </div>
        <div class="admin-panel-body">
          <div class="chart-container tall">
            <canvas id="topProductsBarChart"></canvas>
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
<script src="<?= BASE_URL ?>/assets/js/analytics.js"></script>
</body>
</html>
