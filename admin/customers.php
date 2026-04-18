<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php'); 

// 1. Safety fetch function para sa counts
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
$filter_gender = isset($_GET['gender']) ? mysqli_real_escape_string($conn, $_GET['gender']) : '';

// --- PAGINATION LOGIC ---
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Base Condition
$where_clause = " WHERE 1=1";
if (!empty($search)) {
    $where_clause .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%')";
}
if (!empty($filter_gender) && $filter_gender != 'All Genders') {
    $where_clause .= " AND u.gender = '$filter_gender'";
}

// Kunin ang total rows para sa pagination
$total_query = "SELECT COUNT(*) as total FROM users u $where_clause";
$total_result = mysqli_query($conn, $total_query);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Main Query
$query = "SELECT 
            u.id, u.name, u.email, u.gender, u.age, u.phone_number, u.profile_picture, u.created_at,
            COUNT(o.id) as total_orders, 
            IFNULL(SUM(o.total_amount), 0) as total_spent 
          FROM users u 
          LEFT JOIN orders o ON u.id = o.user_id 
          $where_clause
          GROUP BY u.id 
          ORDER BY u.id DESC 
          LIMIT $offset, $limit";

$result = mysqli_query($conn, $query);

// Quick Stats
$stats_query = "SELECT COUNT(id) as total_users FROM users";
$stats_res = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Customers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-yellow: #facc15;
            --dark-sidebar: #1e293b;
            --bg-light: #f8fafc;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; display: flex; }

        /* Sidebar */
        .sidebar { width: 260px; background: var(--dark-sidebar); height: 100vh; position: fixed; color: white; padding: 20px 0; z-index: 100; }
        .sidebar-header { text-align: center; padding-bottom: 30px; }
        .logo-box { width: 80px; height: 80px; margin: 0 auto; border: 3px solid var(--primary-yellow); border-radius: 50%; overflow: hidden; }
        .sidebar-menu { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-menu li a { color: #94a3b8; text-decoration: none; padding: 15px 25px; display: block; font-weight: 500; transition: 0.3s; }
        .sidebar-menu li.active a, .sidebar-menu li a:hover { background: #334155; color: white; border-left: 4px solid var(--primary-yellow); }

        /* Content & Navbar */
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        .top-navbar { background: white; padding: 15px 40px; display: flex; align-items: center; justify-content: flex-end; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 90; }
        
        /* Icon Badge Styling */
        .notif-btn { position: relative; color: #64748b; font-size: 20px; text-decoration: none; padding: 5px; display: flex; align-items: center; }
        .notif-badge { 
            position: absolute; top: -5px; right: -5px; 
            background: #ef4444; color: white; font-size: 10px; 
            font-weight: 800; padding: 2px 6px; border-radius: 50%; 
            border: 2px solid white;
        }

        .dashboard-container { padding: 40px; }
        
        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .stat-card h3 { margin: 0; color: #64748b; font-size: 13px; text-transform: uppercase; }
        .stat-card p { margin: 5px 0 0; font-size: 24px; font-weight: 800; color: #1e293b; }

        /* Search & Filter Row */
        .filter-row { 
            background: white; padding: 15px 20px; border-radius: 12px; 
            margin-bottom: 25px; display: flex; align-items: center; gap: 15px;
            border: 1px solid #e2e8f0; flex-wrap: wrap;
        }
        .filter-input { 
            padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; 
            font-family: 'Inter', sans-serif; font-size: 14px; outline: none;
        }
        .filter-btn {
            background: #334155; color: white; border: none; padding: 10px 20px;
            border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.2s;
        }
        .btn-export {
            background: #1e293b; color: white; padding: 10px 18px;
            border-radius: 8px; text-decoration: none; font-size: 14px;
            font-weight: 600; display: flex; align-items: center; gap: 8px;
            transition: 0.3s; border: none; cursor: pointer; margin-left: auto;
        }

        /* Table */
        .box-placeholder { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0; }
        .inventory-table { width: 100%; border-collapse: collapse; }
        .inventory-table th { background: #f8fafc; padding: 18px 20px; color: #1e293b; font-size: 12px; text-transform: uppercase; font-weight: 700; text-align: left; border-bottom: 2px solid #edf2f7; }
        .inventory-table td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #475569; font-size: 14px; }

        .spent-badge { font-weight: 700; color: #059669; background: #ecfdf5; padding: 5px 10px; border-radius: 8px; }
        .action-group { display: flex; gap: 8px; }
        .btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none; transition: 0.2s; }
        .btn-view { color: #3b82f6; background: #eff6ff; }
        .btn-delete { color: #ef4444; background: #fee2e2; }

        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-img { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; }
        .user-avatar { width: 40px; height: 40px; background: #e0e7ff; color: #4338ca; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; }

        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 25px; }
        .pagination a { padding: 8px 14px; background: white; border: 1px solid #e2e8f0; color: #1e293b; text-decoration: none; border-radius: 6px; font-size: 14px; }
        .pagination a.active { background: var(--primary-yellow); border-color: var(--primary-yellow); font-weight: 700; }
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
            <li><a href="categories.php"><i class="fa-solid fa-folder"></i> &nbsp; Categories</a></li>
            <li class="active"><a href="customers.php"><i class="fa-solid fa-users"></i> &nbsp; Customers</a></li>
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
            <div class="header-section" style="margin-bottom: 30px;">
                <h1 style="font-size: 28px; font-weight: 800; color: #0f172a; margin: 0;">Customer Management</h1>
                <p style="color: #64748b; margin-top: 5px;">Track customer spending and account details.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Registered</h3>
                    <p><?php echo number_format($stats_res['total_users']); ?></p>
                </div>
            </div>

            <form action="" method="GET" class="filter-row">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" name="search" class="filter-input" style="width: 250px;" placeholder="Name or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <select name="gender" class="filter-input">
                    <option value="All Genders">All Genders</option>
                    <option value="Male" <?php if($filter_gender == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if($filter_gender == 'Female') echo 'selected'; ?>>Female</option>
                </select>

                <button type="submit" class="filter-btn">Filter</button>
                
                <?php if($search != "" || ($filter_gender != "" && $filter_gender != "All Genders")): ?>
                    <a href="customers.php" style="color: #ef4444; text-decoration: none; font-size: 13px; font-weight: 600;">Reset</a>
                <?php endif; ?>

                <a href="export_customers.php" class="btn-export">
                    <i class="fa-solid fa-file-export"></i> Export CSV
                </a>
            </form>

            <div class="box-placeholder">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Email & Demographic</th>
                            <th>Total Orders</th>
                            <th>Revenue</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <?php 
                                            $img_path = "../uploads/profiles/" . $row['profile_picture'];
                                            if(!empty($row['profile_picture']) && file_exists($img_path) && $row['profile_picture'] != 'default_avatar.png'): 
                                        ?>
                                            <img src="<?php echo $img_path; ?>" class="user-img">
                                        <?php else: ?>
                                            <div class="user-avatar"><?php echo strtoupper(substr($row['name'], 0, 1)); ?></div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($row['name']); ?></div>
                                            <div style="font-size: 11px; color: #94a3b8;">ID: #<?php echo $row['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #475569;"><?php echo htmlspecialchars($row['email']); ?></div>
                                    <div style="font-size: 12px; color: #94a3b8;">
                                        <?php echo htmlspecialchars($row['gender'] ?? 'N/A'); ?> • <?php echo htmlspecialchars($row['age'] ?? '0'); ?> yrs
                                    </div>
                                </td>
                                <td style="font-weight: 600;"><?php echo $row['total_orders']; ?> Orders</td>
                                <td><span class="spent-badge">₱<?php echo number_format($row['total_spent'], 2); ?></span></td>
                                <td style="font-size: 13px;">
                                    <?php echo isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A'; ?>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="view_customer.php?id=<?php echo $row['id']; ?>" class="btn btn-view" title="View Details"><i class="fa-solid fa-eye"></i></a>
                                        <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Remove this customer permanently?')" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 60px;">
                                    <i class="fa-solid fa-user-slash" style="font-size: 40px; color: #e2e8f0; margin-bottom: 15px;"></i>
                                    <p style="color: #94a3b8; font-weight: 500;">No customers matching your search.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&gender=<?php echo $filter_gender; ?>" 
                       class="<?php echo ($page == $i) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>