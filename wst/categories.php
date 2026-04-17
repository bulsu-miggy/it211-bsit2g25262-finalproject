<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // header("Location: login.php"); 
    // exit();
}

$display_name = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin User";

// Category Data based on your screenshot
$categories = [
    ["name" => "Electronics", "desc" => "Electronic devices and gadgets", "products" => "24", "status" => "Active", "img" => ""],
    ["name" => "Accessories", "desc" => "Phone cases, cables, and other accessories", "products" => "45", "status" => "Active", "img" => ""],
    ["name" => "Clothing", "desc" => "Apparel and fashion items", "products" => "12", "status" => "Active", "img" => ""],
    ["name" => "Home & Garden", "desc" => "Home decor and garden supplies", "products" => "8", "status" => "Inactive", "img" => ""],
    ["name" => "Sports & Outdoors", "desc" => "Sports equipment and outdoor gear", "products" => "31", "status" => "Active", "img" => ""]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - SPORTIFY</title>
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
                <a href="categories.php" class="nav-item active"><i class="fas fa-folder"></i> Categories</a>
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
                    <h1>Categories</h1>
                    <p>Organize your products into categories</p>
                </div>
                <div class="header-actions">
                    <button class="add-btn"><i class="fas fa-plus"></i> Add Category</button>
                    <div class="user-pill">
                        <div class="user-avatar"></div>
                    </div>
                </div>
            </header>

            <section class="inventory-section">
                <div class="filter-bar">
                    <div class="search-inline">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search categories...">
                    </div>
                </div>

                <div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $c): ?>
                            <tr>
                                <td class="category-cell">
                                    <?php if($c['img']): ?>
                                        <img src="<?php echo $c['img']; ?>" class="category-img" alt="icon">
                                    <?php else: ?>
                                        <div class="category-placeholder"></div>
                                    <?php endif; ?>
                                    <span><?php echo $c['name']; ?></span>
                                </td>
                                <td><?php echo $c['desc']; ?></td>
                                <td><strong><?php echo $c['products']; ?></strong></td>
                                <td>
                                    <span class="status-badge <?php echo ($c['status'] == 'Active') ? 'full' : 'low'; ?>">
                                        <?php echo $c['status']; ?>
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