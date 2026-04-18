<?php
session_start();
require "connect.php";

// protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$display_name = $_SESSION['username'] ?? "User";

// fetch products from database
$stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
$stmt->execute();
$new_arrivals = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-box">
                <img src="image/logo.jpg" style="width:100%; height:auto;">
            </div>
            <h1 class="logo-text">SPORTIFY</h1>
        </div>

        <nav class="nav-menu"> <a href="homepage.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a> <a href="order.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Orders</a> <a href="products.php" class="nav-item"><i class="fas fa-box"></i> Products</a> <a href="categories.php" class="nav-item"><i class="fas fa-folder"></i> Categories</a> <a href="customers.php" class="nav-item"><i class="fas fa-users"></i> Customers</a> <a href="analytics.php" class="nav-item analytics-dark"><i class="fas fa-chart-line"></i> Analytics</a> <div class="nav-divider"></div> <a href="profile.php" class="nav-item"><i class="fas fa-user-gear"></i> Profile</a> <a href="logout.php" class="nav-item logout-btn"><i class="fas fa-power-off"></i> Logout</a> </nav>
    </aside>

    <!-- MAIN -->
    <main class="content-area">

        <header class="top-header">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search products...">
            </div>

            <div style="color:white;">
                Welcome, <?php echo htmlspecialchars($display_name); ?>
            </div>
        </header>

        <!-- STATS (static for now) -->
        <section class="dashboard-stats">
            <div class="stat-box"><h3>200+</h3><p>Brands</p></div>
            <div class="stat-box"><h3>2,000+</h3><p>Products</p></div>
            <div class="stat-box"><h3>30,000+</h3><p>Customers</p></div>
        </section>

        <!-- PRODUCTS -->
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

                    <?php foreach ($new_arrivals as $item): ?>

                        <tr>

                            <td class="product-info">
                                <?php if (!empty($item['img'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['img']); ?>" alt="product">
                                <?php endif; ?>

                                <span><?php echo htmlspecialchars($item['product']); ?></span>
                            </td>

                            <td>
                                <strong>₱<?php echo number_format($item['price'], 2); ?></strong>
                            </td>


                            <td>
                                <span class="stock-badge <?php echo ($item['stock_status'] == 'Low Stock') ? 'low' : 'full'; ?>">
                                    <?php echo htmlspecialchars($item['stock_status']); ?>
                                </span>
                            </td>

                            <td>
                                <a href="cart.php?id=<?php echo $item['id']; ?>" class="icon-action">
    <i class="fas fa-cart-plus"></i>
</a>
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