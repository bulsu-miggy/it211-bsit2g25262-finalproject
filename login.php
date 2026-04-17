<?php
/**
 * UniMerch — Customer Login
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

if (isCustomerLoggedIn()) {
    header('Location: ' . BASE_URL . '/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — UniMerch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/storefront.css" rel="stylesheet">
</head>
<body>

  <div class="auth-page">
    <div class="auth-card">
      <div class="logo">
        <h2><i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span></h2>
        <p>Welcome back! Log in to your account.</p>
      </div>

      <form class="auth-form" id="loginForm">
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control" id="loginEmail" placeholder="you@bulsu.edu.ph" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="position-relative">
            <input type="password" class="form-control" id="loginPassword" required>
            <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2" 
                    onclick="togglePassword('loginPassword', this)" style="border:none; background:none;">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label" for="rememberMe" style="font-size:0.85rem;">Remember me</label>
          </div>
          <a href="<?= BASE_URL ?>/forgot_password.php" style="font-size: 0.85rem; color: var(--primary-600); text-decoration: none; font-weight: 500;">Forgot Password?</a>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-accent btn-lg" id="loginBtn">
            <i class="bi bi-box-arrow-in-right me-2"></i>Log In
          </button>
        </div>
      </form>

      <p class="text-center mt-4 mb-0" style="font-size:0.9rem; color:var(--gray-500);">
        Don't have an account? <a href="<?= BASE_URL ?>/register.php" class="fw-semibold">Sign up</a>
      </p>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>const BASE_URL = '<?= BASE_URL ?>';</script>
  <script src="<?= BASE_URL ?>/assets/js/common.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/auth.js"></script>

  <script>
  function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'bi bi-eye-slash';
    } else {
      input.type = 'password';
      icon.className = 'bi bi-eye';
    }
  }
  </script>
</body>
</html>
