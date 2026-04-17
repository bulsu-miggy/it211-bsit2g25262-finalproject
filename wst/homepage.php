<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // header("Location: login.php"); 
    // exit();
}

$display_name = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin User";

$new_arrivals = [
    ["name" => "adidas Men's Barricade 14", "price" => "₱9,500.00", "img" => "image/shoe1.webp", "stock" => "In Stock"],
    ["name" => "adidas Unisex Harden Vol. 10", "price" => "₱9,500.00", "img" => "image/shoe2.webp", "stock" => "Low Stock"],
    ["name" => "Nike Men's Tatum 4 PF", "price" => "₱6,565.50", "img" => "image/shoe3.webp", "stock" => "In Stock"],
    ["name" => "Nike Men's Giannis Immortality 4", "price" => "₱3,865.50", "img" => "image/shoe4.webp", "stock" => "In Stock"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPORTIFY Dashboard</title>
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
                <a href="homepage.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="order.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Orders</a>
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
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search your collection...">
                </div>
                <div class="user-pill">
                    <span><?php echo htmlspecialchars($display_name); ?></span>
                    <div class="user-avatar"></div>
                </div>
            </header>

            <section class="dashboard-stats">
                <div class="stat-box">
                    <h3>200+</h3>
                    <p>Brands</p>
                </div>
                <div class="stat-box">
                    <h3>2,000+</h3>
                    <p>Products</p>
                </div>
                <div class="stat-box">
                    <h3>30,000+</h3>
                    <p>Customers</p>
                </div>
            </section>

            <section class="inventory-section">
                <div class="section-title">
                    <h2>New Arrivals</h2>
                    <button class="add-btn">View All</button>
                </div>
                <div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Stock Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($new_arrivals as $item): ?>
                            <tr>
                                <td class="product-info">
                                    <img src="<?php echo $item['img']; ?>" alt="product" onerror="this.src='https://via.placeholder.com/40'">
                                    <span><?php echo $item['name']; ?></span>
                                </td>
                                <td><strong><?php echo $item['price']; ?></strong></td>
                                <td>
                                    <span class="stock-badge <?php echo (strpos($item['stock'], 'Low') !== false) ? 'low' : 'full'; ?>">
                                        <?php echo $item['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="cart.php" class="icon-action"><i class="fas fa-cart-plus"></i></a>
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