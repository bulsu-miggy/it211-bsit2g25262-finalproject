<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Success - Sportify</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-card { text-align: center; margin-top: 100px; font-family: 'Montserrat', sans-serif; }
        .success-icon { font-size: 60px; color: #28a745; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">✔️</div>
        <h1>Order Placed Successfully!</h1>
        
        <p>Your gear is being prepared for delivery.</p>
        <br>
        <a href="homepage.php" class="btn-dark" style="text-decoration:none; padding:10px 20px;">Back to Shop</a>
    </div>
</body>
</html>