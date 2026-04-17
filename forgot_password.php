<?php 
  session_start();
  // Redirect if already logged in
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
  <title>LYNX | Reset Password</title>
  
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
      color: black;
      text-decoration: none;
    }

    .icons {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .icons .material-symbols-outlined {
      color: black;
      font-size: 28px;
    }

    /* MAIN CONTENT AREA */
    .main-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      background-color: #f9f9f9;
    }

    /* RESET CARD STYLES */
    .reset-container {
      background: white;
      padding: 60px 40px;
      width: 100%;
      max-width: 500px;
      border-radius: 25px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      text-align: center;
    }

    h2.reset-text {
      font-family: 'Rubik Mono One', sans-serif;
      font-size: 1.8rem;
      color: var(--primary-black);
      margin-bottom: 50px;
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .custom-input-group {
      position: relative;
      margin-bottom: 20px;
    }

    .custom-input-group input {
      background-color: var(--input-bg) !important;
      border: none !important;
      border-radius: 12px !important;
      padding: 12px 50px 12px 20px !important; /* Extra padding on right for eye icon */
      font-size: 0.95rem;
      font-weight: 500;
      color: var(--primary-black);
      height: 55px;
      width: 100%;
    }

    .custom-input-group input::placeholder {
      color: #888;
      text-transform: uppercase;
    }

    .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: var(--text-muted);
      z-index: 10;
      font-size: 22px;
    }

    /* BUTTON GROUP (Side-by-side) */
    .button-group {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 30px;
    }

    .btn-reset {
      border-radius: 35px;
      padding: 12px;
      width: 140px;
      font-weight: 700;
      font-size: 0.9rem;
      letter-spacing: 1px;
      text-transform: uppercase;
      transition: all 0.3s ease;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }

    .btn-discard {
      background-color: white;
      color: var(--primary-black);
      border: 2px solid var(--primary-black);
    }

    .btn-apply {
      background-color: var(--primary-black);
      color: white;
      border: 2px solid var(--primary-black);
    }

    .btn-reset:hover {
      opacity: 0.8;
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .header { padding: 20px; }
      .logo { font-size: 24px; }
      .button-group { flex-direction: column; align-items: center; }
      .btn-reset { width: 100%; }
    }
  </style>
</head>
<body>

  <header class="header">
    <a href="index.php" class="logo">LYNX</a>
    <div class="icons">
        <span class="material-symbols-outlined">search</span>
        <span class="material-symbols-outlined">shopping_cart</span>
        <a href="login.php" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">person</span>
        </a>
    </div>
  </header>

  <div class="main-wrapper">
    <div class="reset-container">
      <h2 class="reset-text">RESET PASSWORD</h2>

      <?php 
      if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger py-2 mb-4" style="font-size: 0.85rem;">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
      }
      ?>

      <form action="db/action/auth.php" method="post" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="reset_password">
        
        <div class="custom-input-group">
          <span class="material-symbols-outlined" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 22px; z-index: 10;">person</span>
          <input type="text" name="user_id" class="form-control" placeholder="USERNAME OR EMAIL" required style="padding-left: 50px !important;">
        </div>

        <div class="custom-input-group">
          <input type="password" name="old_password" id="old_password" class="form-control" placeholder="OLD PASSWORD" required>
          <span class="material-symbols-outlined toggle-password"></span>
        </div>

        <div class="custom-input-group">
          <input type="password" name="new_password" id="new_password" class="form-control" placeholder="NEW PASSWORD" required>
          <span class="material-symbols-outlined toggle-password"></span>
        </div>

        <div class="custom-input-group">
          <input type="password" name="confirm_new_password" id="confirm_new_password" class="form-control" placeholder="CONFIRM NEW PASSWORD" required>
          <span class="material-symbols-outlined toggle-password"></span>
        </div>

        <div class="button-group">
          <a href="login.php" class="btn-reset btn-discard">DISCARD</a>
          <button type="submit" class="btn-reset btn-apply">APPLY</button>
        </div>
      </form>

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

      // Password Toggle Visibility
      $('.toggle-password').click(function() {
        const input = $(this).siblings('input');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).text(type === 'password' ? 'visibility' : 'visibility_off');
      });
    });
  </script>
</body>
</html>