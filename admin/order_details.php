<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php'); 

// Kunin ang Order ID mula sa URL
if(isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // 1. Kunin ang Customer at Order Info
    $order_query = "SELECT orders.*, users.name as customer_name, users.email 
                    FROM orders 
                    JOIN users ON orders.user_id = users.id 
                    WHERE orders.id = '$order_id'";
    $order_result = mysqli_query($conn, $order_query);
    $order_data = mysqli_fetch_assoc($order_result);

    // 2. Kunin ang mga items sa order na ito
    $items_query = "SELECT * FROM order_items WHERE order_id = '$order_id'";
    $items_result = mysqli_query($conn, $items_query);
} else {
    header("Location: orders.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details | #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary-yellow: #facc15; --dark-sidebar: #1e293b; --bg-light: #f8fafc; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; display: flex; }
        .sidebar { width: 260px; background: var(--dark-sidebar); height: 100vh; position: fixed; color: white; padding: 20px 0; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; }
        
        .detail-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .back-btn { text-decoration: none; color: #64748b; font-weight: 600; margin-bottom: 20px; display: inline-block; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box { background: #f1f5f9; padding: 20px; border-radius: 10px; }
        .info-box h4 { margin: 0 0 10px 0; color: #475569; font-size: 12px; text-transform: uppercase; }
        .info-box p { margin: 0; font-weight: 700; color: #1e293b; }

        .item-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .item-table th { text-align: left; padding: 15px; background: #f8fafc; color: #64748b; font-size: 13px; }
        .item-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; }
        
        .status-pill { padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .pending { background: #fef9c3; color: #a16207; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div style="text-align:center; padding: 20px;">
            <h3 style="color: var(--primary-yellow);">SPARKVERSE</h3>
        </div>
    </aside>

    <div class="main-content">
        <a href="orders.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Orders</a>
        
        <div class="header-section" style="margin-bottom: 30px;">
            <h1 style="font-weight: 800; font-size: 28px;">Order Details #ORD-<?php echo $order_id; ?></h1>
            <span class="status-pill pending"><?php echo $order_data['status']; ?></span>
        </div>

        <div class="detail-card">
            <div class="info-grid">
                <div class="info-box">
                    <h4>Customer Information</h4>
                    <p><?php echo $order_data['customer_name']; ?></p>
                    <small style="color: #64748b;"><?php echo $order_data['email']; ?></small>
                </div>
                <div class="info-box">
                    <h4>Order Summary</h4>
                    <p>Total Amount: ₱<?php echo number_format($order_data['total_amount'], 2); ?></p>
                    <small style="color: #64748b;">Ordered on: <?php echo date('M d, Y', strtotime($order_data['created_at'])); ?></small>
                </div>
            </div>

            <h3 style="font-size: 18px; margin-bottom: 15px;">Items Ordered</h3>
            <table class="item-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $item['product_name']; ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td>x<?php echo $item['quantity']; ?></td>
                        <td style="font-weight: 700;">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>