<?php
session_start();
require "connect.php"; //

// Protect page - only logged-in users can view
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

$display_name = $_SESSION['username'] ?? "Admin User";

/* FETCH REAL ORDERS FROM DATABASE
   We join 'orders' with 'customers' to get the buyer's name
   and 'products' to get the product name.
*/
try {
    $query = "SELECT 
                o.orderid, 
                c.fullname as customer_name, 
                p.product as product_name,
                o.date, 
                o.amount, 
                o.status 
              FROM orders o
              JOIN customers c ON o.customerid = c.id
              JOIN products p ON o.productid = p.id
              ORDER BY o.date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $db_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - SPORTIFY Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="app-container">
        <aside class="sidebar">
            
        <div class="sidebar-header">
                <div class="logo-box">
                    <img src="image/logo.jpg" alt="Logo" style="width: 100%; height: auto;">
                </div>
                <h1 class="logo-text">SPORTIFY</h1>
            </div>
            <nav class="nav-menu">
                <a href="homepage.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="order.php" class="nav-item active"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="products.php" class="nav-item"><i class="fas fa-box"></i> Products</a>
                <a href="categories.php" class="nav-item"><i class="fas fa-folder"></i> Categories</a>
                <a href="customers.php" class="nav-item"><i class="fas fa-users"></i> Customers</a>
                <a href="analytics.php" class="nav-item analytics-dark"><i class="fas fa-chart-line"></i> Analytics</a>
                
                <div class="nav-divider"></div>
                <a href="profile.php" class="nav-item"><i class="fas fa-user-gear"></i> Profile</a>
                <a href="logout.php" class="nav-item logout-btn"><i class="fas fa-power-off"></i> Logout</a>
            </nav>
        </aside>

        <main class="content-area">
            <header class="top-header">
                <div class="header-title-area">
                    <h1>Orders</h1>
                    <p>Manage and track all customer orders</p>
                </div>
                <div class="header-actions">
                    <div style="color:white; margin-right: 20px;">
                      
                    Welcome, <?php echo htmlspecialchars($display_name); ?>
                    </div>
                    <button class="export-btn"><i class="fas fa-download"></i> Export</button>
                </div>
            </header>

            <section class="inventory-section">
                <div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($db_orders)): ?>
                                <tr>
                                
                                <td colspan="6" style="text-align:center;">No orders found in database.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($db_orders as $order): ?>
                                <tr>
                                    <td><strong>#ORD-<?php echo str_pad($order['orderid'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                 <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                    <td><?php echo date("M d, Y", strtotime($order['date'])); ?></td>
                                    <td>₱<?php echo number_format($order['amount'], 2); ?></td>
                                    <td>
                                        <span class="status-pill <?php echo strtolower($order['status']); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                 </div>
            </section>
        </main>
    </div>

</body>
</html>