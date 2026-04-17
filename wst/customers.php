<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // header("Location: login.php"); 
    // exit();
}

$display_name = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin User";

// Customer Data based on your screenshot
$customers = [
    ["name" => "John Doe", "email" => "john.doe@email.com", "orders" => "12", "spent" => "$1,245.00", "joined" => "2026-01-15"],
    ["name" => "Jane Smith", "email" => "jane.smith@email.com", "orders" => "8", "spent" => "$890.50", "joined" => "2026-02-03"],
    ["name" => "Bob Johnson", "email" => "bob.j@email.com", "orders" => "23", "spent" => "$2,310.00", "joined" => "2025-11-20"],
    ["name" => "Alice Brown", "email" => "alice.b@email.com", "orders" => "5", "spent" => "$445.00", "joined" => "2026-03-10"],
    ["name" => "Charlie Wilson", "email" => "charlie.w@email.com", "orders" => "31", "spent" => "$3,890.00", "joined" => "2025-09-05"],
    ["name" => "Emma Davis", "email" => "emma.d@email.com", "orders" => "15", "spent" => "$1,567.00", "joined" => "2026-01-28"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - SPORTIFY</title>
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
                    <p>Manage your customer relationships</p>
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
                        <input type="text" placeholder="Search customers...">
                    </div>
                </div>

                <div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($customers as $c): ?>
                            <tr>
                                <td class="customer-cell">
                                    <div class="avatar-placeholder"></div>
                                    <span><?php echo $c['name']; ?></span>
                                </td>
                                <td><?php echo $c['email']; ?></td>
                                <td><?php echo $c['orders']; ?></td>
                                <td><strong><?php echo $c['spent']; ?></strong></td>
                                <td><?php echo $c['joined']; ?></td>
                                <td>
                                    <a href="#" class="view-details-lnk">View Details</a>
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