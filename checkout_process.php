<?php
session_start();
require "connect.php";

// 1. Security Check
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: homepage.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];
$date_today = date("Y-m-d");

try {
    // Start a Transaction (This ensures if one update fails, none of them happen)
    $conn->beginTransaction();

    foreach ($cart as $product_id => $quantity) {
        
        // 🔍 2. Check Stock First
        $stmt = $conn->prepare("SELECT product, price, stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

     
        if (!$product || $product['stock'] < $quantity) {
            throw new Exception("Insufficient stock for: " . ($product['product'] ?? "Unknown Item"));
        }

        $total_price = $product['price'] * $quantity;

        // 3. Insert into Orders Table
        // Note: I'm using the columns found in your connection.php: customerid, productid, date, amount, status
        $sqlOrder = "INSERT INTO orders (customerid, productid, date, amount, status) 
                     VALUES (?, ?, ?, ?, 'pending')";

        $orderStmt = $conn->prepare($sqlOrder);
        $orderStmt->execute([$user_id, $product_id, $date_today, $total_price]);

        //  4. Update Stock in Products Table
        $new_stock = $product['stock'] - $quantity;
        $stock_status = ($new_stock <= 5) ? 'Low Stock' : 'In Stock'; // Auto-update badge logic
        
        $updateStock = "UPDATE products SET stock = ?, stock_status = ? WHERE id = ?";
        
        $stockStmt = $conn->prepare($updateStock);
        $stockStmt->execute([$new_stock, $stock_status, $product_id]);
    }

    // 5. Commit all changes to DB
    $conn->commit();

    // 6. Clear Cart
    unset($_SESSION['cart']);

    // Redirect to success page
    header("Location: checkout_success.php");
    exit();

} catch (Exception $e) {
    // ❌ Rollback if anything went wrong
    $conn->rollBack();
    die("Checkout Error: " . $e->getMessage());
}