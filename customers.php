<?php
session_start();
require "connect.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

$display_name = $_SESSION['username'] ?? "Admin User";

/* FETCH REAL CUSTOMER DATA */
try {
    $query = "SELECT 
                c.id,
                c.fullname, 
                c.email, 
                c.created_at,
                COUNT(o.orderid) AS total_orders, 
                IFNULL(SUM(o.amount), 0) AS total_spent
              FROM customers c
              LEFT JOIN orders o ON c.id = o.customerid
              GROUP BY c.id
              ORDER BY c.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $db_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching customers: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - SPORTIFY Dashboard</title>
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
                <a href="order.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="products.php" class="nav-item"><i class="fas fa-box"></i> Products</a>
                <a href="categories.php" class="nav-item"><i class="fas fa-folder"></i> Categories</a>
                <a href="customers.php" class="nav-item active"><i class="fas fa-users"></i> Customers</a>
                <a href="analytics.php" class="nav-item analytics-dark"><i class="fas fa-chart-line"></i> Analytics</a>
                
                <div class="nav-divider"></div>
                <a href="profile.php" class="nav-item"><i class="fas fa-user-gear"></i> Profile</a>
                <a href="logout.php" class="nav-item logout-btn"><i class="fas fa-power-off"></i> Logout</a>
            </nav>
        </aside>

        <main class="content-area">
            <header class="top-header">
                <div class="header-title-area">
                    <h1>Customers</h1>
                     <p>View and manage your registered members</p>
                </div>
                <div class="header-actions">
                    <div style="color:white; margin-right: 20px;">
                        Admin: <?php echo htmlspecialchars($display_name); ?>
                    </div>
                    <button class="export-btn"><i class="fas fa-download"></i> Export List</button>
                </div>
            </header>

            <section class="inventory-section">
                <div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($db_customers)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;">No customers registered yet.</td>
                                 </tr>
                            <?php else: ?>
                                <?php foreach($db_customers as $customer): ?>
                                <tr>
                                    <td class="customer-cell">
                                        <div class="avatar-placeholder">
                                            <?php echo strtoupper(substr($customer['fullname'], 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($customer['fullname']); ?></span>
                                    </td>
                                     <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo $customer['total_orders']; ?></td>
                                    <td><strong>₱<?php echo number_format($customer['total_spent'], 2); ?></strong></td>
                                    <td><?php echo date("M d, Y", strtotime($customer['created_at'])); ?></td>
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