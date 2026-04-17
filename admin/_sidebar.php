<?php
/**
 * UniMerch Admin — Shared Sidebar Component
 */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pendingOrders = $pendingOrders ?? 0;
?>
<!-- Admin Sidebar (Offcanvas for mobile, Sidebar for desktop) -->
<aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
  <div class="offcanvas-header border-bottom d-lg-none flex-column align-items-start gap-3">
    <div class="d-flex align-items-center justify-content-between w-100">
      <div class="admin-sidebar-brand py-0">
        <h4 class="mb-0"><i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span></h4>
      </div>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close"></button>
    </div>
  </div>

  <div class="offcanvas-body d-flex flex-column p-0">
    <div class="admin-sidebar-brand d-none d-lg-block">
      <h4><i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span></h4>
      <small>Merchant Dashboard</small>
    </div>

    <nav class="admin-nav">
      <div class="admin-nav-section">
        <div class="admin-nav-section-title">Main</div>
        <a href="<?= BASE_URL ?>/admin/" class="admin-nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">
          <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="admin-nav-link <?= $currentPage === 'orders' ? 'active' : '' ?>">
          <i class="bi bi-bag-check"></i> Orders
          <?php if ($pendingOrders > 0): ?>
            <span class="badge"><?= $pendingOrders ?></span>
          <?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/products.php" class="admin-nav-link <?= $currentPage === 'products' ? 'active' : '' ?>">
          <i class="bi bi-box-seam"></i> Products
        </a>
      </div>

      <div class="admin-nav-section">
        <div class="admin-nav-section-title">Insights</div>
        <a href="<?= BASE_URL ?>/admin/analytics.php" class="admin-nav-link <?= $currentPage === 'analytics' ? 'active' : '' ?>">
          <i class="bi bi-bar-chart-line"></i> Analytics
        </a>
      </div>

      <div class="admin-nav-section">
        <div class="admin-nav-section-title">Quick Links</div>
        <a href="<?= BASE_URL ?>/" target="_blank" class="admin-nav-link">
          <i class="bi bi-shop"></i> View Store
        </a>
      </div>
    </nav>

    <div class="admin-sidebar-footer">
      <div class="merchant-info">
        <div class="merchant-avatar">
          <?= strtoupper(substr($merchant['full_name'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="flex-grow-1">
          <div class="merchant-name"><?= sanitize($merchant['full_name'] ?? 'Admin') ?></div>
          <div class="merchant-role">Merchant Dashboard</div>
        </div>
        <a href="<?= BASE_URL ?>/admin/logout.php" class="logout-icon-btn" title="Logout">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</aside>
