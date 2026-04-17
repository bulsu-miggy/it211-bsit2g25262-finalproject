<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['cart']);
    $_SESSION['cart_success'] = 'Cart cleared successfully';
}

header('Location: cart.php');
exit();
?>