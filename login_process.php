<?php
include 'db.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        echo "<h1>Welcome back, " . $user['name'] . "! Login successful! Let’s get started.</h1>";
    } else {
        echo "<script>alert('Authentication failed. Please check your password and try again.'); window.history.back();</script>";
    }
}
?>