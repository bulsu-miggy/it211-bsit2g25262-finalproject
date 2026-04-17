<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include('../config/db_connect.php'); 

// 1. Safety fetch function para sa counts (Gaya ng sa index.php)
function getCount($conn, $query) {
    $res = mysqli_query($conn, $query);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        return $row['total'] ?? 0;
    }
    return 0;
}

// 2. Notification Count (Para sa bell badge)
$unread_count = getCount($conn, "SELECT (
    (SELECT COUNT(*) FROM orders WHERE is_read = 0) + 
    (SELECT COUNT(*) FROM users WHERE is_read = 0)
) as total");

// --- SEARCH AT FILTER LOGIC ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_cat = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$products_query = "SELECT p.*, c.name AS category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";

if (!empty($search)) {
    $products_query .= " AND p.name LIKE '%$search%'";
}

if (!empty($filter_cat) && $filter_cat != 'All Categories') {
    $products_query .= " AND c.id = '$filter_cat'";
}

$products_query .= " ORDER BY p.id DESC";
$products_result = mysqli_query($conn, $products_query);

if (!$products_result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Manage Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-yellow: #facc15;
            --dark-sidebar: #1e293b;
            --bg-light: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; display: flex; }

        /* Sidebar Styling */
        .sidebar { width: 260px; background: var(--dark-sidebar); height: 100vh; position: fixed; color: white; padding: 20px 0; z-index: 100; }
        .sidebar-header { text-align: center; padding-bottom: 30px; }
        .logo-box { width: 80px; height: 80px; margin: 0 auto; border: 3px solid var(--primary-yellow); border-radius: 50%; overflow: hidden; }
        .sidebar-menu { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-menu li a { color: #94a3b8; text-decoration: none; padding: 15px 25px; display: block; font-weight: 500; transition: 0.3s; }
        .sidebar-menu li.active a, .sidebar-menu li a:hover { background: #334155; color: white; border-left: 4px solid var(--primary-yellow); }

        /* Main Content & Navbar */
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .top-navbar { background: white; padding: 15px 40px; display: flex; align-items: center; justify-content: flex-end; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 90; }
        
        /* Icon Badge Styling (Gaya sa index.php) */
        .notif-btn { position: relative; color: #64748b; font-size: 20px; text-decoration: none; padding: 5px; display: flex; align-items: center; }
        .notif-badge { 
            position: absolute; top: -5px; right: -5px; 
            background: #ef4444; color: white; font-size: 10px; 
            font-weight: 800; padding: 2px 6px; border-radius: 50%; 
            border: 2px solid white;
        }

        .dashboard-container { padding: 40px; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-section h1 { font-size: 28px; font-weight: 800; color: var(--text-main); margin: 0; }
        .header-section p { color: var(--text-muted); margin: 5px 0 0 0; }

        .filter-row { 
            background: white; padding: 15px 20px; border-radius: 12px; 
            margin-bottom: 25px; display: flex; align-items: center; gap: 15px;
            border: 1px solid #e2e8f0;
        }
        .filter-input { 
            padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; 
            font-family: 'Inter', sans-serif; font-size: 14px; outline: none;
        }
        .filter-btn {
            background: #334155; color: white; border: none; padding: 10px 20px;
            border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.2s;
        }
        .filter-btn:hover { background: #1e293b; }

        .box-placeholder { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0; }
        .inventory-table { width: 100%; border-collapse: collapse; }
        .inventory-table th { background: #f1f5f9; padding: 15px 20px; color: #475569; font-size: 12px; text-transform: uppercase; font-weight: 700; text-align: left; }
        .inventory-table td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

        .add-btn { background: var(--primary-yellow); color: #0f172a; padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 10px rgba(250, 204, 21, 0.3); }
        .add-btn:hover { background: #eab308; transform: translateY(-2px); }

        .stock-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .stock-low { background: #fee2e2; color: #ef4444; }
        .stock-good { background: #d1fae5; color: #10b981; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-box">
                <img src="../character.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h3 style="font-size: 14px; margin-top: 15px; letter-spacing: 1px;">SPARKVERSE ADMIN</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-gauge"></i> &nbsp; Dashboard</a></li>
            <li><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> &nbsp; Orders</a></li>
            <li class="active"><a href="products.php"><i class="fa-solid fa-box"></i> &nbsp; Products</a></li>
            <li><a href="categories.php"><i class="fa-solid fa-folder"></i> &nbsp; Categories</a></li>
            <li><a href="customers.php"><i class="fa-solid fa-users"></i> &nbsp; Customers</a></li>
            <li><a href="analytics.php"><i class="fa-solid fa-chart-line"></i> &nbsp; Analytics</a></li>
            <li><a href="logout.php" style="color: #ef4444;"><i class="fa-solid fa-right-from-bracket"></i> &nbsp; Logout</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <header class="top-navbar">
            <div style="display: flex; align-items: center; gap: 25px;">
                
                <a href="notifications.php" class="notif-btn" title="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    <?php if($unread_count > 0): ?>
                        <span class="notif-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>

                <a href="profile.php">
                    <div style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary-yellow); overflow: hidden;">
                        <img src="../character.jpg" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </a>
            </div>
        </header>

        <main class="dashboard-container">
            <div class="header-section">
                <div>
                    <h1>Inventory Management</h1>
                    <p>Add, edit, or remove your K-pop merchandise items.</p>
                </div>
                <a href="add_product.php" class="add-btn">
                    <i class="fa-solid fa-plus"></i> &nbsp; ADD PRODUCT
                </a>
            </div>

            <form action="" method="GET" class="filter-row">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" name="search" class="filter-input" style="width: 300px;" placeholder="Search product name..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <select name="category" class="filter-input">
                        <option value="All Categories">All Categories</option>
                        <?php
                        $cat_list = mysqli_query($conn, "SELECT * FROM categories");
                        while($c = mysqli_fetch_assoc($cat_list)) {
                            $sel = ($filter_cat == $c['id']) ? 'selected' : '';
                            echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="filter-btn">Filters</button>
                
                <?php if($search != "" || ($filter_cat != "" && $filter_cat != "All Categories")): ?>
                    <a href="products.php" style="color: #ef4444; text-decoration: none; font-size: 13px; font-weight: 600;">Reset</a>
                <?php endif; ?>
            </form>

            <div class="box-placeholder">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Product Details</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Inventory</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($products_result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($products_result)): ?>
                            <tr>
                                <td style="display: flex; align-items: center; gap: 15px;">
                                    <?php 
                                        $img_src = $row['image'];
                                        if (strpos($img_src, '/') === false) {
                                            $img_path = "../uploads/" . $img_src;
                                        } else {
                                            $img_path = "../" . $img_src;
                                        }
                                    ?>
                                    <img src="<?php echo $img_path; ?>" width="50" height="50" style="border-radius: 10px; object-fit: cover; border: 1px solid #e2e8f0;">
                                    <span style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($row['name']); ?></span>
                                </td>
                                <td>
                                    <span style="color: #64748b; font-size: 13px;">
                                        <?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: #0f172a;">₱<?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <span class="stock-badge <?php echo ($row['stock'] <= 5) ? 'stock-low' : 'stock-good'; ?>">
                                        <?php echo $row['stock']; ?> IN STOCK
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" style="color: #3b82f6; margin-right: 15px;"><i class="fa-solid fa-pen"></i></a>
                                    <a href="delete_product.php?id=<?php echo $row['id']; ?>" style="color: #ef4444;" onclick="return confirm('Remove this product?')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #64748b;">
                                    <i class="fa-solid fa-box-open" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                    No products found matching your criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>