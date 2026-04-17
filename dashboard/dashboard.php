<?php
include '../db/connection.php';

// Calculate total overview stats directly (for accuracy)
$total_revenue = $conn->query("
  SELECT COALESCE(SUM(total_amount), 0) as revenue 
  FROM orders 
  WHERE status = 'completed'
")->fetchColumn();

$total_orders = $conn->query("
  SELECT COUNT(*) 
  FROM orders
")->fetchColumn();

$total_customers = $conn->query("
  SELECT COUNT(DISTINCT customer_name) 
  FROM orders
")->fetchColumn();

// Dynamic stock count
$items_stock = $conn->query("SELECT SUM(stock) as total_stock FROM products")->fetchColumn();

// Revenue periods (consistent with total)
$daily_revenue = $conn->query("
  SELECT COALESCE(SUM(total_amount), 0) 
  FROM orders 
  WHERE order_date >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND status = 'completed'
")->fetchColumn();

$weekly_revenue = $conn->query("
  SELECT COALESCE(SUM(total_amount), 0) 
  FROM orders 
  WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'completed'
")->fetchColumn();

$monthly_revenue = $conn->query("
  SELECT COALESCE(SUM(total_amount), 0) 
  FROM orders 
  WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status = 'completed'
")->fetchColumn();

// Chart data - last 7 days (accurate breakdown)
$stmt = $conn->query("
  SELECT DATE(order_date) as day, COALESCE(SUM(total_amount), 0) as revenue
  FROM orders 
  WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'completed'
  GROUP BY DATE(order_date)
  ORDER BY day ASC
");
$daily_chart = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$chart_labels = [];
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
  $date_key = date('Y-m-d', strtotime("-$i days"));
  $chart_labels[] = date('D', strtotime("-$i days"));
  $chart_data[] = $daily_chart[$date_key] ?? 0;
}

// Recent orders
$recent_stmt = $conn->query("
  SELECT id, product_name, total_amount, payment_status, status, order_date 
  FROM orders 
  ORDER BY id DESC 
  LIMIT 5
");
$recent_orders = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700;900&family=Rubik+Mono+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .logo { font-family: 'Rubik Mono One', sans-serif; font-size: 24px; color: black; text-decoration: none; margin: 0; }
    .revenue-tabs { display: flex; gap: 8px; margin-bottom: 20px; }
    .tab-btn { padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; transition: all 0.2s; }
    .tab-btn.active { background: #1a1d23; color: white; border-color: #1a1d23; }
    .order-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .payment-paid { background: #d4edda; color: #155724; }
    .payment-due { background: #fff3cd; color: #856404; }
    .status-shipped { background: #d1ecf1; color: #0c5460; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>

<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo-section">
      <a href="dashboard.php" class="logo">LYNX</a>
    </div>

    <nav class="nav-container">
      <ul class="nav">
        <li class="active">
          <span class="material-icons-outlined">dashboard</span>
          Dashboard
        </li>
        <li onclick="location.href='orders.php'">
          <span class="material-icons-outlined">shopping_cart</span>
          Orders
        </li>
        <li onclick="location.href='products.php'">
          <span class="material-icons-outlined">inventory_2</span>
          Products
        </li>
        <li onclick="location.href='categories.php'">
          <span class="material-icons-outlined">category</span>
          Categories
        </li>
        <li onclick="location.href='customers.php'">
          <span class="material-icons-outlined">group</span>
          Customers
        </li>
        <li onclick="location.href='analytics.php'">
          <span class="material-icons-outlined">analytics</span>
          Analytics
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <div class="avatar"></div>
      <div class="user-info">
        <strong>Admin</strong>
      </div>
      <a href="../db/action/logout.php" title="Logout" id="logout" style="color: black; margin-left: auto; display: flex; align-items: center; text-decoration: none;" onclick="return confirm('Are you sure you want to logout?');">
        <span class="material-icons-outlined">logout</span>
      </a>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <h1 class="page-title">Dashboard</h1>
      <div class="top-actions">
        <div class="search-container">
          <span class="material-icons-outlined">search</span>
          <input type="text" placeholder="Search...">
        </div>
        <div class="icon-badge">
          <span class="material-icons-outlined">notifications</span>
          <span class="dot">3</span>
        </div>
        <div class="avatar small"></div>
      </div>
    </header>

    <div class="welcome-section">
      <h2>Dashboard</h2>
      <p class="subtext">Welcome back! Here's what's happening today.</p>
    </div>

    <section class="stats">
      <div class="stat-card">
        <div class="card-top">
          <div class="icon-box"><span class="material-icons-outlined">attach_money</span></div>
          <!-- <span class="percentage positive">+12.5%</span> -->
        </div>
        <h3 class="stat-value">$<?php echo number_format($total_revenue, 2); ?></h3>
        <p class="stat-label">Total Revenue</p>
      </div>

      <div class="stat-card">
        <div class="card-top">
          <div class="icon-box"><span class="material-icons-outlined">shopping_cart</span></div>
          <!-- <span class="percentage positive">+8.2%</span> -->
        </div>
        <h3 class="stat-value"><?php echo number_format($total_orders); ?></h3>
        <p class="stat-label">Total Orders</p>
      </div>

      <div class="stat-card">
        <div class="card-top">
          <div class="icon-box"><span class="material-icons-outlined">group</span></div>
          <!-- <span class="percentage positive">+5.1%</span> -->
        </div>
        <h3 class="stat-value"><?php echo number_format($total_customers); ?></h3>
        <p class="stat-label">Total Customers</p>
      </div>

      <div class="stat-card">
        <div class="card-top">
          <div class="icon-box"><span class="material-icons-outlined">inventory_2</span></div>
          <!-- <span class="percentage negative">-1.2%</span> -->
        </div>
        <h3 class="stat-value"><?php echo number_format($items_stock); ?></h3>
        <p class="stat-label">Items in Stock</p>
      </div>
    </section>

    <section class="panels">
      <div class="panel large chart-view full-width">
        <div class="panel-header">
          <div class="header-group">
            <h3 class="panel-title-text">Revenue Overview</h3>
            <p class="subtext">Total earnings from completed orders over time.</p>
          </div>
          <div class="revenue-tabs">
            <button class="tab-btn active" onclick="switchChart('daily')">Daily</button>
            <button class="tab-btn" onclick="switchChart('weekly')">Weekly</button>
            <button class="tab-btn" onclick="switchChart('monthly')">Monthly</button>
          </div>
        </div>
        
        <canvas id="salesChart" style="max-height: 300px;"></canvas>
      </div>

      <div class="panel activity-view full-width">
        <div class="panel-header">
          <h3 class="panel-title-text">Recent Orders</h3>
        </div>

        <div class="table-container">
          <table class="orders-table">
            <thead>
              <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Payment</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_orders as $order): ?>
              <tr>
                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                <td><span class="order-badge payment-<?php echo strtolower($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                <td><span class="order-badge status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($recent_orders)): ?>
              <tr><td colspan="4" style="text-align:center; padding: 40px; color: #666;">No recent orders</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
const weeklyData = <?php echo json_encode($chart_data); ?>;
const weeklyLabels = <?php echo json_encode($chart_labels); ?>;

let currentChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: weeklyLabels,
        datasets: [{
            label: 'Revenue',
            data: weeklyData,
            borderColor: '#1a1d23',
            backgroundColor: 'rgba(26, 29, 35, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { callback: value => '$' + value.toLocaleString() }
            },
            x: { grid: { display: false } }
        }
    }
});

function switchChart(period) {
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  
  if (period === 'daily') {
    currentChart.data.labels = ['Today'];
    currentChart.data.datasets[0].data = [<?php echo $daily_revenue; ?>];
  } else if (period === 'weekly') {
    currentChart.data.labels = weeklyLabels;
    currentChart.data.datasets[0].data = weeklyData;
  } else if (period === 'monthly') {
    currentChart.data.labels = ['Week 1','Week 2','Week 3','Week 4'];
    currentChart.data.datasets[0].data = [1200, 1500, 1800, 2100];
  }
  currentChart.update('none');
}
</script>

</body>
</html>
