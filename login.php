<?php 
  session_start();
  if (isset($_SESSION["username"]) && isset($_SESSION["password"]))
  {
    header('Location: index.php');
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LYNX | Sign In</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&family=Rubik+Mono+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --primary-black: #000000;
      --input-bg: #ececec;
      --text-muted: #6c757d;
    }

    body {
      background-color: #ffffff;
      font-family: 'Rubik', sans-serif;
      margin: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* HEADER STYLES */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 80px;
      background: white;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .logo {
      font-family: 'Rubik Mono One', sans-serif;
      font-size: 32px;
      margin: 0;
    }

    .icons {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .icons .material-symbols-outlined {
      color: black;
      font-size: 28px;
      cursor: pointer;
    }

    /* MAIN CONTENT AREA */
    .main-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      background-color: #f9f9f9; /* Subtle contrast for the card */
    }

    /* LOGIN CARD STYLES */
    .login-container {
      background: white;
      padding: 60px 40px;
      width: 100%;
      max-width: 480px;
      border-radius: 25px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      text-align: center;
    }

    h2.welcome-text {
      font-family: 'Rubik Mono One', sans-serif;
      font-size: 2.2rem;
      color: var(--primary-black);
      margin-bottom: 45px;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .custom-input-group {
      position: relative;
      margin-bottom: 15px;
    }

    .custom-input-group .material-symbols-outlined {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 22px;
      z-index: 10;
    }

    .custom-input-group input {
      background-color: var(--input-bg) !important;
      border: none !important;
      border-radius: 12px !important;
      padding: 12px 15px 12px 50px !important;
      font-size: 0.95rem;
      font-weight: 500;
      color: var(--primary-black);
      height: 55px;
    }

    .custom-input-group input::placeholder {
      color: #888;
      text-transform: uppercase;
    }

    .toggle-password {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      right: 20px;
      cursor: pointer;
      color: var(--text-muted);
      z-index: 10;
      font-size: 20px;
    }

    .forgot-link {
      display: block;
      text-align: right;
      font-size: 0.85rem;
      color: var(--text-muted);
      text-decoration: none;
      margin-bottom: 35px;
      margin-top: -5px;
    }

    .btn-signin {
      background-color: var(--primary-black);
      color: white;
      border: none;
      border-radius: 35px;
      padding: 14px;
      width: 65%;
      font-weight: 700;
      font-size: 1.1rem;
      letter-spacing: 1px;
      margin-bottom: 25px;
      transition: all 0.3s ease;
    }

    .btn-signin:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .signup-text {
      font-size: 0.9rem;
      color: var(--text-muted);
    }

    .signup-text a {
      color: var(--primary-black);
      text-decoration: none;
      font-weight: 700;
    }

    @media (max-width: 768px) {
      .header { padding: 20px; }
      .logo { font-size: 24px; }
    }
  </style>
</head>
<body>

  <header class="header">
    <a href="index.php" style="text-decoration: none; color: black;">
      <h1 class="logo">LYNX</h1>
    </a>
    <div class="icons">
        <span class="material-symbols-outlined">search</span>
        <span class="material-symbols-outlined">shopping_cart</span>
        <a href="login.php" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">person</span>
        </a>
    </div>
  </header>

  <div class="main-wrapper">
    <div class="login-container">
      <h2 class="welcome-text">WELCOME BACK</h2>

      <?php 
      if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger py-2 mb-4" style="font-size: 0.85rem;">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
      }
      ?>

      <form action="db/action/auth.php" method="post" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="login">
        
        <div class="custom-input-group">
          <span class="material-symbols-outlined">person</span>
          <input type="text" name="username" id="username" class="form-control" placeholder="USERNAME OR EMAIL" required>
        </div>

        <div class="custom-input-group">
          <span class="material-symbols-outlined">lock</span>
          <input type="password" name="password" id="password" class="form-control" placeholder="PASSWORD" required>
          <span class="material-symbols-outlined toggle-password" id="togglePassword"></span>
        </div>

        <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>

        <button type="submit" class="btn-signin">SIGN IN</button>
      </form>

      <p class="signup-text">
        Don't have an account? <a href="register.php">Sign Up</a>
      </p>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(document).ready(function() {
      // Form Validation
      $('form.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        $(this).addClass('was-validated');
      });

      // Password Toggle
      $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        $(this).text(type === 'password' ? 'visibility' : 'visibility_off');
      });
    });
  </script>
</body>
</html>