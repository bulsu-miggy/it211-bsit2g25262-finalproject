<?php
session_start(); // Dapat Line 1 ito palagi!
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    // 2. I-check kung may nahanap na email
    if(mysqli_num_rows($result) == 1){
        $row = mysqli_fetch_assoc($result); // DITO natin kukunin yung data ni user

        // 3. I-check kung tama yung password
        if(password_verify($password, $row['password'])){
            // SUCCESS! 
            $_SESSION['user_id'] = $row['id']; 
            $_SESSION['user_name'] = $row['name']; // Para magamit natin name niya sa home
            
            echo "<script>alert('Login Success! Welcome back, " . $row['name'] . "'); window.location='home.php';</script>";
        } else {
            // Maling password
            echo "<script>alert('Incorrect password. Please try again.'); window.history.back();</script>";
        }
    } else {
        // Hindi mahanap yung email
        echo "<script>alert('Email address not found. Please check and try again.'); window.location='register.html';</script>";
    }
}
?>