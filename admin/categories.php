<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php'); 

// --- 1. HANDLE ADD CATEGORY LOGIC ---
if(isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $meta = mysqli_real_escape_string($conn, $_POST['meta_description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $icon = "";
    if(!empty($_FILES['icon']['name'])) {
        $icon = time() . '_' . $_FILES['icon']['name'];
        move_uploaded_file($_FILES['icon']['tmp_name'], "../uploads/" . $icon);
    }

    $insert = "INSERT INTO categories (name, meta_description, icon, is_active, is_featured) 
               VALUES ('$name', '$meta', '$icon', '$is_active', '$is_featured')";
    
    if(mysqli_query($conn, $insert)) {
        header("Location: categories.php?success=1");
        exit();
    }
}

// 2. Safety fetch function
function getCount($conn, $query) {
    $res = mysqli_query($conn, $query);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        return $row['total'] ?? 0;
    }
    return 0;
}

$unread_count = getCount($conn, "SELECT (
    (SELECT COUNT(*) FROM orders WHERE is_read = 0) + 
    (SELECT COUNT(*) FROM users WHERE is_read = 0)
) as total");

// --- SEARCH LOGIC ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND c.name LIKE '%$search%'";
}

$query .= " GROUP BY c.id ORDER BY c.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Manage Categories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-yellow: #facc15;
            --dark-sidebar: #1e293b;
            --bg-light: #f8fafc;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; display: flex; }

        .sidebar { width: 260px; background: var(--dark-sidebar); height: 100vh; position: fixed; color: white; padding: 20px 0; z-index: 100; }
        .sidebar-header { text-align: center; padding-bottom: 30px; }
        .logo-box { width: 80px; height: 80px; margin: 0 auto; border: 3px solid var(--primary-yellow); border-radius: 50%; overflow: hidden; }
        .sidebar-menu { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-menu li a { color: #94a3b8; text-decoration: none; padding: 15px 25px; display: block; font-weight: 500; transition: 0.3s; }
        .sidebar-menu li.active a, .sidebar-menu li a:hover { background: #334155; color: white; border-left: 4px solid var(--primary-yellow); }

        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        .top-navbar { background: white; padding: 15px 40px; display: flex; align-items: center; justify-content: flex-end; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 90; }
        
        .notif-btn { position: relative; color: #64748b; font-size: 20px; text-decoration: none; padding: 5px; display: flex; align-items: center; }
        .notif-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 50%; border: 2px solid white; }

        .dashboard-container { padding: 40px; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-section h1 { font-size: 28px; font-weight: 800; color: #0f172a; margin: 0; }
        .header-section p { color: #64748b; margin: 5px 0 0 0; }

        .filter-row { background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; border: 1px solid #e2e8f0; }
        .filter-input { padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; }
        .filter-btn { background: #334155; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.2s; }

        .box-placeholder { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0; }
        .inventory-table { width: 100%; border-collapse: collapse; }
        .inventory-table th { background: #f8fafc; padding: 15px 20px; color: #334155; font-size: 13px; font-weight: 700; text-align: left; border-bottom: 2px solid #e2e8f0;}
        .inventory-table td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #475569; font-size: 14px; }

        .add-btn { background: var(--primary-yellow); color: #0f172a; padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 10px rgba(250, 204, 21, 0.3); cursor: pointer; border: none; }
        .add-btn:hover { background: #eab308; transform: translateY(-2px); }

        .cat-img-preview { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0; margin-right: 12px; vertical-align: middle; }
        .cat-icon-placeholder { width: 40px; height: 40px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; vertical-align: middle; color: #94a3b8; }

        .status-badge { padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-active { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-inactive { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-featured { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; margin-left: 5px; }

        /* MODAL STYLING */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; width: 500px; margin: 50px auto; border-radius: 15px; padding: 30px; position: relative; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .close-modal { position: absolute; right: 20px; top: 20px; font-size: 20px; cursor: pointer; color: #64748b; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; color: #1e293b; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; font-family: inherit; }
        .checkbox-group { display: flex; gap: 20px; margin-top: 10px; }
        .checkbox-item { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; }
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
            <li><a href="products.php"><i class="fa-solid fa-box"></i> &nbsp; Products</a></li>
            <li class="active"><a href="categories.php"><i class="fa-solid fa-folder"></i> &nbsp; Categories</a></li>
            <li><a href="customers.php"><i class="fa-solid fa-users"></i> &nbsp; Customers</a></li>
            <li><a href="analytics.php"><i class="fa-solid fa-chart-line"></i> &nbsp; Analytics</a></li>
            <li><a href="logout.php" style="color: #ef4444;"><i class="fa-solid fa-right-from-bracket"></i> &nbsp; Logout</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <header class="top-navbar">
            <div style="display: flex; align-items: center; gap: 25px;">
                <a href="notifications.php" class="notif-btn">
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
                    <h1>Category Management</h1>
                    <p>Organize your merchandise by adding or editing product categories.</p>
                </div>
                <button onclick="openModal()" class="add-btn">
                    <i class="fa-solid fa-plus"></i> &nbsp; ADD CATEGORY
                </button>
            </div>

            <form action="" method="GET" class="filter-row">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" name="search" class="filter-input" style="width: 350px;" placeholder="Search category name..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="filter-btn">Search</button>
            </form>

            <div class="box-placeholder">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Meta Description</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td style="font-weight: 600; color: #1e293b;">
                                    <?php if(!empty($row['icon']) && file_exists("../uploads/".$row['icon'])): ?>
                                        <img src="../uploads/<?php echo $row['icon']; ?>" class="cat-img-preview">
                                    <?php else: ?>
                                        <div class="cat-icon-placeholder">
                                            <i class="fa-regular fa-folder-open"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </td>
                                <td style="max-width: 300px; color: #64748b; font-size: 13px;">
                                    <?php echo !empty($row['meta_description']) ? htmlspecialchars($row['meta_description']) : "<i style='color:#cbd5e1'>No description.</i>"; ?>
                                </td>
                                <td style="font-weight: 700; color: #334155;">
                                    <?php echo $row['product_count']; ?> items
                                </td>
                                <td>
                                    <?php if($row['is_active'] == 1): ?>
                                        <span class="status-badge badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge badge-inactive">Inactive</span>
                                    <?php endif; ?>

                                    <?php if($row['is_featured'] == 1): ?>
                                        <span class="status-badge badge-featured"><i class="fa-solid fa-star"></i> Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="edit_category.php?id=<?php echo $row['id']; ?>" style="color: #3b82f6; margin-right: 15px;"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="delete_category.php?id=<?php echo $row['id']; ?>" style="color: #ef4444;" onclick="return confirm('Delete category?')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 40px; color: #64748b;">No categories found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 style="margin-top: 0; color: #0f172a;">Add New Category</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. K-Pop Albums" required>
                </div>
                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="3" placeholder="Brief description for SEO..."></textarea>
                </div>
                <div class="form-group">
                    <label>Category Icon/Image</label>
                    <input type="file" name="icon" class="form-control">
                </div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" name="is_active" id="active" checked>
                        <label for="active" style="margin:0">Active Category</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="is_featured" id="featured">
                        <label for="featured" style="margin:0">Featured Category</label>
                    </div>
                </div>
                <button type="submit" name="add_category" class="add-btn" style="width: 100%; margin-top: 25px;">
                    SAVE CATEGORY
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('addCategoryModal').style.display = 'block'; }
        function closeModal() { document.getElementById('addCategoryModal').style.display = 'none'; }
        window.onclick = function(event) {
            let modal = document.getElementById('addCategoryModal');
            if (event.target == modal) { modal.style.display = 'none'; }
        }
    </script>

</body>
</html>