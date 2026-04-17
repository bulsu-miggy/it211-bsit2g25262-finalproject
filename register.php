<?php
/**
 * UniMerch — Customer Registration
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
  <title>Create Account — UniMerch</title>
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
        <h2><i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span></h2>
        <p>Create your account to start shopping</p>
      </div>

      <!-- Step 1: Registration Form -->
      <div id="registerStep">
        <form class="auth-form" id="registerForm">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">First Name</label>
              <input type="text" class="form-control" id="regFirstName" required>
            </div>
            <div class="col-6">
              <label class="form-label">Last Name</label>
              <input type="text" class="form-control" id="regLastName" required>
            </div>
            <div class="col-12">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" id="regEmail" placeholder="you@bulsu.edu.ph" required>
            </div>
            <div class="col-12">
              <label class="form-label">Phone Number</label>
              <input type="tel" class="form-control" id="regPhone" placeholder="09XX-XXX-XXXX">
            </div>
            <div class="col-12">
              <label class="form-label">Password</label>
              <div class="position-relative">
                <input type="password" class="form-control" id="regPassword" placeholder="••••••••" required>
                <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2" 
                        onclick="togglePassword('regPassword', this)" style="border:none; background:none;">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="col-12">
              <div class="p-3 bg-light rounded-3">
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
            </div>
            <div class="col-12">
              <label class="form-label">Confirm Password</label>
              <input type="password" class="form-control" id="regConfirmPassword" required>
            </div>
            <div class="col-12">
              <div class="d-grid">
                <button type="submit" class="btn btn-accent btn-lg" id="registerBtn" disabled>
                  <i class="bi bi-person-plus me-2"></i>Create Account
                </button>
              </div>
            </div>
          </div>
        </form>
        <p class="text-center mt-4 mb-0" style="font-size:0.9rem; color:var(--gray-500);">
          Already have an account? <a href="<?= BASE_URL ?>/login.php" class="fw-semibold">Log in</a>
        </p>
      </div>

      <!-- Step 2: OTP Verification -->
      <div id="otpStep" style="display:none;">
        <div class="text-center mb-3">
          <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
               style="width:64px; height:64px; background:var(--primary-50);">
            <i class="bi bi-envelope-check" style="font-size:1.75rem; color:var(--primary-600);"></i>
          </div>
          <h5 style="font-family: var(--font-heading); font-weight:700;">Verify Your Email</h5>
          <p style="font-size:0.85rem; color:var(--gray-500);">
            We've sent a 6-digit code to <strong id="otpEmail"></strong>
          </p>
        </div>

        <!-- Simulated OTP Display (for demo) -->
        <div class="alert alert-warning d-flex align-items-start gap-2" style="border-radius:var(--radius-lg); font-size:0.8rem;">
          <i class="bi bi-info-circle mt-1"></i>
          <div>
            <strong>Demo Mode:</strong> Your OTP code is <strong id="otpDisplay" class="text-primary" style="font-size:1.1rem;"></strong>
            <br><small>In production, this would be sent via email/SMS.</small>
          </div>
        </div>

        <form id="otpForm">
          <div class="otp-inputs" id="otpInputs">
            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-accent btn-lg" id="verifyOtpBtn">
              <i class="bi bi-shield-check me-2"></i>Verify & Continue
            </button>
          </div>
        </form>
        <p class="text-center mt-3" style="font-size:0.85rem; color:var(--gray-500);">
          Didn't receive the code? <a href="#" id="resendOtpLink" class="fw-semibold">Resend</a>
        </p>
      </div>
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

  // Password Complexity Validation
  const passwordInput = $('#regPassword');
  const confirmInput = $('#regConfirmPassword');
  const submitBtn = $('#registerBtn');

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

    checkRegistrationValidity();
  });

  confirmInput.on('input', checkRegistrationValidity);

  function toggleCriteria(id, isValid) {
    const el = $(id);
    if (isValid) {
      el.addClass('valid').find('i').removeClass('bi-circle').addClass('bi-check-circle-fill');
    } else {
      el.removeClass('valid').find('i').removeClass('bi-check-circle-fill').addClass('bi-circle');
    }
  }

  function checkRegistrationValidity() {
    const pass = passwordInput.val();
    const confirm = confirmInput.val();
    
    const allCriteria = (pass.length >= 8 && /[A-Z]/.test(pass) && /[0-9]/.test(pass) && /[!@#$%^&*(),.?":{}|<>]/.test(pass));
    const match = (pass === confirm && pass !== '');

    submitBtn.prop('disabled', !(allCriteria && match));
  }
  </script>
</body>
</html>
