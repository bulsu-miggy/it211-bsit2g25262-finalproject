<?php
/**
 * UniMerch — Reset Password
 * Step 3: New Password with Complexity Checklist
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

session_start();
if (!($_SESSION['reset_authorized'] ?? false)) {
    header('Location: ' . BASE_URL . '/forgot_password.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Password — UniMerch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/storefront.css" rel="stylesheet">
  <style>
    .checklist-item {
      font-size: 0.85rem;
      color: var(--gray-500);
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      transition: all 0.2s ease;
    }
    .checklist-item i {
      margin-right: 8px;
      font-size: 1rem;
    }
    .checklist-item.valid {
      color: #059669;
    }
    .checklist-item.valid i::before {
      content: "\f272"; /* bi-check-circle-fill */
    }
  </style>
</head>
<body>

  <div class="auth-page">
    <div class="auth-card">
      <div class="logo">
        <h2><i class="bi bi-key-fill me-1"></i>Uni<span>Merch</span></h2>
        <p>Security Guidance</p>
      </div>
      <p class="text-muted text-center mb-4" style="font-size:0.9rem;">
        Complete the forensic security criteria to update your account password.
      </p>

      <form id="resetForm">
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <div class="position-relative">
            <input type="password" class="form-control" id="newPassword" placeholder="••••••••" required>
            <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2" 
                    onclick="togglePassword('newPassword', this)" style="border:none; background:none;">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <div class="mb-4 p-3 bg-light rounded-3">
          <div class="checklist-item" id="crit-length">
            <i class="bi bi-circle"></i> Minimum 8 characters
          </div>
          <div class="checklist-item" id="crit-upper">
            <i class="bi bi-circle"></i> At least one Uppercase letter
          </div>
          <div class="checklist-item" id="crit-number">
            <i class="bi bi-circle"></i> At least one Number
          </div>
          <div class="checklist-item" id="crit-special">
            <i class="bi bi-circle"></i> At least one Special character (!, @, #, etc.)
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label">Confirm Password</label>
          <input type="password" class="form-control" id="confirmPassword" placeholder="••••••••" required>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-accent btn-lg" id="submitBtn" disabled>
            <i class="bi bi-check2-circle me-2"></i>Reset Password
          </button>
        </div>
      </form>
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

    const passwordInput = $('#newPassword');
    const confirmInput = $('#confirmPassword');
    const submitBtn = $('#submitBtn');

    passwordInput.on('input', function() {
      const val = $(this).val();
      
      const isLength = val.length >= 8;
      const isUpper = /[A-Z]/.test(val);
      const isNumber = /[0-9]/.test(val);
      const isSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(val);

      toggleCriteria('#crit-length', isLength);
      toggleCriteria('#crit-upper', isUpper);
      toggleCriteria('#crit-number', isNumber);
      toggleCriteria('#crit-special', isSpecial);

      checkValidity();
    });

    confirmInput.on('input', checkValidity);

    function toggleCriteria(id, isValid) {
      const el = $(id);
      if (isValid) {
        el.addClass('valid').find('i').removeClass('bi-circle').addClass('bi-check-circle-fill');
      } else {
        el.removeClass('valid').find('i').removeClass('bi-check-circle-fill').addClass('bi-circle');
      }
    }

    function checkValidity() {
      const pass = passwordInput.val();
      const confirm = confirmInput.val();
      
      const allCriteria = (pass.length >= 8 && /[A-Z]/.test(pass) && /[0-9]/.test(pass) && /[!@#$%^&*(),.?":{}|<>]/.test(pass));
      const match = (pass === confirm && pass !== '');

      submitBtn.prop('disabled', !(allCriteria && match));
    }

    $('#resetForm').on('submit', function(e) {
      e.preventDefault();
      submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

      $.ajax({
        url: BASE_URL + '/api/auth.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'reset_password', password: passwordInput.val() }),
        success: function(res) {
          if (res.success) {
            showToast(res.message, 'success');
            setTimeout(() => {
              window.location.href = BASE_URL + '/login.php';
            }, 1500);
          } else {
            showToast(res.message, 'error');
            submitBtn.prop('disabled', false).text('Reset Password');
          }
        }
      });
    });
  </script>
</body>
</html>
