<?php
// Admin Login Page
$error = $_GET['error'] ?? '';
$error_msg = '';
$success = $_GET['success'] ?? '';
if ($error === 'invalid') {
    $error_msg = 'Invalid email or password. Please try again.';
} elseif ($error === 'empty_fields') {
    $error_msg = 'Please fill in all fields.';
} elseif ($error === 'invalid_email') {
    $error_msg = 'Please enter a valid email address.';
} elseif ($error === 'password_too_short') {
    $error_msg = 'Password must be 8+ characters.';
} elseif ($error === 'email_exists') {
    $error_msg = 'Email already exists.';
} elseif ($error === 'signup_failed') {
    $error_msg = 'Signup failed. Try again.';
} elseif ($error === 'sql_error') {
    $error_msg = 'Database error. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SOLIS — Admin Login</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<link href="css/style.css" rel="stylesheet"/>
<link href="css/pages/auth.css" rel="stylesheet"/>
</head>
<body>

<!-- ═══════════════════ LOGIN ═══════════════════ -->
<div class="auth-screen active" id="loginScreen">
  <div class="auth-bg-deco"></div>
  <div class="auth-card">
    <div class="auth-logo">
      <span class="candle-icon">🕯️</span>
      <h2>S O L I S</h2>
      <p>Admin Portal</p>
    </div>
    <?php if ($error_msg): ?>
    <div style="background:#f5e6e2;border-radius:10px;padding:11px 14px;color:#9a4a3a;font-size:.83rem;margin-bottom:14px;display:block;border:1px solid #e8c4bc;">
      <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
    </div>
    <?php endif; ?>
    <div class="tab-group">
      <button class="tab-btn active" onclick="switchAuthTab('login')">Sign In</button>
      <button class="tab-btn" onclick="switchAuthTab('signup')">Sign Up</button>
    </div>

    <form id="loginForm" class="auth-form active" action="../db/action/process_admin_login.php" method="POST">
      <div class="form-group">
        <label>Email</label>
        <input class="form-control" name="email" type="email" placeholder="admin@solis.com" required/>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-icon-wrap">
          <input class="form-control" id="loginPassword" name="password" type="password" placeholder="••••••••" required/>
          <i class="fas fa-eye" onclick="togglePw('loginPassword',this)"></i>
        </div>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Sign In</button>
    </form>

    <form id="signupForm" class="auth-form" action="../db/action/process_admin_signup.php" method="POST">
      <div class="form-group">
        <label>Full Name</label>
        <input class="form-control" name="full_name" placeholder="John Doe" required/>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input class="form-control" name="email" type="email" placeholder="your@email.com" required/>
      </div>
      <div class="form-group">
        <label>Password (8+ chars)</label>
        <div class="input-icon-wrap">
          <input class="form-control" id="signupPassword" name="password" type="password" placeholder="••••••••" minlength="8" required/>
          <i class="fas fa-eye" onclick="togglePw('signupPassword',this)"></i>
        </div>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Create Account</button>
    </form>

    <?php if ($success === 'signup'): ?>
    <div style="background:#e8f5e8;border-radius:10px;padding:11px 14px;color:#4a7c4a;font-size:.83rem;margin-bottom:14px;border:1px solid #c4e4c4;">
      Account created! You can now sign in.
    </div>
    <?php endif; ?>

    <div class="demo-creds"><b>Demo:</b> admin@solis.com / Solis@1</div>
  </div>
</div>

<script src="js/dashboard.js"></script>
</body>
</html>
