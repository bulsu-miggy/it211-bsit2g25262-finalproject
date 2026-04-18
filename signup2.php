<?php
session_start();
require "connect.php";

$email = $_SESSION['temp_email'] ?? '';

if (isset($_POST['register'])) {

    $first = $_POST['first'];
    $last = $_POST['last'];
    $fullname = $first . " " . $last;

    $username = $_POST['username'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    $created_at = date("Y-m-d H:i:s");

    // check email session
    if (!isset($_SESSION['temp_email']) || empty($_SESSION['temp_email'])) {
        $error = "Session expired. Please go back and enter email again.";
    } else {

        $email = $_SESSION['temp_email'];

        if ($password !== $cpassword) {
            $error = "Passwords do not match!";
        } else {

            try {

                // check email separately
                $checkEmail = $conn->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
                $checkEmail->execute([$email]);

                if ($checkEmail->fetchColumn() > 0) {
                    $error = "Email already exists!";
                }

                // check username separately
                $checkUser = $conn->prepare("SELECT COUNT(*) FROM customers WHERE username = ?");
                $checkUser->execute([$username]);

                
                if ($checkUser->fetchColumn() > 0) {
                    $error = "Username already exists!";
                }

                // only insert if no errors
                if (!isset($error)) {

                    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("
                        INSERT INTO customers (
                            username,
                            fullname,
                            email,
                            password,
                            created_at
                        ) VALUES (?,?,?,?,?)
                    ");

                    $stmt->execute([
                        $username,
                        $fullname,
                        $email,
                        $pass_hash,
                        $created_at
                    ]);

                    unset($_SESSION['temp_email']);

                    echo '<script>
                            alert("Account Created!");
                            window.location.href="login.php";
                          </script>';
                }

            } catch (PDOException $e) {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sportify - Register</title>
     <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="auth-container">
        <img src="image/logo.jpg" class="logo">
        <h1>Create your Sportify Account</h1>

        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

        <form method="POST">


            <div class="form-group row">
                <div class="col">
                    <input type="text" name="first" placeholder="First Name*" required>
                </div>
                <div class="col">
                    <input type="text" name="last" placeholder="Last Name*" required>
                </div>
            </div>

            <div class="form-group">
                <input type="text" name="username" placeholder="Username*" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Password*" required>
            </div>

            <div class="form-group">
                <input type="password" name="cpassword" placeholder="Confirm Password*" required>
            </div>

            <button type="submit" name="register" class="btn-dark">
                Create Account
            </button>

        </form>
    
    </div>
</body>
</html>