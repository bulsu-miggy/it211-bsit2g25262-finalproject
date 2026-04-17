<?php
session_start();
require "dbconfig.php";

// If user is already logged in, send them to homepage
if (isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit();
}

$error = "";

if (isset($_POST['continue'])) {
    $email = trim($_POST['email']);

    // Check if email is already registered
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        $error = "This email is already registered. Please log in instead.";
    } else {
        $_SESSION['temp_email'] = $email;
        header("Location: signup2.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportify - Join Us</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="auth-container">
        <img src="images/logo.png" alt="Sportify Logo" class="logo">
        <h1>Enter your email to join us or sign in.</h1>

        <?php if ($error !== "") echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>

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
