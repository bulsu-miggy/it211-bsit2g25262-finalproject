<?php
/**
 * ==========================================
 * PROCESS ORDER - ORDER FINALIZATION
 * ==========================================
 * 
 * Purpose: Create order record in database after checkout
 * 
 * Process:
 * 1. Verify user is logged in
 * 2. Verify request is POST (from checkout form)
 * 3. Calculate order total from cart items
 * 4. Insert order record into orders table
 * 5. Insert individual items into order_items table
 * 6. Clear shopping cart after successful order
 * 7. Display success message with order ID
 * 
 * Access: Logged-in customers only
 * Called From: checkout.php (form submission)
 */

// Start session to access cart and user data
session_start();

// Include authentication functions
require_once 'auth.php';

// Ensure only logged-in users can process orders
requireLogin();

// Include database connection
require_once 'db/connection.php';

// ==========================================
// REQUEST VALIDATION
// ==========================================
// Ensure this script is only accessed via POST (form submission)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Invalid request method - redirect to checkout
    header('Location: checkout.php');
    exit();
}

// ==========================================
// GATHER ORDER DATA
// ==========================================
// Extract and validate POST data from checkout form

// Get logged-in user's ID from session
$user_id = $_SESSION['user_id'];

// Payment method selected by user (default: Cash on Delivery)
$payment_method = $_POST['payment_method'] ?? 'COD';

// Optional message from buyer to seller
$seller_message = trim($_POST['seller_message'] ?? '');

// Shipping fee from checkout form
$shipping_fee = floatval($_POST['shipping_fee'] ?? 50);

// ==========================================
// CALCULATE ORDER TOTAL
// ==========================================
// Sum up all cart items and prepare for database insertion

// Initialize subtotal accumulator
$subtotal = 0;

// Array to store prepared cart items for order_items table
$cart_items = [];

// Loop through cart and calculate subtotal
foreach ($_SESSION['cart'] as $product_id => $item) {
    // Extract and validate item price
    $price = floatval($item['price']);
    
    // Extract and validate item quantity
    $quantity = intval($item['quantity']);
    
    // Add to running subtotal
    $subtotal += $price * $quantity;
    
    // Store item data for order_items insertion
    $cart_items[] = [
        'name' => $item['name'],
        'size' => $item['size'],
        'quantity' => $quantity,
        'price' => $price
    ];
}

// Calculate grand total (subtotal + shipping fee)
$total_amount = $subtotal + $shipping_fee;

try {
    // ==========================================
    // INSERT ORDER INTO DATABASE
    // ==========================================
    // Create new order record in orders table
    
    // Prepare INSERT statement with parameterized query (prevents SQL injection)
    $order_stmt = $conn->prepare('INSERT INTO orders (user_id, total_amount, shipping_fee, payment_method, seller_message) VALUES (?, ?, ?, ?, ?)');
    
    // Execute with provided values
    $order_stmt->execute([$user_id, $total_amount, $shipping_fee, $payment_method, $seller_message]);
    
    // Get the newly created order's ID
    $order_id = $conn->lastInsertId();

    // ==========================================
    // INSERT ORDER ITEMS INTO DATABASE
    // ==========================================
    // Add each cart item as a separate record in order_items table
    
    // Prepare INSERT statement for order items
    $item_stmt = $conn->prepare('INSERT INTO order_items (order_id, product_name, size, quantity, price) VALUES (?, ?, ?, ?, ?)');
    
    // Loop through cart items and insert each one
    foreach ($cart_items as $item) {
        $item_stmt->execute([$order_id, $item['name'], $item['size'], $item['quantity'], $item['price']]);
    }

    // ==========================================
    // FINALIZE ORDER
    // ==========================================
    // Clear cart and set success message

    // Empty the shopping cart session variable
    unset($_SESSION['cart']);

    // Set success message with order confirmation
    $_SESSION['order_success'] = 'Order placed successfully! Order ID: ' . $order_id;
    header('Location: index.php');
    exit();
} catch (PDOException $e) {
    $_SESSION['order_error'] = 'Failed to place order: ' . $e->getMessage();
    header('Location: checkout.php');
    exit();
}
?>