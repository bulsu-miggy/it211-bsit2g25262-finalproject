<?php
/**
 * UniMerch Admin — Customers Management
 * Displays searchable list of customers, their purchase history, and saved addresses.
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
  <title>Customers — UniMerch Admin</title>
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
          <div class="d-none d-lg-block">
            <h1 class="admin-page-title">Customers</h1>
          </div>
        </div>
      <div class="admin-topbar-right">
        <input type="text" class="form-control form-control-sm" id="customerSearch" placeholder="Search customers..." style="width:250px;">
      </div>
    </div>

    <div class="admin-body">
      <div class="admin-panel">
        <div class="admin-panel-body no-pad">
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Customer Name</th>
                  <th>Contact Info</th>
                  <th>Orders</th>
                  <th>Total Spent</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="customersTableBody">
                <tr><td colspan="6" class="text-center py-4"><div class="spinner mx-auto"></div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Customer Detail Modal -->
<div class="modal fade admin-modal" id="customerDetailModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Customer Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="customerDetailBody">
        <!-- Loaded via AJAX -->
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
<script>
  $(document).ready(function() {
    loadCustomers();

    $('#customerSearch').on('input', function() {
      loadCustomers($(this).val().trim());
    });
  });

  function loadCustomers(search = '') {
    $.get(BASE_URL + '/api/admin/customers.php', { search: search }, function(res) {
      if (!res.success) return;
      
      let html = '';
      if (res.data.length === 0) {
        html = '<tr><td colspan="6" class="text-center py-4 text-muted">No customers found</td></tr>';
      } else {
        res.data.forEach(c => {
          html += `
            <tr>
              <td data-label="Customer">
                <div class="fw-bold">${c.first_name} ${c.last_name}</div>
                <small class="text-muted">ID: #${c.id}</small>
              </td>
              <td data-label="Contact">
                <div><i class="bi bi-envelope me-1"></i>${c.email}</div>
                <small class="text-muted"><i class="bi bi-phone me-1"></i>${c.phone || 'N/A'}</small>
              </td>
              <td data-label="Orders">${c.order_count} orders</td>
              <td data-label="Total Spent" class="fw-bold">₱${parseFloat(c.total_spent || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
              <td data-label="Status">
                <span class="badge ${c.is_verified ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}">
                  ${c.is_verified ? 'Verified' : 'Unverified'}
                </span>
              </td>
              <td data-label="Actions">
                <button class="btn btn-ghost btn-sm" onclick="viewCustomer(${c.id})"><i class="bi bi-eye"></i></button>
              </td>
            </tr>
          `;
        });
      }
      $('#customersTableBody').html(html);
    });
  }

  function viewCustomer(id) {
    $('#customerDetailBody').html('<div class="text-center py-5"><div class="spinner mx-auto"></div></div>');
    new bootstrap.Modal('#customerDetailModal').show();

    $.get(BASE_URL + '/api/admin/customers.php', { id: id }, function(res) {
      if (!res.success) return;
      const c = res.data;

      let historyHTML = '';
      if (c.orders.length === 0) {
        historyHTML = '<p class="text-muted">No purchase history found.</p>';
      } else {
        historyHTML = `
          <table class="table table-sm table-borderless align-middle" style="font-size:0.85rem;">
            <thead><tr class="text-muted"><th>Order #</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
            <tbody>
              ${c.orders.map(o => `
                <tr>
                  <td><a href="orders.php?search=${o.order_number}" class="fw-bold">${o.order_number}</a></td>
                  <td>${new Date(o.created_at).toLocaleDateString()}</td>
                  <td><span class="badge-status badge-${o.status}">${o.status}</span></td>
                  <td class="fw-bold">₱${parseFloat(o.total_amount).toLocaleString()}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        `;
      }

      let addressesHTML = '';
      if (c.addresses.length === 0) {
        addressesHTML = '<p class="text-muted">No saved addresses.</p>';
      } else {
        addressesHTML = c.addresses.map(a => `
          <div class="p-2 mb-2 bg-light rounded border-start border-4 ${a.is_default ? 'border-primary' : 'border-gray-300'}">
            <div class="fw-bold small d-flex justify-content-between">
              ${a.label} ${a.is_default ? '<span class="text-primary">(Default)</span>' : ''}
            </div>
            <div style="font-size:0.8rem;">${a.full_address}, ${a.city}, ${a.province} ${a.postal_code}</div>
          </div>
        `).join('');
      }

      $('#customerDetailBody').html(`
        <div class="row g-4">
          <div class="col-md-4 border-end">
            <div class="text-center mb-3">
              <div class="avatar-lg mx-auto mb-2" style="width:80px; height:80px; background:var(--primary-100); color:var(--primary-600); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:700;">
                ${c.first_name[0]}${c.last_name[0]}
              </div>
              <h5 class="mb-0">${c.first_name} ${c.last_name}</h5>
              <p class="text-muted small">Customer since ${new Date(c.created_at).toLocaleDateString()}</p>
            </div>
            <hr>
            <div class="mb-3">
              <label class="text-muted small text-uppercase fw-bold mb-1">Contact Details</label>
              <div class="small mb-1"><i class="bi bi-envelope me-2"></i>${c.email}</div>
              <div class="small"><i class="bi bi-phone me-2"></i>${c.phone || 'N/A'}</div>
            </div>
            <div>
              <label class="text-muted small text-uppercase fw-bold mb-1">Saved Addresses</label>
              ${addressesHTML}
            </div>
          </div>
          <div class="col-md-8">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Purchase History</h6>
            ${historyHTML}
          </div>
        </div>
      `);
    });
  }
</script>
</body>
</html>
