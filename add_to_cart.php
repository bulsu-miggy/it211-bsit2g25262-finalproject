<?php
session_start();
include 'db.php';

if (isset($_POST['add_to_cart'])) {
    // Siguraduhin na may naka-login na user
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price = $_POST['product_price'];
    $image = mysqli_real_escape_string($conn, $_POST['product_image']);

    // Check kung nandoon na ang item para quantity na lang ang dagdagan
    $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND product_name = '$name'");
    
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = '$user_id' AND product_name = '$name'");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, product_name, product_price, product_image, quantity) 
                            VALUES ('$user_id', '$name', '$price', '$image', 1)");
    }

    // Balik sa home na may success alert
    header("Location: home.php?status=success");
}
?>