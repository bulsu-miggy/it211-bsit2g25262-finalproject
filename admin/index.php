<?php

session_start();

if(!isset($_SESSION['admin_id'])) {

    header("Location: login.php");

    exit();

}

include('../config/db_connect.php');
// 1. Safety fetch function para sa stats

function getCount($conn, $query) {
    $res = mysqli_query($conn, $query);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        return $row['total'] ?? 0;
    }
    return 0;
}
$prod_count  = getCount($conn, "SELECT COUNT(*) as total FROM products");
$order_count = getCount($conn, "SELECT COUNT(*) as total FROM orders");
$user_count  = getCount($conn, "SELECT COUNT(*) as total FROM users");
$low_stock   = getCount($conn, "SELECT COUNT(*) as total FROM products WHERE stock < 5");


// 2. Notification Count (Unread only galing sa orders at users)
$unread_count = getCount($conn, "SELECT (
    (SELECT COUNT(*) FROM orders WHERE is_read = 0) +
    (SELECT COUNT(*) FROM users WHERE is_read = 0)
) as total");
// 3. Sales Analytics Logic (Fixed Chart Query)

$analytics_query = "SELECT MONTHNAME(created_at) as month, SUM(total_amount) as total
                    FROM orders
                    GROUP BY MONTH(created_at)
                    ORDER BY MONTH(created_at) ASC LIMIT 5";
$analytics_res = mysqli_query($conn, $analytics_query);

$sales_data = [];
$months = [];
if ($analytics_res) {
    while($row = mysqli_fetch_assoc($analytics_res)) {
        $sales_data[] = $row['total'];
        $months[] = $row['month'];
    }
}
$max_sales = (count($sales_data) > 0) ? max($sales_data) : 1;
// 4. Recent Updates (Activity Feed)
$updates_query = "(SELECT 'order' as type, total_amount as info, created_at FROM orders)
                  UNION
                  (SELECT 'user' as type, name as info, created_at FROM users)
                  ORDER BY created_at DESC LIMIT 5";

$updates_res = mysqli_query($conn, $updates_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Admin Dashboard</title>
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

        /* Main Navbar */
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .top-navbar { background: white; padding: 15px 40px; display: flex; align-items: center; justify-content: flex-end; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 90; }
        /* Bell Badge Styling */

        .notif-btn { position: relative; color: #64748b; font-size: 20px; text-decoration: none; padding: 5px; }
        .notif-badge {
            position: absolute; top: -5px; right: -5px;
            background: #ef4444; color: white; font-size: 10px;
            font-weight: 800; padding: 2px 6px; border-radius: 50%;
            border: 2px solid white;
        }

        /* Dashboard Grid */

        .dashboard-container { padding: 40px; }
        .header-section { margin-bottom: 35px; }
        .header-section h1 { font-size: 28px; font-weight: 800; color: var(--text-main); margin: 0; }
        .header-section p { color: var(--text-muted); margin: 5px 0 0 0; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; border: 1px solid #e2e8f0; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 20px; }
        .icon-prod { background: #eff6ff; color: #3b82f6; }
        .icon-order { background: #fef9c3; color: #ca8a04; }
        .icon-user { background: #f0fdf4; color: #16a34a; }
        .icon-alert { background: #fef2f2; color: #dc2626; }

        .stat-card h3 { font-size: 32px; font-weight: 800; margin: 0; color: var(--text-main); }

        .stat-card p { color: var(--text-muted); font-weight: 600; font-size: 14px; margin: 5px 0 0 0; }
        /* Content Grid */
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .info-box { background: white; border-radius: 20px; padding: 30px; border: 1px solid #e2e8f0; }
        .info-box h2 { font-size: 18px; font-weight: 700; margin-top: 0; margin-bottom: 20px; color: var(--text-main); }

        /* Chart Bar Styling */
        .chart-container {
            height: 200px; display: flex; align-items: flex-end; gap: 20px;
            padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;
        }
        .bar {
            width: 45px; background: var(--primary-yellow); border-radius: 6px 6px 0 0;
            position: relative; transition: 0.3s; cursor: pointer;

        }

        .bar:hover { filter: brightness(0.9); }
        .bar-label {
            position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%);
            font-size: 11px; color: var(--text-muted); white-space: nowrap;

        }
        .update-list { list-style: none; padding: 0; margin: 0; }
        .update-item { padding: 12px 0; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; font-size: 14px; color: var(--text-muted); }

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
            <li class="active"><a href="index.php"><i class="fa-solid fa-gauge"></i> &nbsp; Dashboard</a></li>
            <li><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> &nbsp; Orders</a></li>
            <li><a href="products.php"><i class="fa-solid fa-box"></i> &nbsp; Products</a></li>
            <li><a href="categories.php"><i class="fa-solid fa-folder"></i> &nbsp; Categories</a></li>
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
                <h1>Dashboard Overview</h1>
                <p>Monitor your shop's daily performance.</p>
            </div>
            <div class="stats-grid">

                <div class="stat-card">

                    <div class="stat-icon icon-prod"><i class="fa-solid fa-box"></i></div>
                    <h3><?php echo $prod_count; ?></h3>
                    <p>Total Products</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-order"><i class="fa-solid fa-cart-shopping"></i></div>
                    <h3><?php echo $order_count; ?></h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-user"><i class="fa-solid fa-users"></i></div>
                    <h3><?php echo $user_count; ?></h3>
                    <p>Users</p>
                </div>
                <div class="stat-card" style="<?php echo ($low_stock > 0) ? 'border-color: #fecaca;' : ''; ?>">
                    <div class="stat-icon icon-alert"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h3 style="<?php echo ($low_stock > 0) ? 'color: #ef4444;' : ''; ?>"><?php echo $low_stock; ?></h3>
                    <p>Low Stock</p>
                </div>
            </div>

            <div class="content-grid">
                <div class="info-box">
                    <h2>Monthly Revenue</h2>
                    <div class="chart-container">
                        <?php if(empty($sales_data)): ?>
                            <div style="color: var(--text-muted); width: 100%; text-align: center; font-size: 14px;">No data yet.</div>
                        <?php else: ?>
                            <?php foreach($sales_data as $index => $val): 
                                $height = ($val / $max_sales) * 100;
                            ?>
                                <div class="bar" style="height: <?php echo max($height, 5); ?>%;" title="₱<?php echo number_format($val); ?>">
                                    <span class="bar-label"><?php echo $months[$index]; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 35px; color: var(--text-muted); font-size: 12px;">
                        * This chart shows revenue from the last 5 months.
                    </div>
                </div>

                <div class="info-box">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="margin:0;">Recent Activity</h2>
                        <a href="notifications.php" style="font-size: 12px; color: #3b82f6; text-decoration: none;">View All</a>
                    </div>
                    <ul class="update-list">
                        <?php if($updates_res && mysqli_num_rows($updates_res) > 0): ?>
                            <?php while($up = mysqli_fetch_assoc($updates_res)): ?>
                                <li class="update-item">
                                    <i class="fa-solid fa-circle" style="color: <?php echo ($up['type'] == 'order') ? '#ca8a04' : '#16a34a'; ?>; font-size: 8px;"></i>
                                    <div>
                                        <?php if($up['type'] == 'order'): ?>
                                            Order: <b>₱<?php echo number_format($up['info']); ?></b>
                                        <?php else: ?>
                                            New User: <b><?php echo htmlspecialchars($up['info']); ?></b>
                                        <?php endif; ?>
                                        <br><small style="color: #94a3b8; font-size: 11px;"><?php echo date('M d, g:i A', strtotime($up['created_at'])); ?></small>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li style="color: var(--text-muted); font-size: 14px; text-align: center; padding: 20px;">No activity yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>