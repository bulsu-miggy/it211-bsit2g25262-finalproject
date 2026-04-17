<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$applied_voucher = isset($_POST['applied_voucher']) ? $_POST['applied_voucher'] : "";
$final_total = isset($_POST['final_total']) ? $_POST['final_total'] : 0;

// 1. SAVE TO ORDERS TABLE
$status = "pending"; 
$order_query = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)";
$stmt_order = $conn->prepare($order_query);
$stmt_order->bind_param("ids", $user_id, $final_total, $status);
$stmt_order->execute();
$db_order_id = $stmt_order->insert_id; 
$stmt_order->close();

// 2. SAVE INDIVIDUAL ITEMS & UPDATE STOCK
$cart_items_query = "SELECT product_name, product_price, quantity FROM cart WHERE user_id = '$user_id'";
$cart_result = mysqli_query($conn, $cart_items_query);

if ($cart_result && mysqli_num_rows($cart_result) > 0) {
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
    
    // Inihanda natin ang SQL para sa pagbabawas ng stock
    $update_stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE name = ?");

    while ($item = mysqli_fetch_assoc($cart_result)) {
        $p_name = $item['product_name'];
        $p_qty = $item['quantity'];
        $p_price = $item['product_price'];

        // Save sa order_items table
        $item_stmt->bind_param("isdi", $db_order_id, $p_name, $p_price, $p_qty);
        $item_stmt->execute();

        // UPDATE STOCK: Binabawasan ang stock sa products table base sa product_name
        $update_stock_stmt->bind_param("is", $p_qty, $p_name);
        $update_stock_stmt->execute();
    }
    $item_stmt->close();
    $update_stock_stmt->close();
}

// 3. Mark the voucher as used
if (!empty($applied_voucher)) {
    $update_stmt = $conn->prepare("UPDATE user_vouchers SET is_used = 1 WHERE user_id = ? AND voucher_title = ?");
    $update_stmt->bind_param("is", $user_id, $applied_voucher);
    $update_stmt->execute();
    $update_stmt->close();
}

// 4. Clear user's cart
mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'");

// Setup Display
$order_display_id = "SPK-" . str_pad($db_order_id, 5, "0", STR_PAD_LEFT);
$receiver_name = isset($_SESSION['shipping_name']) ? $_SESSION['shipping_name'] : "Valued Customer";
$phone = isset($_SESSION['shipping_phone']) ? $_SESSION['shipping_phone'] : "Not Provided";
$address = isset($_SESSION['shipping_address']) ? $_SESSION['shipping_address'] : "No Address Provided";
$min_delivery = date("F j, Y", strtotime("+3 days"));
$max_delivery = date("F j, Y", strtotime("+5 days"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Order Confirmed</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fdfdfd; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; text-align: center; }
        .confirmation-card { background: white; padding: 50px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; max-width: 600px; width: 90%; margin: 20px;}
        .success-icon { font-size: 80px; color: #00B4D8; margin-bottom: 20px; }
        h1 { color: #00B4D8; margin-bottom: 10px; font-size: 28px; }
        .order-details { background: #f9f9f9; padding: 20px; border-radius: 15px; margin: 30px 0; text-align: left; border-left: 5px solid #00B4D8; }
        .detail-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; border-bottom: 1px solid #f0f0f0; padding-bottom: 8px; }
        .total-pay { border-top: 2px dashed #00B4D8; margin-top: 15px; padding-top: 15px; font-size: 18px; color: #00B4D8; }
        .delivery-box { background: #e0f7fa; color: #007c91; padding: 15px; border-radius: 10px; font-weight: bold; margin-top: 20px; text-align: center; display: block; }
        .btn-home { display: inline-block; background: #00B4D8; color: white; padding: 15px 40px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-top: 30px; transition: 0.3s; }
        .btn-home:hover { background: #007791; transform: translateY(-3px); }
    </style>
</head>
<body>
<div class="confirmation-card">
    <div class="success-icon">✔</div>
    <h1>Thank you, <?php echo htmlspecialchars($receiver_name); ?>!</h1>
    <p>Your order has been placed successfully. Please prepare the payment for our rider.</p>
    <div class="order-details">
        <div class="detail-item"><span>Order ID:</span><strong><?php echo $order_display_id; ?></strong></div>
        <div class="detail-item"><span>Contact:</span><strong><?php echo htmlspecialchars($phone); ?></strong></div>
        <div class="detail-item">
            <span>Shipping To:</span>
            <strong style="text-align: right; max-width: 250px;"><?php echo htmlspecialchars($address); ?></strong>
        </div>
        <div class="detail-item">
            <span>Payment Method:</span>
            <strong>Cash on Delivery (COD)</strong>
        </div>

        <div class="detail-item"><span>Voucher Applied:</span><strong><?php echo !empty($applied_voucher) ? htmlspecialchars($applied_voucher) : 'None'; ?></strong></div>
        <div class="detail-item total-pay">
            <span>TOTAL AMOUNT TO PAY:</span>
            <strong>₱ <?php echo number_format($final_total, 2); ?></strong>
        </div>
        <div class="delivery-box">🚚 Estimated Delivery: <?php echo $min_delivery; ?> - <?php echo $max_delivery; ?></div>
    </div>
    <a href="home.php" class="btn-home">Back to Home</a>
</div>
</body>
</html>