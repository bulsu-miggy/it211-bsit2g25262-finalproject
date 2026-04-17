<?php
/**
 * UniMerch Admin — Login Page
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (isMerchantLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Merchant Login — UniMerch Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/dashboard.css" rel="stylesheet">
  <style>
    body {
      background: radial-gradient(circle at top right, #1e293b 0%, #0f172a 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }
    .admin-login-card {
      background: rgba(30, 41, 59, 0.7);
      backdrop-filter: blur(12px);
      padding: 2.5rem;
      border-radius: 1.5rem;
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      width: 100%;
      max-width: 420px;
    }
    .input-group-text {
      background: rgba(255, 255, 255, 0.03) !important;
      border-color: rgba(255, 255, 255, 0.1) !important;
      color: var(--gray-400) !important;
    }
    .form-control {
      background: rgba(255, 255, 255, 0.05) !important;
      border-color: rgba(255, 255, 255, 0.1) !important;
      color: white !important;
    }
    .form-control::placeholder {
      color: var(--gray-500) !important;
    }
    .form-control:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--primary-500) !important;
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }
  </style>
</head>
<body>

  <div class="admin-login-page">
    <div class="admin-login-card">
      <div class="text-center mb-5">
        <h3 style="font-family: var(--font-heading); font-weight: 800; letter-spacing: -0.02em;">
          <i class="bi bi-mortarboard-fill me-1" style="color:var(--primary-400);"></i>Uni<span style="color: var(--accent-400);">Merch</span>
        </h3>
        <p style="color: var(--gray-400); font-size: 0.85rem;">Merchant Dashboard Access</p>
      </div>

      <form id="adminLoginForm">
        <div class="mb-3">
          <label class="form-label small text-gray-300">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="adminUsername" placeholder="Enter username" required>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label small text-gray-300">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="adminPassword" placeholder="Enter password" required>
          </div>
        </div>
        <div class="d-grid shadow-lg">
          <button type="submit" class="btn btn-primary-gradient btn-lg" id="adminLoginBtn" style="min-height: 52px; font-weight: 600;">
            Sign In
          </button>
        </div>
      </form>

      <div class="text-center mt-5" style="font-size: 0.78rem; color: var(--gray-500);">
        <i class="bi bi-shield-lock me-1"></i>Protected area &bull; Authorized merchants only
      </div>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>const BASE_URL = '<?= BASE_URL ?>';</script>

  <script>
  // Toast utility
  function showToast(message, type = 'info') {
    const icons = { success: 'check-circle-fill', error: 'exclamation-circle-fill', warning: 'exclamation-triangle-fill', info: 'info-circle-fill' };
    const toast = $(`
      <div class="toast toast-${type}">
        <i class="bi bi-${icons[type] || icons.info}"></i>
        <span>${message}</span>
      </div>
    `);
    $('#toastContainer').append(toast);
    setTimeout(() => toast.fadeOut(300, function() { $(this).remove(); }), 4000);
  }

  $('#adminLoginForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#adminLoginBtn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Signing in...');

    $.ajax({
      url: BASE_URL + '/api/admin/auth.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({
        username: $('#adminUsername').val(),
        password: $('#adminPassword').val()
      }),
      success: function(res) {
        if (res.success) {
          showToast(res.message, 'success');
          setTimeout(() => window.location.href = res.redirect, 800);
        } else {
          showToast(res.message, 'error');
          btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right me-2"></i>Sign In');
        }
      },
      error: function() {
        showToast('Connection failed. Check if XAMPP is running.', 'error');
        btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right me-2"></i>Sign In');
      }
    });
  });
  </script>
</body>
</html>
