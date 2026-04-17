/**
 * UniMerch — Customer Auth JavaScript
 * Registration, OTP verification, and Login
 */

let registrationEmail = '';

// ============================================================
// Registration
// ============================================================
$(document).on('submit', '#registerForm', function(e) {
  e.preventDefault();

  const password = $('#regPassword').val();
  const confirm = $('#regConfirmPassword').val();

  if (password !== confirm) {
    showToast('Passwords do not match', 'error');
    return;
  }

  const btn = $('#registerBtn');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating account...');

  $.ajax({
    url: `${BASE_URL}/api/auth.php`,
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      action: 'register',
      first_name: $('#regFirstName').val().trim(),
      last_name: $('#regLastName').val().trim(),
      email: $('#regEmail').val().trim(),
      phone: $('#regPhone').val().trim(),
      password: password
    }),
    success(res) {
      if (res.success) {
        showToast(res.message, 'success');
        registrationEmail = res.email;
        
        // Show OTP step
        $('#registerStep').slideUp(300);
        setTimeout(() => {
          $('#otpStep').slideDown(300);
          $('#otpEmail').text(res.email);
          $('#otpDisplay').text(res.otp);
          // Focus first OTP input
          $('#otpInputs input:first').focus();
        }, 300);
      } else {
        showToast(res.message, 'error');
        btn.prop('disabled', false).html('<i class="bi bi-person-plus me-2"></i>Create Account');
      }
    },
    error() {
      showToast('Registration failed. Please try again.', 'error');
      btn.prop('disabled', false).html('<i class="bi bi-person-plus me-2"></i>Create Account');
    }
  });
});

// ============================================================
// OTP Input Behavior
// ============================================================
$(document).on('input', '#otpInputs input', function() {
  // Move to next input
  if (this.value.length === 1) {
    $(this).next('input').focus();
  }
});

$(document).on('keydown', '#otpInputs input', function(e) {
  // Backspace moves to previous input
  if (e.key === 'Backspace' && !this.value) {
    $(this).prev('input').focus();
  }
});

$(document).on('paste', '#otpInputs input', function(e) {
  // Handle paste of OTP
  e.preventDefault();
  const paste = (e.originalEvent.clipboardData || window.clipboardData).getData('text').trim();
  if (/^\d{6}$/.test(paste)) {
    $('#otpInputs input').each(function(i) {
      $(this).val(paste[i]);
    });
    $('#otpInputs input:last').focus();
  }
});

// ============================================================
// OTP Verification
// ============================================================
$(document).on('submit', '#otpForm', function(e) {
  e.preventDefault();

  let otp = '';
  $('#otpInputs input').each(function() { otp += $(this).val(); });

  if (otp.length !== 6) {
    showToast('Please enter the complete 6-digit code', 'warning');
    return;
  }

  const btn = $('#verifyOtpBtn');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Verifying...');

  $.ajax({
    url: `${BASE_URL}/api/auth.php`,
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      action: 'verify_otp',
      email: registrationEmail,
      otp: otp
    }),
    success(res) {
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => { window.location.href = res.redirect; }, 1000);
      } else {
        showToast(res.message, 'error');
        btn.prop('disabled', false).html('<i class="bi bi-shield-check me-2"></i>Verify & Continue');
        // Clear OTP inputs
        $('#otpInputs input').val('');
        $('#otpInputs input:first').focus();
      }
    },
    error() {
      showToast('Verification failed', 'error');
      btn.prop('disabled', false).html('<i class="bi bi-shield-check me-2"></i>Verify & Continue');
    }
  });
});

// Resend OTP
$(document).on('click', '#resendOtpLink', function(e) {
  e.preventDefault();
  $.ajax({
    url: `${BASE_URL}/api/auth.php`,
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ action: 'resend_otp', email: registrationEmail }),
    success(res) {
      if (res.success) {
        showToast('New OTP sent!', 'success');
        $('#otpDisplay').text(res.otp);
      } else {
        showToast(res.message, 'error');
      }
    }
  });
});

// ============================================================
// Login
// ============================================================
$(document).on('submit', '#loginForm', function(e) {
  e.preventDefault();

  const btn = $('#loginBtn');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Logging in...');

  $.ajax({
    url: `${BASE_URL}/api/auth.php`,
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      action: 'login',
      email: $('#loginEmail').val().trim(),
      password: $('#loginPassword').val()
    }),
    success(res) {
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => { window.location.href = res.redirect; }, 800);
      } else {
        showToast(res.message, 'error');
        btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right me-2"></i>Log In');
      }
    },
    error() {
      showToast('Login failed', 'error');
      btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right me-2"></i>Log In');
    }
  });
});
