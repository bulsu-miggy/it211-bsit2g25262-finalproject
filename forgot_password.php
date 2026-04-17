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
    <title>SOLIS | Forgot Password</title>

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

    <main class="container auth-container auth-center">
        <section class="form-side">
            <div class="card">
                <h2>Forgot Password</h2>
                <p class="section-subtitle">Enter the email for your Solis account</p>

                <form id="forgotPasswordForm" action="db/action/process_forgot_password.php" method="POST">
                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="email@example.com" autocomplete="email" required>
                    </div>

                    <button type="submit" class="btn-main">Send Reset Link</button>
                </form>

                <div class="form-footer">
                    Remembered your password? <a href="login.php">Login here</a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'guest_header/guestfooter.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/auth.js"></script>
</body>
</html>
