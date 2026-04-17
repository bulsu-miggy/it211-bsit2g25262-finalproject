<?php
session_start();
require "dbconfig.php";

// If user is already logged in, send them to homepage
if (isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $user_input = trim($_POST['user_input']);
    $pass = trim($_POST['password']);

    // Fetch the user by username or email
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$user_input, $user_input]);
    $user = $stmt->fetch();

    // Use password_verify() to check against a hashed password
    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: homepage.php");
        exit();
    } else {
        $error = "Invalid username/email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportify - Log In</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="auth-container">
        <img src="images/logo.png" alt="Sportify Logo" class="logo">
        <h1>Log in to your Sportify Account.</h1>

        <?php if ($error !== "") echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="user_input" placeholder="Username or Email*" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Password*" required>
            </div>

            <button type="submit" name="login" class="btn-dark">Log in</button>
        </form>

        <div class="footer-links">
            <p><a href="forgot.php">Forgot Password?</a></p>
            <p>Don't have an account? <a href="signup1.php">Sign Up</a></p>
        </div>
    </div>
</body>
</html>
