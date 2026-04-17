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
  <title>LYNX | Create Account</title>
  
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

    /* HEADER STYLES (Preserved) */
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
      padding: 60px 20px;
      background-color: #f9f9f9; /* Subtle contrast */
    }

    /* FORM CARD STYLES */
    .form-container {
      background: white;
      padding: 60px 40px;
      width: 100%;
      max-width: 500px;
      border-radius: 25px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      text-align: center;
    }

    h2.welcome-text {
      font-family: 'Rubik Mono One', sans-serif;
      font-size: 2.2rem;
      color: var(--primary-black);
      margin-bottom: 50px;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    /* Custom Input Group Styling (from image example) */
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

    /* Confirm Password Checkmark */
    .custom-input-group.success .material-symbols-outlined {
      color: #28a745; /* Success Green */
    }

    /* Sign Up Button (Match image proportion) */
    .btn-signup {
      background-color: var(--primary-black);
      color: white;
      border: none;
      border-radius: 35px;
      padding: 14px;
      width: 65%; /* Width matching screenshot */
      font-weight: 700;
      font-size: 1.1rem;
      letter-spacing: 1px;
      margin-top: 20px;
      margin-bottom: 30px;
      transition: all 0.3s ease;
    }

    .btn-signup:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    /* Footer Text */
    .signin-text {
      font-size: 0.9rem;
      color: var(--text-muted);
    }

    .signin-text a {
      color: var(--primary-black);
      text-decoration: none;
      font-weight: 700;
    }

    /* Bootstap Validation Styles (Subtle overrides) */
    .was-validated .form-control:valid,
    .form-control.is-valid {
      border-color: var(--input-bg);
      background-image: none; /* remove default green tick */
    }

    @media (max-width: 768px) {
      .header { padding: 20px; }
      .logo { font-size: 24px; }
      .form-container { padding: 40px 20px; }
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
    <div class="form-container">
      <h2 class="welcome-text">WELCOME</h2>

      <form action="db/action/auth.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="register">
        
        <div class="custom-input-group">
          <span class="material-symbols-outlined">person</span>
          <input type="text" name="first_name" id="first_name" class="form-control" placeholder="FIRST NAME" required>
        </div>

        <div class="custom-input-group">
          <span class="material-symbols-outlined">person</span>
          <input type="text" name="last_name" id="last_name" class="form-control" placeholder="LAST NAME" required>
        </div>

        <div class="custom-input-group">
          <span class="material-symbols-outlined">person_pin</span>
          <input type="text" name="username" id="username" class="form-control" placeholder="USERNAME" required>
        </div>

        <div class="custom-input-group">
          <span class="material-symbols-outlined">email</span>
          <input type="email" name="email" id="email" class="form-control" placeholder="EMAIL" required>
        </div>

        <div class="custom-input-group">
          <span class="material-symbols-outlined">lock</span>
          <input type="password" name="password" id="password" class="form-control" placeholder="PASSWORD" required>
        </div>

        <div class="custom-input-group" id="cpasswordGroup">
          <span class="material-symbols-outlined" id="cpasswordIcon">lock</span>
          <input type="password" name="cpassword" id="cpassword" class="form-control" placeholder="CONFIRM PASSWORD" required>
        </div>

        <button type="submit" name="submit" class="btn-signup">SIGN UP</button>
      </form>

      <p class="signin-text">
        Already have an account? <a href="login.php">Sign In</a>
      </p>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(document).ready(function() {
      // Bootstrap Validation
      $('form.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        $(this).addClass('was-validated');
      });

      // Simple password matching check (for UI green tick)
      $('#cpassword').on('keyup', function () {
          if ($('#password').val() == $('#cpassword').val()) {
              $('#cpasswordGroup').addClass('success');
              $('#cpasswordIcon').text('done');
          } else {
              $('#cpasswordGroup').removeClass('success');
              $('#cpasswordIcon').text('lock');
          }
      });
    });
  </script>
</body>
</html>