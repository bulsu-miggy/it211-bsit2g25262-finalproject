<?php
/**
 * UniMerch Admin — Products Management
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

requireMerchantAuth();
$merchant = getMerchant();
$pendingOrders = db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$categories = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products — UniMerch Admin</title>
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
          <h1 class="admin-page-title">Products</h1>
        </div>
      </div>
      <div class="admin-topbar-right">
        <button class="btn btn-primary-gradient btn-sm" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetProductForm()">
          <i class="bi bi-plus-lg me-1"></i>Add Product
        </button>
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
                <input type="text" class="form-control border-0 ps-0" id="productSearch" placeholder="Search products..." style="font-size: 0.8rem;">
              </div>
            </div>
            <div class="col-lg-4 col-md-3">
              <select class="form-select form-select-sm border-0 bg-light shadow-none" id="productCategoryFilter" style="border-radius: 8px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= sanitize($cat['code']) ?> — <?= sanitize($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-lg-4 col-md-4">
              <select class="form-select form-select-sm border-0 bg-light shadow-none" id="productStatusFilter" style="border-radius: 8px;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Table -->
      <div class="admin-panel">
        <div class="admin-panel-body no-pad">
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th class="text-start" style="width: 35%;">Product</th>
                  <th class="text-center" style="width: 10%;">Category</th>
                  <th class="text-center" style="width: 12%;">Price</th>
                  <th class="text-center" style="width: 10%;">Stock</th>
                  <th class="text-center d-none d-md-table-cell" style="width: 10%;">Status</th>
                  <th class="text-center d-none d-md-table-cell" style="width: 10%;">Featured</th>
                  <th class="text-center" style="width: 13%;">Actions</th>
                </tr>
              </thead>
              <tbody id="productsTableBody">
                <tr><td colspan="7" class="text-center py-4"><div class="spinner mx-auto"></div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal fade admin-modal" id="productModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalTitle">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="productForm" enctype="multipart/form-data">
          <input type="hidden" id="productId">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Product Name *</label>
              <input type="text" class="form-control" id="productName" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Category *</label>
              <select class="form-select" id="productCategory" required>
                <option value="">Select...</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= sanitize($cat['code']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description</label>
              <textarea class="form-control" id="productDescription" rows="3"></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Price (₱) *</label>
              <input type="number" class="form-control" id="productPrice" step="0.01" min="0" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Stock *</label>
              <input type="number" class="form-control" id="productStock" min="0" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sizes</label>
              <input type="text" class="form-control" id="productSizes" placeholder="XS,S,M,L,XL">
              <small class="text-muted">Comma-separated. Leave blank for no sizes.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Product Image</label>
              <div class="image-preview-zone" id="imagePreviewZone" onclick="document.getElementById('productImage').click()">
                <i class="bi bi-cloud-arrow-up" style="font-size:2rem; color:var(--gray-400);"></i>
                <p class="text-muted mb-0 mt-2" style="font-size:0.85rem;">Click to upload</p>
              </div>
              <input type="file" id="productImage" accept="image/*" style="display:none;">
            </div>
            <div class="col-md-6 d-flex flex-column justify-content-center">
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="productFeatured">
                <label class="form-check-label fw-semibold" for="productFeatured">Featured Product</label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary-gradient" id="saveProductBtn" onclick="saveProduct()">
          <i class="bi bi-check-lg me-1"></i>Save Product
        </button>
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
<script src="<?= BASE_URL ?>/assets/js/products-admin.js"></script>
</body>
</html>
