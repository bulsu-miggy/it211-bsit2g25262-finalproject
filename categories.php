<?php
session_start();
require "connect.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$display_name = $_SESSION['username'] ?? "Admin User";

// --- 1. GET FILTER PARAMETERS ---
$stock_filter = $_GET['stock_status'] ?? 'All';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 20000;

// --- 2. BUILD DYNAMIC SQL ---
$sql = "SELECT * FROM products WHERE price BETWEEN ? AND ?";
$params = [$min_price, $max_price];

if ($stock_filter !== 'All') {
    $sql .= " AND stock_status = ?";
    $params[] = $stock_filter;
}

$sql .= " ORDER BY id DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $filtered_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Filter Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Filters - SPORTIFY</title>
    <link rel="stylesheet" href="dashboard.css">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-container { display: flex; gap: 20px; margin-top: 20px; }
        .filter-sidebar { width: 250px; background: #1a1a1a; padding: 20px; border-radius: 8px; height: fit-content; }
        .filter-group { margin-bottom: 20px; }
        .filter-group label { display: block; color: #888; margin-bottom: 8px; font-size: 12px; text-transform: uppercase; }
         .filter-group select, .filter-group input { width: 100%; background: #222; border: 1px solid #333; color: white; padding: 8px; border-radius: 4px; }
        .apply-btn { width: 100%; background: white; color: black; border: none; padding: 10px; font-weight: bold; cursor: pointer; border-radius: 4px; }
        .results-area { flex-grow: 1; }
    </style>
</head>
<body>

     <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">

                <div class="logo-box"><img src="image/logo.jpg" alt="Logo" style="width: 100%;"></div>
                <h1 class="logo-text">SPORTIFY</h1>
            </div>
            <nav class="nav-menu">
                <a href="homepage.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="order.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Orders</a>           
                 <a href="products.php" class="nav-item"><i class="fas fa-box"></i> Products</a>
                <a href="categories.php" class="nav-item active"><i class="fas fa-filter"></i> Filters</a>
                <a href="customers.php" class="nav-item"><i class="fas fa-users"></i> Customers</a>
                <div class="nav-divider"></div>
                <a href="logout.php" class="nav-item logout-btn"><i class="fas fa-power-off"></i> Logout</a>
            </nav>
        </aside>

        <main class="content-area">
            <header class="top-header">
                <div class="header-title-area">
                    <h1>Product Filters</h1>
                    <p>Find products based on stock and price</p>
                </div>
            </header>


            <div class="filter-container">
                <aside class="filter-sidebar">
                    <form method="GET" action="categories.php">
                      
                    <div class="filter-group">
                            <label>Stock Status</label>
                            <select name="stock_status">
                                <option value="All" <?php if($stock_filter == 'All') echo 'selected'; ?>>All Items</option>
                                <option value="In Stock" <?php if($stock_filter == 'In Stock') echo 'selected'; ?>>In Stock Only</option>
                                <option value="Low Stock" <?php if($stock_filter == 'Low Stock') echo 'selected'; ?>>Low Stock Only</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Min Price (₱)</label>
                            <input type="number" name="min_price" value="<?php echo $min_price; ?>">
                        </div>

                        <div class="filter-group">
                            <label>Max Price (₱)</label>
                            <input type="number" name="max_price" value="<?php echo $max_price; ?>">
                        </div>


                        <button type="submit" class="apply-btn">Apply Filters</button>
                        <a href="categories.php" style="display:block; text-align:center; color:#888; font-size:12px; margin-top:10px; text-decoration:none;">Reset All</a>
                    </form>
                </aside>

                <section class="results-area">
                    <div class="table-container">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Current Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($filtered_products)): ?>
                                    <tr><td colspan="4" style="text-align:center; padding:40px;">No products match these criteria.</td></tr>
                                <?php else: ?>
                                    <?php foreach($filtered_products as $p): ?>
                                    <tr>

                                        <td class="product-cell">
                                            <img src="<?php echo htmlspecialchars($p['img']); ?>" class="product-img-small">
                                            <span><?php echo htmlspecialchars($p['product']); ?></span>
                                        </td>
                                        <td>₱<?php echo number_format($p['price'], 2); ?></td>
                                        <td><?php echo $p['stock']; ?></td>
                                        <td>
                                            <span class="status-pill <?php echo ($p['stock_status'] == 'In Stock') ? 'completed' : 'low'; ?>">
                                                <?php echo $p['stock_status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>