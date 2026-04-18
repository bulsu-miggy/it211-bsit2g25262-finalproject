<?php
session_start();
require "connect.php";

if (isset($_POST['login'])) {

    $user_input = $_POST['user_input'];
    $password = $_POST['password'];

    try {

        //  get user by username OR email
        $stmt = $conn->prepare("
            SELECT * 
            FROM customers 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$user_input, $user_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        //  verify password (matches password_hash from signup)
        if ($user && password_verify($password, $user['password'])) {

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    header("Location: homepage.php");
    exit();

} else {
    $error = "Invalid credentials!";
}

    } catch (PDOException $e) {
        $error = "Login error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sportify - Log In</title>
     <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="auth-container">
        <img src="image/logo.jpg" alt="Sportify Logo" class="logo">
        <h1>Log in to your Sportify Account.</h1>

        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

        <form method="POST">

           
        <div class="form-group">
                <input type="text"
                       name="user_input"
                       placeholder="Username or Email*"
                       required>
            </div>

            <div class="form-group">
               
            <input type="password"
                       name="password"
                       placeholder="Password*"
                       required>
            </div>

            <button type="submit" name="login" class="btn-dark">
                Log in
            </button>

        </form>

        <div class="footer-links">
            <p><a href="forgot.php">Forgot Password?</a></p>
           
            <p>Don't have an account? <a href="signup1.php">Sign Up</a></p>
        </div>
    </div>
</body>
</html>