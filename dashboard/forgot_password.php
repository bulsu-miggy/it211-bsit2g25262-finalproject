<?php
// Admin Forgot Password Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Lumière Candle Co. — Reset Password</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<link href="css/style.css" rel="stylesheet"/>
</head>
<body>

<!-- ═══════════════════ FORGOT PASSWORD ═══════════════════ -->
<div class="auth-screen active" id="forgotScreen">
  <div class="auth-bg-deco"></div>
  <div class="auth-card">
    <div class="auth-logo">
      <span class="candle-icon"></span>
      <h2>Reset Password</h2>
      <p>We'll guide you back in</p>
    </div>
    <div id="forgotSuccess" style="background:#e8f2e7;border-radius:10px;padding:11px 14px;color:#4a7a44;font-size:.83rem;margin-bottom:14px;display:none;border:1px solid #c0d9bc;">
      <i class="fas fa-check-circle"></i> A reset link has been sent to your email!
    </div>
    <div class="form-group">
      <label>Registered Email</label>
      <input class="form-control" id="forgotEmail" type="email" placeholder="admin@solis.com"/>
      <div class="form-hint">Enter the email linked to your admin account. A secure reset link will be sent.</div>
    </div>
    <div id="newPwBlock" style="display:none;">
      <div class="form-group">
        <label>Create New Password</label>
        <div class="input-icon-wrap">
          <input class="form-control" id="newPw" type="password" placeholder="New secure password" oninput="checkRules(this.value,'pw')"/>
          <i class="fas fa-eye" onclick="togglePw('newPw',this)"></i>
        </div>
        <ul class="pw-rules" id="pwRules">
          <li id="r1" class="fail"><i class="fas fa-times"></i>Minimum 8 characters</li>
          <li id="r2" class="fail"><i class="fas fa-times"></i>At least one uppercase letter (A–Z)</li>
          <li id="r3" class="fail"><i class="fas fa-times"></i>At least one number (0–9)</li>
          <li id="r4" class="fail"><i class="fas fa-times"></i>At least one special character (!@#$%)</li>
        </ul>
      </div>
    </div>
    <button class="btn btn-primary" onclick="doForgot()"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
    <div class="auth-link"><a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a></div>
  </div>
</div>

<script src="js/dashboard.js"></script>
</body>
</html>