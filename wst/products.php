<?php
session_start();

// Mock authentication check
if (!isset($_SESSION['user_id'])) {
    // header("Location: login.php"); 
    // exit();
}

$display_name = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin User";

// Shoe Product Data based on your Arrivals and Dashboard images
$products = [
    ["name" => "adidas Men's Barricade 14", "cat" => "Footwear", "price" => "₱9,500.00", "stock" => "45", "status" => "Active", "img" => "image/shoe1.webp"],
    ["name" => "adidas Unisex Harden Vol. 10", "cat" => "Footwear", "price" => "₱9,500.00", "stock" => "23", "status" => "Active", "img" => "image/shoe2.webp"],
    ["name" => "Nike Men's Tatum 4 PF", "cat" => "Footwear", "price" => "₱6,565.50", "stock" => "120", "status" => "Active", "img" => "image/shoe3.webp"],
    ["name" => "Nike Men's Giannis Immortality 4", "cat" => "Footwear", "price" => "₱3,865.50", "stock" => "0", "status" => "Out of Stock", "img" => "image/shoe4.webp"],
    ["name" => "Nike Air Zoom Pegasus", "cat" => "Running", "price" => "₱7,200.00", "stock" => "67", "status" => "Active", "img" => "image/shoe5.webp"],
    ["name" => "adidas Ultraboost Light", "cat" => "Running", "price" => "₱10,000.00", "stock" => "15", "status" => "Active", "img" => "image/shoe6.webp"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - SPORTIFY</title>
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
                <a href="products.php" class="nav-item active"><i class="fas fa-box"></i> Products</a>
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
                    <h1>Products</h1>
                    <p>Manage your footwear collection</p>
                </div>
                <div class="header-actions">
                    <button class="add-product-btn"><i class="fas fa-plus"></i> Add Product</button>
                    <div class="user-pill">
                        <span><?php echo htmlspecialchars($display_name); ?></span>
                        <div class="user-avatar"></div>
                    </div>
                </div>
            </header>

            <section class="inventory-section">
                <div class="filter-bar">
                    <div class="search-inline">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search shoes...">
                    </div>
                    <div class="filter-controls">
                        <select class="filter-select">
                            <option>All Categories</option>
                            <option>Footwear</option>
                            <option>Running</option>
                            <option>Basketball</option>
                        </select>
                        <div class="view-toggle">
                            <button class="toggle-btn"><i class="fas fa-th"></i></button>
                            <button class="toggle-btn active"><i class="fas fa-list"></i></button>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td class="product-cell">
                                    <img src="<?php echo $p['img']; ?>" alt="shoe" class="product-img-small" onerror="this.src='https://via.placeholder.com/40'">
                                    <span><?php echo $p['name']; ?></span>
                                </td>
                                <td><?php echo $p['cat']; ?></td>
                                <td><strong><?php echo $p['price']; ?></strong></td>
                                <td><?php echo $p['stock']; ?></td>
                                <td>
                                    <span class="status-pill <?php echo ($p['status'] == 'Active') ? 'completed' : 'cancelled'; ?>">
                                        <?php echo $p['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-links">
                                        <a href="#" class="edit-lnk">Edit</a>
                                        <a href="#" class="delete-lnk">Delete</a>
                                    </div>
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