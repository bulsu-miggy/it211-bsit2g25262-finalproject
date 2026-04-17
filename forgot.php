<?php
session_start();
require "dbconfig.php";

$error = "";
$success = "";

if (isset($_POST['reset'])) {
    $username  = trim($_POST['username']);
    $new_pass  = $_POST['new_pass'];
    $conf_pass = $_POST['confirm_pass'];

    if ($new_pass !== $conf_pass) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_pass) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            // Use password_hash for secure password storage
            $pass_hash = password_hash($new_pass, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update->execute([$pass_hash, $username]);

            $success = "Password updated! You can now log in.";
        } else {
            $error = "Username not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportify - Reset Password</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="auth-container">
        <img src="images/logo.png" alt="Sportify Logo" class="logo">
        <h1>Reset your Sportify Password.</h1>

        <?php if ($error !== "")   echo "<p style='color:red;'>"   . htmlspecialchars($error)   . "</p>"; ?>
        <?php if ($success !== "") echo "<p style='color:green;'>" . htmlspecialchars($success) . "</p>"; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username*" required>
            </div>

            <div class="form-group">
                <input type="password" name="new_pass" placeholder="New Password*" minlength="8" required>
            </div>

            <div class="form-group">
                <input type="password" name="confirm_pass" placeholder="Re-enter Password*" required>
            </div>

            <button type="submit" name="reset" class="btn-dark">Confirm</button>
        </form>

        <div class="footer-links">
            <p><a href="login.php">Back to Log In</a></p>
        </div>
    </div>
</body>
</html>
