<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        $_SESSION['success'] = 'Product deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete product.';
    }
} else {
    $_SESSION['error'] = 'Invalid product ID.';
}

header('Location: ../../dashboard/products.php');
exit;
?>
