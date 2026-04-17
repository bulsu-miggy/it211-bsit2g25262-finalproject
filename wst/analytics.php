<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // header("Location: login.php"); 
    // exit();
}

$display_name = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin User";

// Data for the top metric cards
$metrics = [
    ["label" => "Revenue Growth", "value" => "23.5%", "change" => "+12%", "icon" => "fa-arrow-trend-up", "color" => "green"],
    ["label" => "Conversion Rate", "value" => "3.2%", "change" => "+12%", "icon" => "fa-dollar-sign", "color" => "green"],
    ["label" => "Avg. Order Value", "value" => "$125", "change" => "-5%", "icon" => "fa-shopping-bag", "color" => "red"],
    ["label" => "Customer Retention", "value" => "84%", "change" => "+12%", "icon" => "fa-chart-line", "color" => "green"]
];

// Data for the Bar Chart
$months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
$heights = [50, 70, 40, 85, 65, 55, 80, 95, 60, 75, 88, 90]; // Random percentages for bar heights
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
</head>
<body>

    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-box">LOGO</div>
                <h1 class="logo-text">SPORTIFY</h1>
            </div>
            <nav class="nav-menu">
                <a href="homepage.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="products.php" class="nav-item"><i class="fas fa-box"></i> Products</a>
                <a href="categories.php" class="nav-item"><i class="fas fa-folder"></i> Categories</a>
                <a href="customers.php" class="nav-item"><i class="fas fa-users"></i> Customers</a>
                <a href="analytics.php" class="nav-item active analytics-dark"><i class="fas fa-chart-line"></i> Analytics</a>
                
                <div class="nav-divider"></div>
                <a href="profile.php" class="nav-item"><i class="fas fa-user-gear"></i> Profile</a>
                <a href="logout.php" class="nav-item logout-btn"><i class="fas fa-power-off"></i> Logout</a>
            </nav>
        </aside>

        <main class="content-area">
            <header class="top-header">
                <div class="header-title-area">
                    <h1>Analytics</h1>
                    <p>Track your business performance and insights</p>
                </div>
                <div class="header-actions">
                    <div class="search-bar" style="width: 200px; margin-right: 15px;">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search...">
                    </div>
                    <div class="user-pill">
                        <div class="user-avatar"></div>
                    </div>
                </div>
            </header>

            <section class="analytics-grid">
                <?php foreach($metrics as $m): ?>
                <div class="metric-card">
                    <div class="metric-header">
                        <i class="fas <?php echo $m['icon']; ?> metric-icon"></i>
                        <span class="change-pill <?php echo $m['color']; ?>"><?php echo $m['change']; ?></span>
                    </div>
                    <p class="metric-label"><?php echo $m['label']; ?></p>
                    <h2 class="metric-value"><?php echo $m['value']; ?></h2>
                </div>
                <?php endforeach; ?>
            </section>

            <section class="chart-section">
                <div class="chart-header">
                    <div>
                        <h3>Revenue Overview</h3>
                        <p>Monthly revenue for the past 12 months</p>
                    </div>
                    <div class="chart-toggles">
                        <button class="toggle-btn active">Month</button>
                        <button class="toggle-btn">Year</button>
                    </div>
                </div>
                
                <div class="bar-chart-container">
                    <div class="bars-wrapper">
                        <?php foreach($heights as $index => $h): ?>
                        <div class="bar-group">
                            <div class="bar" style="height: <?php echo $h; ?>%;"></div>
                            <span class="bar-label"><?php echo $months[$index]; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="analytics-footer-grid">
                <div class="footer-card">
                    <h3>Top Products</h3>
                    <div class="progress-item">
                        <span>Wireless Headphones</span>
                        <div class="progress-bar"><div class="fill" style="width: 80%;"></div></div>
                    </div>
                    <div class="progress-item">
                        <span>Smart Watch</span>
                        <div class="progress-bar"><div class="fill" style="width: 60%;"></div></div>
                    </div>
                </div>
                <div class="footer-card">
                    <h3>Traffic Sources</h3>
                    <div class="traffic-placeholder"></div>
                </div>
            </section>
        </main>
    </div>

</body>
</html>