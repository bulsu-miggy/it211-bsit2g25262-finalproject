<?php
session_start();
if (isset($_POST['continue'])) {
    $_SESSION['temp_email'] = $_POST['email'];
    header("Location: signup2.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sportify - Join Us</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="auth-container">
        <img src="image/logo.jpg" alt="Sportify Logo" class="logo">
        <h1>Enter your email to join us or sign in.</h1>
        
        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email*" required>
            </div>
            
            <p class="legal-text">
                By continuing, I agree to Sportify's <a href="#">Privacy Policy</a> and <a href="#">Terms of Use</a>.
            </p>
            
            <button type="submit" name="continue" class="btn-dark">Continue</button>
        </form>
        
        <div class="footer-links">
            <p>Already have an account? <a href="login.php">Log In</a></p>
        </div>
    </div>
</body>
</html>