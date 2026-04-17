<?php
/**
 * UniMerch — Forgot Password
 * Step 1: Email Input
 * Step 2: 4-digit OTP Verification
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
  <title>Reset Password — UniMerch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/storefront.css" rel="stylesheet">
</head>
<body>

  <div class="auth-page">
    <div class="auth-card">
      <div id="emailStep">
        <div class="logo">
          <h2><i class="bi bi-shield-lock-fill me-1"></i>Uni<span>Merch</span></h2>
          <p>Password Recovery</p>
        </div>
        <p class="text-muted text-center mb-4" style="font-size:0.9rem;">
          Enter your email address and we'll send you a 4-digit code to reset your password.
        </p>

        <form id="forgotForm">
          <div class="mb-4">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" id="resetEmail" placeholder="you@bulsu.edu.ph" required>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary-gradient btn-lg" id="sendOtpBtn">
              Send Reset Code
            </button>
          </div>
        </form>
      </div>

      <div id="otpStep" style="display:none;">
        <div class="logo">
          <h2><i class="bi bi-patch-check-fill me-1"></i>Uni<span>Merch</span></h2>
          <p>Verify Code</p>
        </div>
        <p class="text-muted text-center mb-4" style="font-size:0.9rem;">
          We've sent a 4-digit security code to <strong id="displayEmail"></strong>
        </p>

        <!-- Simulated OTP Display (for demo) -->
        <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" id="demoOtpAlert" style="display:none !important; border-radius:var(--radius-lg); font-size:0.8rem;">
          <i class="bi bi-info-circle mt-1"></i>
          <div>
            <strong>Demo Mode:</strong> Your recovery code is <strong id="otpDisplay" class="text-primary" style="font-size:1.1rem;"></strong>
            <br><small>In production, this would be sent via email.</small>
          </div>
        </div>

        <form id="otpForm">
          <div class="mb-4 d-flex justify-content-center gap-2">
            <input type="text" maxlength="1" class="form-control otp-input" pattern="[0-9]" required>
            <input type="text" maxlength="1" class="form-control otp-input" pattern="[0-9]" required>
            <input type="text" maxlength="1" class="form-control otp-input" pattern="[0-9]" required>
            <input type="text" maxlength="1" class="form-control otp-input" pattern="[0-9]" required>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-accent btn-lg" id="verifyBtn">
              Verify & Continue
            </button>
          </div>
          <p class="text-center mt-3 small text-muted">
            Didn't get the code? <a href="#" onclick="resendOtp(event)" class="text-primary fw-bold">Resend</a>
          </p>
        </form>
      </div>

      <p class="text-center mt-4 mb-0" style="font-size:0.9rem; color:var(--gray-500);">
        Remember your password? <a href="<?= BASE_URL ?>/login.php" class="fw-semibold">Back to Login</a>
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
    // Handle Forgot Email
    $('#forgotForm').on('submit', function(e) {
      e.preventDefault();
      const email = $('#resetEmail').val();
      const btn = $('#sendOtpBtn');
      
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Sending...');
      
      $.ajax({
        url: BASE_URL + '/api/auth.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'forgot_password', email: email }),
        success: function(res) {
          if (res.success) {
            $('#displayEmail').text(email);
            $('#emailStep').hide();
            $('#otpStep').fadeIn();
            showToast(res.message, 'success');
            
            // For demo: Show the OTP on screen
            if(res.otp) {
              $('#otpDisplay').text(res.otp);
              $('#demoOtpAlert').attr('style', 'display:flex !important');
            }
          } else {
            showToast(res.message, 'error');
            btn.prop('disabled', false).text('Send Reset Code');
          }
        }
      });
    });

    // OTP Inputs Auto-focus
    $('.otp-input').on('keyup', function(e) {
      if (this.value.length === 1) {
        $(this).next('.otp-input').focus();
      } else if (e.keyCode === 8) { // Backspace
        $(this).prev('.otp-input').focus();
      }
    });

    // Handle OTP Verify
    $('#otpForm').on('submit', function(e) {
      e.preventDefault();
      const email = $('#resetEmail').val();
      let otp = '';
      $('.otp-input').each(function() { otp += this.value; });
      
      const btn = $('#verifyBtn');
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Verifying...');

      $.ajax({
        url: BASE_URL + '/api/auth.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'verify_reset_otp', email: email, otp: otp }),
        success: function(res) {
          if (res.success) {
            showToast('OTP Verified! Proceeding to reset.', 'success');
            setTimeout(() => {
              window.location.href = BASE_URL + '/reset_password.php';
            }, 1000);
          } else {
            showToast(res.message, 'error');
            btn.prop('disabled', false).text('Verify & Continue');
            $('.otp-input').addClass('is-invalid');
          }
        }
      });
    });

    function resendOtp(e) {
      e.preventDefault();
      $('#forgotForm').submit();
    }
  </script>

  <style>
    .otp-input {
      width: 50px;
      height: 60px;
      text-align: center;
      font-size: 1.5rem;
      font-weight: 800;
      border-radius: 12px;
      border: 2px solid var(--gray-200);
      color: var(--primary-600);
    }
    .otp-input:focus {
      border-color: var(--primary-500);
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
  </style>
</body>
</html>
