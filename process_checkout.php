<?php
session_start();
require_once 'db/connection.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: shopping-cart.php?error=empty');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['firstName'] ?? '');
    $last_name = trim($_POST['lastName'] ?? '');
    $customer_name = $first_name . ' ' . $last_name;
    
    if (empty($customer_name)) {
        header('Location: checkout.php?error=name');
        exit();
    }
    
    // Recalculate total from session cart (secure)
    $cart_items = $_SESSION['cart'];
    $items_total = 0;
    try {
        foreach ($cart_items as $item) {
            $table = $item['table'] ?? 'products'; // fallback
            $stmt = $conn->prepare("SELECT price FROM {$table} WHERE id = ?");
            $stmt->execute([$item['id']]);
            $price = $stmt->fetchColumn();
            if ($price) {
                $items_total += $price * $item['qty'];
            }
        }
        $vat = $items_total * 0.12;
        $total_amount = $items_total + $vat;
    } catch (PDOException $e) {
        error_log('Cart total error: ' . $e->getMessage());
        $total_amount = 0;
    }
    
    if ($total_amount > 0) {
            try {
                $stmt = $conn->prepare("INSERT INTO orders (customer_name, total_amount, status, order_date) VALUES (?, ?, 'pending', NOW())");
                $stmt->execute([$customer_name, $total_amount]);
                
                // Update stats table with new order totals
                $order_id = $conn->lastInsertId();
                $amount_stmt = $conn->prepare("SELECT total_amount FROM orders WHERE id = ?");
                $amount_stmt->execute([$order_id]);
                $new_amount = $amount_stmt->fetchColumn() ?: 0;
                
                $customers_query = $conn->query("SELECT COUNT(id) FROM login WHERE role = 'client'");
                $total_customers = $customers_query->fetchColumn();
                
                $stats_query = $conn->query("SELECT total_revenue, total_orders FROM stats WHERE id = 1");
                $current_stats = $stats_query->fetch(PDO::FETCH_ASSOC) ?: ['total_revenue' => 0, 'total_orders' => 0];
                
                $new_revenue = $current_stats['total_revenue'] + $new_amount;
                $new_orders = $current_stats['total_orders'] + 1;
                
                $stats_stmt = $conn->prepare("UPDATE stats SET total_revenue = ?, total_orders = ?, total_customers = ? WHERE id = 1");
                $stats_stmt->execute([$new_revenue, $new_orders, $total_customers]);
                
                unset($_SESSION['cart']); // Clear cart
                header('Location: confirmation.php?success=1&order_id=' . $order_id);
                exit();
            } catch (PDOException $e) {
                error_log('Order insert error: ' . $e->getMessage());
                header('Location: checkout.php?error=db');
                exit();
            }
    } else {
        header('Location: checkout.php?error=total');
        exit();
    }
} else {
    // Redirect to checkout if direct access
    header('Location: checkout.php');
    exit();
}
?>

