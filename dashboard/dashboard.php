<?php
/**
 * dashboard.php
 * Admin dashboard shell for SOLIS. Handles authentication and renders
 * the admin sidebar, main content pages, and reusable modal dialogs.
 */

// Admin Dashboard - Check authentication
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SOLIS — Admin</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<link href="css/style.css?v=<?php echo time(); ?>" rel="stylesheet"/>
</head>
<body>

<!-- ═══════════════════ APP SHELL ═══════════════════ -->
<div id="appShell" style="display:flex;width:100%;flex-direction:row;">

<!-- SIDEBAR -->
<nav class="sidebar" id="sidebar">
  <div class="sidebar-brand" style="justify-content: left; text-align: left;">
    <div class="brand-text" style="margin-left: 0;">
      <div class="name">S O L I S</div>
      <div class="tagline">Awaken your senses</div>
    </div>
  </div>
  <div class="nav-section-title">Overview</div>
  <div class="nav-item active" onclick="goTo('dashboard',this)"><i class="fas fa-chart-pie"></i><span class="nav-label">Dashboard</span></div>
  <div class="nav-section-title">Catalog</div>
  <div class="nav-item" onclick="goTo('products',this)"><i class="fas fa-fire"></i><span class="nav-label">Products</span><span class="nav-badge" id="stockBadge"></span></div>
  <div class="nav-item" onclick="goTo('categories',this)"><i class="fas fa-tags"></i><span class="nav-label">Categories</span></div>
  <div class="nav-section-title">Sales</div>
  <div class="nav-item" onclick="goTo('orders',this)"><i class="fas fa-receipt"></i><span class="nav-label">Orders</span></div>
  <div class="nav-item" onclick="goTo('customers',this)"><i class="fas fa-users"></i><span class="nav-label">Customers</span></div>
  <div class="nav-section-title">System</div>
  <div class="nav-item" onclick="goTo('settings',this)"><i class="fas fa-cog"></i><span class="nav-label">Settings</span></div>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar">LC</div>
      <div><div style="color:var(--espresso);font-size:.82rem;font-weight:600;">Super Admin</div><div style="font-size:.7rem;color:var(--muted);">admin@solis.com</div></div>
    </div>
  </div>
</nav>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="topbar-right">
      <div class="icon-btn" onclick="openModal('logoutModal')"><i class="fas fa-sign-out-alt"></i></div>
      <div class="avatar" style="cursor:pointer;" onclick="goTo('settings',null)">LC</div>
    </div>
  </div>

  <div class="content">
    <!-- Main admin pages are loaded here. Only one page is visible at a time. -->
    <?php include 'pages/dashboard.php'; ?>
    <?php include 'pages/products.php'; ?>
    <?php include 'pages/categories.php'; ?>
    <?php include 'pages/orders.php'; ?>
    <?php include 'pages/customers.php'; ?>
    <?php include 'pages/settings.php'; ?>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /appShell -->

<!-- ═══════ MODALS ═══════ -->
<!-- The following modal dialogs are shared across dashboard pages. -->

<!-- Product Modal -->
<div class="modal-overlay" id="productModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="pModalTitle">Add Candle Product</h3>
      <i class="fas fa-times modal-close" onclick="closeModal('productModal')"></i>
    </div>
    <div class="modal-body">
      <div class="two-col">
        <div class="form-group"><label>Product Name</label><input class="form-control" id="pTitle" placeholder="e.g. Vanilla Amber Dream"/></div>
        <div class="form-group"><label>Category</label>
          <select class="form-control" id="pCat">
            <option>Summer</option><option>Spring</option><option>Fall</option><option>Winter</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Scent Notes</label><input class="form-control" id="pScent" placeholder="e.g. Vanilla, Sandalwood, Musk"/></div>
      <div class="form-group"><label>Description</label><textarea class="form-control" id="pDesc" rows="2" placeholder="Describe the fragrance and mood of this candle…" style="resize:vertical;"></textarea></div>
      <div class="two-col">
        <div class="form-group"><label>Price (₱)</label><input class="form-control" id="pPrice" type="number" placeholder="0.00"/></div>
        <div class="form-group"><label>Stock Qty</label><input class="form-control" id="pStock" type="number" placeholder="0"/></div>
      </div>
      <div class="form-group">
        <label>Product Images</label>
        <div class="upload-zone" onclick="document.getElementById('imgInput').click()">
          <i class="fas fa-camera" style="font-size:1.8rem;margin-bottom:8px;color:var(--taupe);display:block;"></i>
          <div style="font-weight:600;font-size:.875rem;color:var(--muted);">Click to upload product photos</div>
          <div style="font-size:.76rem;color:var(--taupe);margin-top:4px;">PNG, JPG · up to 5MB each · drag to reorder</div>
        </div>
        <input type="file" id="imgInput" multiple accept="image/*" style="display:none;" onchange="previewImgs(event)"/>
        <div class="img-preview-row" id="imgPreview">
          <div class="img-thumb"><i class="fas fa-fire"></i><div class="rm" onclick="this.parentNode.remove()">✕</div></div>
          <div class="img-thumb"><i class="fas fa-fire"></i><div class="rm" onclick="this.parentNode.remove()">✕</div></div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('productModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveProduct()"><i class="fas fa-save"></i> Save Product</button>
    </div>
  </div>
</div>

<!-- Category Modal -->
<div class="modal-overlay" id="catModal">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header">
      <h3 id="catModalTitle">Add Category</h3>
      <i class="fas fa-times modal-close" onclick="closeModal('catModal')"></i>
    </div>
    <div class="modal-body">
      <div class="form-group"><label>Category Name</label><input class="form-control" id="catName" placeholder="e.g. Gift Sets"/></div>
      <div class="form-group"><label>Slug (URL)</label><input class="form-control" id="catSlug" placeholder="gift-sets"/></div>
      <div class="form-group"><label>Description (Optional)</label><textarea class="form-control" id="catDesc" rows="2" placeholder="Short category description…" style="resize:vertical;"></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('catModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveCat()"><i class="fas fa-save"></i> Save Category</button>
    </div>
  </div>
</div>

<!-- Admin Modal -->
<div class="modal-overlay" id="adminModal">
  <div class="modal" style="max-width:440px;">
    <div class="modal-header">
      <h3 id="adminModalTitle">Add Admin User</h3>
      <i class="fas fa-times modal-close" onclick="closeModal('adminModal')"></i>
    </div>
    <div class="modal-body">
      <div class="two-col">
        <div class="form-group"><label>First Name</label><input class="form-control" id="aFname" placeholder="John"/></div>
        <div class="form-group"><label>Last Name</label><input class="form-control" id="aLname" placeholder="Doe"/></div>
      </div>
      <div class="form-group"><label>Email</label><input class="form-control" id="aEmail" type="email" placeholder="john@solis.com"/></div>
      <div class="form-group"><label>Role</label>
        <select class="form-control" id="aRole"><option>Super Admin</option><option>Product Manager</option><option>Order Manager</option><option>Viewer</option></select>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-icon-wrap">
          <input class="form-control" id="aPass" type="password" placeholder="Must meet security requirements" oninput="checkRules(this.value,'admin')"/>
          <i class="fas fa-eye" onclick="togglePw('aPass',this)"></i>
        </div>
        <ul class="pw-rules" id="adminPwRules">
          <li id="a1" class="fail"><i class="fas fa-times"></i>Minimum 8 characters</li>
          <li id="a2" class="fail"><i class="fas fa-times"></i>At least one uppercase letter</li>
          <li id="a3" class="fail"><i class="fas fa-times"></i>At least one number</li>
          <li id="a4" class="fail"><i class="fas fa-times"></i>At least one special character</li>
        </ul>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('adminModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveAdmin()"><i class="fas fa-save"></i> Save Admin</button>
    </div>
  </div>
</div>

<!-- Order Detail Modal -->
<div class="modal-overlay" id="orderModal">
  <div class="modal" style="max-width:540px;">
    <div class="modal-header"><h3>Order Details</h3><i class="fas fa-times modal-close" onclick="closeModal('orderModal')"></i></div>
    <div class="modal-body" id="orderModalBody"></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('orderModal')">Close</button>
    </div>
  </div>
</div>

<!-- Customer Detail Modal -->
<div class="modal-overlay" id="customerModal">
  <div class="modal" style="max-width:540px;">
    <div class="modal-header"><h3>Customer Details</h3><i class="fas fa-times modal-close" onclick="closeModal('customerModal')"></i></div>
    <div class="modal-body" id="customerModalBody"></div>
    <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('customerModal')">Close</button></div>
  </div>
</div>

<!-- Confirm/Delete Modal -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal" style="max-width:360px;">
    <div class="modal-body">
      <div class="confirm-box">
        <div class="confirm-icon del" id="confirmIcon"><i class="fas fa-trash"></i></div>
        <h3 id="confirmTitle" style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;">Delete Item?</h3>
        <p id="confirmMsg" style="color:var(--muted);font-size:.875rem;margin:10px 0 22px;"></p>
        <div style="display:flex;gap:10px;justify-content:center;">
          <button class="btn btn-secondary" onclick="closeModal('confirmModal')">Cancel</button>
          <button class="btn btn-danger" id="confirmBtn"><i class="fas fa-trash"></i> Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Logout Modal -->
<div class="modal-overlay" id="logoutModal">
  <div class="modal" style="max-width:380px;">
    <div class="modal-body">
      <div class="confirm-box">
        <div class="confirm-icon warn"><i class="fas fa-sign-out-alt"></i></div>
        <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;">Sign Out?</h3>
        <p style="color:var(--muted);font-size:.875rem;margin:8px 0 4px;">You are about to leave the SOLIS admin panel.</p>
        <div style="background:#fdf0e0;border:1px solid #e8c89a;border-radius:10px;padding:12px 14px;font-size:.82rem;text-align:left;margin:12px 0 16px;">
          <i class="fas fa-exclamation-triangle" style="color:var(--amber);"></i>
          <b style="color:var(--mocha);"> Pending actions:</b>
          <ul style="margin-top:6px;padding-left:18px;color:var(--bark);">
            <li>2 orders awaiting review</li>
            <li>1 unsaved product draft</li>
          </ul>
        </div>
        <p style="color:var(--muted);font-size:.8rem;margin-bottom:18px;">All unsaved changes will be lost upon logout. Please finish any pending tasks before leaving.</p>
        <div style="display:flex;gap:10px;justify-content:center;">
          <button class="btn btn-secondary" onclick="closeModal('logoutModal')"><i class="fas fa-times"></i> Stay</button>
          <button class="btn btn-danger" onclick="doLogout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script src="js/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>