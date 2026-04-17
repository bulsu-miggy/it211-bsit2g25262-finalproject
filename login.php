<?php
session_start();
include 'db/connection.php';

// Auth Guard: Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: shop.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLIS | Login</title>

    <!-- 1. External Assets -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,600&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages/auth.css">
    
    <!-- jQuery and SweetAlert2 (Must be loaded before auth.js) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="auth-body">

    <?php include 'guest_header/guest_header.php'; ?>

    <main class="container auth-container">
        <section class="hero-side">
            <div class="hero-text">
                <h1>Welcome Back</h1>
                <p>Log in to access your curated collection of scents and track your recent orders.</p>
            </div>
            <img src="images/log_in.png" alt="Solis Candle Luxury" class="hero-img">
        </section>

        <section class="form-side">
            <div class="card">
                <h2>Login</h2>
                <p class="section-subtitle">Enter your credentials</p>
                
                <form id="loginForm" action="db/action/process_login.php" method="POST">
                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="email@example.com" autocomplete="email" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                    </div>
                    
                    <button type="submit" class="btn-main">Login to Account</button>
                </form>

                <div class="form-footer">
                    <a href="forgot_password.php" class="forgot-password-link">Forgot Password?</a>
                </div>
                <div class="form-footer">
                    Don't have an account? <a href="signup.php">Join Solis</a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'guest_header/guestfooter.php'; ?>

    <!-- External Auth Logic -->
    <script src="js/utils.js?v=<?php echo time(); ?>"></script>
    <script src="js/auth.js?v=<?php echo time(); ?>"></script>
    <script src="js/modals.js?v=<?php echo time(); ?>"></script>

</body>
</html>
