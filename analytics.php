<?php
session_start();
require "connect.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$display_name = $_SESSION['username'] ?? "Admin User";

try {
    // 1. TOTAL REVENUE
    $revStmt = $conn->query("SELECT SUM(amount) FROM orders WHERE status != 'cancelled'");
    $total_revenue = $revStmt->fetchColumn() ?: 0;

    // 2. TOTAL ORDERS
    $ordStmt = $conn->query("SELECT COUNT(orderid) FROM orders");
    $total_orders = $ordStmt->fetchColumn() ?: 0;

    // 3. ACTIVE CUSTOMERS
    $custStmt = $conn->query("SELECT COUNT(DISTINCT customerid) FROM orders");
    $active_customers = $custStmt->fetchColumn() ?: 0;

    // 4. TOP SELLING PRODUCTS
    $topProdStmt = $conn->query("
        SELECT p.product, COUNT(o.orderid) as sales_count 
        FROM products p 
        JOIN orders o ON p.id = o.productid 
        GROUP BY p.id 
        ORDER BY sales_count DESC 
        LIMIT 5
    ");
    $top_products = $topProdStmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. MONTHLY SALES TREND (Last 6 Months)
    $chartStmt = $conn->query("
        SELECT DATE_FORMAT(date, '%b') as month, SUM(amount) as total 
        FROM orders 
        WHERE date > DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY month 
        ORDER BY date ASC
    ");
    $chart_data = $chartStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Analytics Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
  <html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - SPORTIFY</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reusing your table-container style for analytics boxes */
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #3b3a3a; padding: 25px; border-radius: 8px; border: 1px solid #333; }

        .stat-card h3 { color: #888; font-size: 12px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .value { font-size: 24px; font-weight: 800; color: white; }

        .dashboard-split { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; }
         .content-box { background: #474747; padding: 20px; border-radius: 8px; border: 1px solid #333; }
        .content-box h2 { font-size: 18px; margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 10px; }

        /* Simple visible Bar Chart */
        .bar-chart { display: flex; align-items: flex-end; gap: 10px; height: 200px; padding-top: 20px; }
        .bar-wrapper { flex: 1; display: flex; flex-direction: column; align-items: center; }
        .bar { width: 100%; background: #fff; border-radius: 2px 2px 0 0; min-height: 2px; }
        .label { font-size: 10px; color: #888; margin-top: 8px; }

        .list-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #222; }
        .list-item:last-child { border: none; }
    </style>
</head>
<body>

<div class="app-container">
    <aside class="sidebar">
        <div class="sidebar-header">

            <div class="logo-box"><img src="image/logo.jpg" alt="Logo" style="width:100%;"></div>
            <h1 class="logo-text">SPORTIFY</h1>
        </div>
        <nav class="nav-menu"> <a href="homepage.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a> <a href="order.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Orders</a> <a href="products.php" class="nav-item"><i class="fas fa-box"></i> Products</a> <a href="categories.php" class="nav-item"><i class="fas fa-folder"></i> Categories</a> <a href="customers.php" class="nav-item"><i class="fas fa-users"></i> Customers</a> <a href="analytics.php" class="nav-item analytics-dark"><i class="fas fa-chart-line"></i> Analytics</a> <div class="nav-divider"></div> <a href="profile.php" class="nav-item"><i class="fas fa-user-gear"></i> Profile</a> <a href="logout.php" class="nav-item logout-btn"><i class="fas fa-power-off"></i> Logout</a> </nav>
    </aside>

    <main class="content-area">
        <header class="top-header">
            <div class="header-title-area">
                <h1>Analytics</h1>
                <p>Store performance based on real-time data</p>
            </div>
            <div class="header-actions">
                <div class="user-pill"><span><?php echo htmlspecialchars($display_name); ?></span></div>
            </div>
        </header>

         <div class="analytics-grid">
            <div class="stat-card">
                <h3>Total Revenue</h3>

                <div class="value">₱<?php echo number_format($total_revenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Orders Total</h3>
                <div class="value"><?php echo $total_orders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Customer Base</h3>
                <div class="value"><?php echo $active_customers; ?></div>
            </div>
        </div>

        <div class="dashboard-split">
            <div class="content-box">
                <h2>Revenue Growth</h2>
                <div class="bar-chart">

                    <?php 
                    $max_total = !empty($chart_data) ? max(array_column($chart_data, 'total')) : 1;
                    foreach ($chart_data as $row): 
                        $height = ($row['total'] / $max_total) * 100;
                    ?>
                    <div class="bar-wrapper">
                        <div class="bar" style="height: <?php echo $height; ?>%;"></div>
                        <span class="label"><?php echo $row['month']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="content-box">
                <h2>Top Selling Products</h2>
                <div style="margin-top: 10px;">
                    <?php foreach ($top_products as $prod): ?>
                    <div class="list-item">
                        <span><?php echo htmlspecialchars($prod['product']); ?></span>
                        <strong style="color: #888;"><?php echo $prod['sales_count']; ?> sold</strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
     </main>
</div>

</body>
</html>