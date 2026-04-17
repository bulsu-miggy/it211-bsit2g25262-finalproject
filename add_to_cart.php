<?php
session_start();

// Check if product data was sent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Support both old format (size/img) and new format (product_id)
    $product_id = $_POST['product_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    // If product_id exists, use new format; otherwise fallback to old format
    if (!$product_id) {
        // Legacy support for old size/img format
        $size = $_POST['size'] ?? '';
        $img = $_POST['img'] ?? '';
        
        if (empty($size) || empty($img) || empty($name) || empty($price)) {
            $_SESSION['cart_error'] = 'Missing product information';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
        
        $product_id = $size . '_' . $img;
    }
    
    // Validate required fields
    if (empty($product_id) || empty($name) || empty($price)) {
        $_SESSION['cart_error'] = 'Missing product information';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add or update product in cart
    if (isset($_SESSION['cart'][$product_id])) {
        // Update quantity
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        // Add new product
        $_SESSION['cart'][$product_id] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity
        ];
    }
    
    $_SESSION['cart_success'] = 'Item added to cart!';
    header('Location: cart.php');
    exit();
}

// If not POST request, redirect back
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>