<?php
session_start();

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
    <title>SOLIS | Sign Up</title>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,600&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages/auth.css">
</head>
<body class="auth-body">

    <?php include 'guest_header/guest_header.php'; ?>

    <main class="container auth-container">
        <section class="hero-side">
            <div class="hero-text">
                <h1>Join the Circle</h1>
                <p>Create an account to track your artisanal candle collections and receive early access to new releases.</p>
            </div>
            <img src="images/sign_up.png" alt="Solis Candle Set" class="hero-img">
        </section>

        <section class="form-side">
            <div class="card">
                <h2>Sign Up</h2>
                <p class="section-subtitle">LUMINOUS CIRCLE REGISTRATION</p>
                
                <form id="signupForm" action="db/action/process_signup.php" method="POST">
                    <div class="input-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" placeholder="Your name">
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="email@example.com">
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••">
                    </div>

                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••">
                    </div>
                    
                    <button type="submit" class="btn-main">Create Account</button>
                </form>

                <div class="form-footer">
                    Already a member? <a href="login.php">Log in here</a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'guest_header/guestfooter.php'; ?>

    <script src="js/auth.js"></script>

</body>
</html>
    