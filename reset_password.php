<?php
session_start();
include 'db/connection.php';

// Auth Guard: Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: shop.php');
    exit();
}

$token = trim($_GET['token'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLIS | Reset Password</title>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,600&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages/auth.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="auth-body">

    <?php include 'guest_header/guest_header.php'; ?>

    <main class="container auth-container auth-center">
        <section class="form-side">
            <div class="card">
                <h2>Reset Password</h2>
                <p class="section-subtitle">Enter your new password</p>

                <form id="resetPasswordForm" action="db/action/update_password.php" method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="input-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="new-password" required>
                    </div>

                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" autocomplete="new-password" required>
                    </div>

                    <p class="password-guidance">Your password must be 8 characters or more, and both fields must match.</p>

                    <button type="submit" class="btn-main">Update Password</button>
                </form>

                <div class="form-footer">
                    <a href="login.php">Back to Login</a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'guest_header/guestfooter.php'; ?>

    <script src="js/auth.js"></script>
</body>
</html>
