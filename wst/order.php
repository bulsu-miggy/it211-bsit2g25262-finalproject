<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // header("Location: login.php"); 
    // exit();
}

$display_name = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin User";

// Mock Data for Orders based on your screenshot
$orders = [
    ["id" => "#ORD-001", "customer" => "John Doe", "date" => "2026-03-25", "amount" => "$250.00", "status" => "Completed"],
    ["id" => "#ORD-002", "customer" => "Jane Smith", "date" => "2026-03-25", "amount" => "$180.50", "status" => "Processing"],
    ["id" => "#ORD-003", "customer" => "Bob Johnson", "date" => "2026-03-24", "amount" => "$320.00", "status" => "Shipped"],
    ["id" => "#ORD-004", "customer" => "Alice Brown", "date" => "2026-03-24", "amount" => "$95.00", "status" => "Completed"],
    ["id" => "#ORD-005", "customer" => "Charlie Wilson", "date" => "2026-03-23", "amount" => "$410.00", "status" => "Processing"],
    ["id" => "#ORD-006", "customer" => "Emma Davis", "date" => "2026-03-23", "amount" => "$155.00", "status" => "Cancelled"],
    ["id" => "#ORD-007", "customer" => "Frank Miller", "date" => "2026-03-22", "amount" => "$275.50", "status" => "Completed"],
    ["id" => "#ORD-008", "customer" => "Grace Lee", "date" => "2026-03-22", "amount" => "$199.99", "status" => "Shipped"]
];
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
                <div class="logo-box">LOGO</div>
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
                    <button class="export-btn"><i class="fas fa-download"></i> Export</button>
                    <div class="user-pill">
                        <div class="user-avatar"></div>
                    </div>
                </div>
            </header>

            <section class="inventory-section">
                <div class="filter-bar">
                    <div class="search-inline">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search orders...">
                    </div>
                    <div class="filter-controls">
                        <select class="filter-select">
                            <option>All Status</option>
                            <option>Completed</option>
                            <option>Processing</option>
                            <option>Shipped</option>
                            <option>Cancelled</option>
                        </select>
                        <button class="filter-btn"><i class="fas fa-filter"></i> Filters</button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo $order['id']; ?></strong></td>
                                <td><?php echo $order['customer']; ?></td>
                                <td><?php echo $order['date']; ?></td>
                                <td><?php echo $order['amount']; ?></td>
                                <td>
                                    <span class="status-pill <?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="view-link">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

</body>
</html>