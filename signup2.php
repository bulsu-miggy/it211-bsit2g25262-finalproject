<?php
session_start();
require "dbconfig.php";

// If no email from step 1, send back
if (empty($_SESSION['temp_email'])) {
    header("Location: signup1.php");
    exit();
}

$email = $_SESSION['temp_email'];
$error = "";

if (isset($_POST['register'])) {
    $first    = trim($_POST['first']);
    $last     = trim($_POST['last']);
    $username = trim($_POST['username']);
    $pass     = $_POST['password'];
    $cpass    = $_POST['cpassword'];
    $birthday = trim($_POST['month']) . " " . trim($_POST['day']) . ", " . trim($_POST['year']);
    $phone    = trim($_POST['phone']);

    if ($pass !== $cpass) {
        $error = "Passwords do not match!";
    } elseif (strlen($pass) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Use password_hash for secure password storage
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password, birthday, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first, $last, $username, $email, $pass_hash, $birthday, $phone]);

            // Clean up temp session
            unset($_SESSION['temp_email']);

            // Redirect cleanly instead of using inline JS alert
            $_SESSION['signup_success'] = true;
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            // Check for duplicate username
            if ($e->getCode() == 23000) {
                $error = "Username is already taken. Please choose another one.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportify - Register</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="auth-container">
        <img src="images/logo.png" alt="Sportify Logo" class="logo">
        <h1>Now let's make you a Sportify Member.</h1>

        <?php if ($error !== "") echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>

        <form method="POST">
            <div class="form-group row">
                <div class="col"><input type="text" name="first" placeholder="First Name*" required></div>
                <div class="col"><input type="text" name="last" placeholder="Last Name*" required></div>
            </div>

            <div class="form-group">
                <input type="text" name="username" placeholder="Username*" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Password*" minlength="8" required>
                <span class="instructions">Minimum of 8 characters. Uppercase, lowercase letters, and one number.</span>
            </div>

            <div class="form-group">
                <input type="password" name="cpassword" placeholder="Confirm Password*" required>
            </div>

            <div class="form-group row">
                <div class="col"><input type="text" name="month" placeholder="Month*" required></div>
                <div class="col"><input type="text" name="day" placeholder="Day*" required></div>
                <div class="col"><input type="text" name="year" placeholder="Year*" required></div>
            </div>

            <div class="form-group">
                <input type="tel" name="phone" placeholder="Phone No.*" required>
            </div>

            <div class="form-group">
                <label class="checkbox-container">
                    <input type="checkbox" required>
                    <span class="legal-text" style="margin:0;">I agree to Sportify's <a href="#">Privacy Policy</a> and <a href="#">Terms of Use</a>.</span>
                </label>
            </div>

            <button type="submit" name="register" class="btn-dark">Create Account</button>
        </form>
    </div>
</body>
</html>
