<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php'); 

// 1. Query for Monthly Revenue (Line Chart)
$sales_query = "SELECT MONTHNAME(created_at) as month, SUM(total_amount) as total FROM orders GROUP BY MONTH(created_at) ORDER BY MONTH(created_at) ASC";
$sales_result = mysqli_query($conn, $sales_query);

$months = [];
$totals = [];
if($sales_result) {
    while($row = mysqli_fetch_assoc($sales_result)) {
        $months[] = $row['month'];
        $totals[] = (float)$row['total'];
    }
}

// 2. LIVE METRICS CALCULATIONS
// Notification Badge Count
$unread_count_res = mysqli_query($conn, "SELECT (
    (SELECT COUNT(*) FROM orders WHERE is_read = 0) + 
    (SELECT COUNT(*) FROM users WHERE is_read = 0)
) as total");
$unread_row = mysqli_fetch_assoc($unread_count_res);
$unread_count = $unread_row['total'] ?? 0;

// Average Order Value
$aov_query = "SELECT AVG(total_amount) as avg_val FROM orders";
$aov_exec = mysqli_query($conn, $aov_query);
$aov_res = ($aov_exec) ? mysqli_fetch_assoc($aov_exec) : null;
$avg_order_value = $aov_res['avg_val'] ?? 0;

// Conversion Rate
$user_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$order_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
$user_count_res = ($user_count_query) ? mysqli_fetch_assoc($user_count_query) : ['total' => 0];
$order_count_res = ($order_count_query) ? mysqli_fetch_assoc($order_count_query) : ['total' => 0];
$conversion_rate = ($user_count_res['total'] > 0) ? ($order_count_res['total'] / $user_count_res['total']) * 100 : 0;

// 3. TOP PRODUCTS
$top_products_query = "SELECT product_name as name, SUM(quantity) as sales_count 
                       FROM order_items 
                       GROUP BY product_name 
                       ORDER BY sales_count DESC LIMIT 5";
$top_products_result = mysqli_query($conn, $top_products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { 
            --primary-yellow: #facc15; 
            --dark-sidebar: #1e293b; 
            --bg-light: #f8fafc; 
            --text-main: #0f172a;
            --text-muted: #64748b;
        }
        
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; display: flex; }
        
        /* Sidebar Styling */
        .sidebar { width: 260px; background: var(--dark-sidebar); height: 100vh; position: fixed; color: white; padding: 20px 0; z-index: 100; }
        .sidebar-header { text-align: center; padding-bottom: 30px; }
        .logo-box { width: 80px; height: 80px; margin: 0 auto; border: 3px solid var(--primary-yellow); border-radius: 50%; overflow: hidden; }
        .sidebar-menu { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-menu li a { color: #94a3b8; text-decoration: none; padding: 15px 25px; display: block; font-weight: 500; transition: 0.3s; }
        .sidebar-menu li.active a, .sidebar-menu li a:hover { background: #334155; color: white; border-left: 4px solid var(--primary-yellow); }

        /* Main Content */
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .top-navbar { background: white; padding: 15px 40px; display: flex; align-items: center; justify-content: flex-end; border-bottom: 1px solid #e2e8f0; }
        
        .notif-btn { position: relative; color: #64748b; font-size: 20px; text-decoration: none; padding: 5px; display: flex; align-items: center; }
        .notif-badge { 
            position: absolute; top: -5px; right: -5px; 
            background: #ef4444; color: white; font-size: 10px; 
            font-weight: 800; padding: 2px 6px; border-radius: 50%; 
            border: 2px solid white;
        }

        .dashboard-container { padding: 40px; }
        
        /* Metrics Grid */
        .metrics-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .metric-card { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; position: relative; transition: 0.3s; }
        .metric-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .metric-card i { font-size: 20px; margin-bottom: 15px; display: block; }
        .metric-badge { position: absolute; top: 20px; right: 20px; padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .badge-green { background: #f0fdf4; color: #16a34a; }
        
        .metric-label { color: var(--text-muted); font-size: 14px; font-weight: 600; }
        .metric-value { font-size: 26px; font-weight: 800; color: var(--text-main); margin-top: 8px; }

        /* Charts Section */
        .analytics-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .chart-card { background: white; padding: 30px; border-radius: 20px; border: 1px solid #e2e8f0; }
        .chart-card h3 { font-size: 18px; font-weight: 700; color: var(--text-main); margin-top: 0; margin-bottom: 25px; }
        
        /* Progress Bar Styling */
        .product-item { margin-bottom: 22px; }
        .product-info { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; font-weight: 600; color: var(--text-main); }
        .progress-bar { height: 10px; background: #f1f5f9; border-radius: 20px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #334155, #475569); border-radius: 20px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-box">
                <img src="../character.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h3 style="font-size: 14px; margin-top: 15px; letter-spacing: 1px;">SPARKVERSE ADMIN</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-gauge"></i> &nbsp; Dashboard</a></li>
            <li><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> &nbsp; Orders</a></li>
            <li><a href="products.php"><i class="fa-solid fa-box"></i> &nbsp; Products</a></li>
            <li><a href="categories.php"><i class="fa-solid fa-folder"></i> &nbsp; Categories</a></li>
            <li><a href="customers.php"><i class="fa-solid fa-users"></i> &nbsp; Customers</a></li>
            <li class="active"><a href="analytics.php"><i class="fa-solid fa-chart-line"></i> &nbsp; Analytics</a></li>
            <li><a href="logout.php" style="color: #ef4444;"><i class="fa-solid fa-right-from-bracket"></i> &nbsp; Logout</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <header class="top-navbar">
            <div style="display: flex; align-items: center; gap: 25px;">
                <a href="notifications.php" class="notif-btn">
                    <i class="fa-regular fa-bell"></i>
                    <?php if($unread_count > 0): ?>
                        <span class="notif-badge"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="profile.php">
                    <div style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary-yellow); overflow: hidden;">
                        <img src="../character.jpg" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </a>
            </div>
        </header>

        <main class="dashboard-container">
            <div class="header-section" style="margin-bottom: 35px;">
                <h1 style="font-size: 28px; font-weight: 800; color: var(--text-main); margin: 0;">Analytics Overview</h1>
                <p style="color: var(--text-muted); margin-top: 5px;">Visual insights and business growth data.</p>
            </div>

            <div class="metrics-row">
                <div class="metric-card">
                    <i class="fa-solid fa-coins" style="color: #ca8a04;"></i>
                    <span class="metric-badge badge-green">Live</span>
                    <div class="metric-label">Total Revenue</div>
                    <div class="metric-value">₱<?= number_format(array_sum($totals), 2); ?></div>
                </div>
                <div class="metric-card">
                    <i class="fa-solid fa-rotate" style="color: #3b82f6;"></i>
                    <div class="metric-label">Conversion Rate</div>
                    <div class="metric-value"><?= number_format($conversion_rate, 1); ?>%</div>
                </div>
                <div class="metric-card">
                    <i class="fa-solid fa-hand-holding-dollar" style="color: #16a34a;"></i>
                    <div class="metric-label">Avg. Order Value</div>
                    <div class="metric-value">₱<?= number_format($avg_order_value, 2); ?></div>
                </div>
                <div class="metric-card">
                    <i class="fa-solid fa-user-group" style="color: #6366f1;"></i>
                    <div class="metric-label">Total Customers</div>
                    <div class="metric-value"><?= $user_count_res['total']; ?></div>
                </div>
            </div>

            <div class="analytics-grid">
                <div class="chart-card">
                    <h3>Revenue Growth</h3>
                    <div style="height: 300px; width: 100%;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3>Top Selling Products</h3>
                    <div style="margin-top: 10px;">
                        <?php 
                        if ($top_products_result && mysqli_num_rows($top_products_result) > 0):
                            while($prod = mysqli_fetch_assoc($top_products_result)): 
                        ?>
                        <div class="product-item">
                            <div class="product-info">
                                <span><?= htmlspecialchars($prod['name']); ?></span>
                                <span style="color: var(--text-muted);"><?= $prod['sales_count']; ?> units</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min(($prod['sales_count'] * 5), 100); ?>%;"></div>
                            </div>
                        </div>
                        <?php 
                            endwhile; 
                        else:
                            echo "<p style='color: var(--text-muted); font-size: 14px;'>No sales data recorded yet.</p>";
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        // Gradient for the line chart
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(30, 41, 59, 0.2)');
        gradient.addColorStop(1, 'rgba(30, 41, 59, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($months); ?>,
                datasets: [{
                    label: 'Monthly Revenue (₱)',
                    data: <?= json_encode($totals); ?>,
                    borderColor: '#1e293b',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#1e293b',
                    pointBorderWidth: 2
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { family: 'Inter', size: 13 },
                        bodyFont: { family: 'Inter', size: 13 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { 
                            font: { family: 'Inter', size: 12 },
                            callback: function(value) { return '₱' + value.toLocaleString(); }
                        }
                    },
                    x: { 
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { family: 'Inter', size: 12 } }
                    }
                }
            }
        });
    </script>
</body>
</html>