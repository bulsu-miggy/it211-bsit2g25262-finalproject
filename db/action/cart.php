<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["username"])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $product_id = $_POST['product_id'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    $size = $_POST['size'] ?? 'M';

    if (empty($product_id)) {
        echo json_encode(['success' => false, 'message' => 'Product ID is missing']);
        exit;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $cart_key = $product_id . "_" . $size;

    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'size' => $size
        ];
    }

    echo json_encode(['success' => true, 'message' => 'Added to bag']);
    exit;

} else if ($action === 'remove') {
    $cart_key = $_POST['cart_key'] ?? '';

    if (isset($_SESSION['cart'][$cart_key])) {
        unset($_SESSION['cart'][$cart_key]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
    exit;
}