<?php
// 1. Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../config/db_connect.php');

// 2. Auth Check
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// 3. Mark all as read logic
if(isset($_GET['mark_all_read'])) {
    mysqli_query($conn, "UPDATE orders SET is_read = 1");
    mysqli_query($conn, "UPDATE users SET is_read = 1");
    header("Location: notifications.php");
    exit();
}

// 4. Fetch Unread (Para sa Main List)
$unread_query = "(SELECT 'order' as type, id, total_amount as info, created_at FROM orders WHERE is_read = 0)
                 UNION
                 (SELECT 'user' as type, id, name as info, created_at FROM users WHERE is_read = 0)
                 ORDER BY created_at DESC";
$unread_res = mysqli_query($conn, $unread_query);

// 5. Fetch Read (History)
$read_query = "(SELECT 'order' as type, id, total_amount as info, created_at FROM orders WHERE is_read = 1)
               UNION
               (SELECT 'user' as type, id, name as info, created_at FROM users WHERE is_read = 1)
               ORDER BY created_at DESC LIMIT 15";
$read_res = mysqli_query($conn, $read_query);

// 6. Notification Count para sa Bell Icon sa Navbar
$count_res = mysqli_query($conn, "SELECT (
    (SELECT COUNT(*) FROM orders WHERE is_read = 0) + 
    (SELECT COUNT(*) FROM users WHERE is_read = 0)
) as total");
$count_row = mysqli_fetch_assoc($count_res);
$unread_count = $count_row['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-yellow: #facc15;
            --dark-sidebar: #1e293b;
            --bg-light: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; display: flex; }

        /* Sidebar */
        .sidebar { width: 260px; background: var(--dark-sidebar); height: 100vh; position: fixed; color: white; padding: 20px 0; z-index: 100; }
        .sidebar-header { text-align: center; padding-bottom: 30px; }
        .logo-box { width: 80px; height: 80px; margin: 0 auto; border: 3px solid var(--primary-yellow); border-radius: 50%; overflow: hidden; }
        .sidebar-menu { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-menu li a { color: #94a3b8; text-decoration: none; padding: 15px 25px; display: block; font-weight: 500; transition: 0.3s; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: #334155; color: white; border-left: 4px solid var(--primary-yellow); }

        /* Main Content */
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        
        /* Navbar */
        .top-navbar { background: white; padding: 15px 40px; display: flex; align-items: center; justify-content: flex-end; border-bottom: 1px solid #e2e8f0; }
        .notif-btn { position: relative; color: #64748b; font-size: 20px; text-decoration: none; padding: 5px; display: flex; align-items: center; margin-right: 20px; }
        .notif-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 50%; border: 2px solid white; }
        .nav-profile-img { width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary-yellow); object-fit: cover; }

        /* Notifications Styling */
        .notif-wrapper { padding: 40px; max-width: 900px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .section-header h2 { font-size: 24px; font-weight: 800; color: var(--text-main); margin: 0; }
        
        .btn-mark-all { background: var(--text-main); color: white; text-decoration: none; padding: 10px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; transition: 0.3s; }
        .btn-mark-all:hover { background: #334155; transform: translateY(-1px); }

        .notif-card { 
            background: white; padding: 20px; border-radius: 15px; border: 1px solid #e2e8f0; 
            margin-bottom: 12px; display: flex; align-items: center; gap: 20px; 
            transition: 0.2s;
        }
        .notif-card.unread { border-left: 5px solid var(--primary-yellow); background: #fffde7; }
        .notif-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }

        .notif-icon { 
            width: 48px; height: 48px; border-radius: 12px; 
            display: flex; align-items: center; justify-content: center; font-size: 20px;
        }
        .icon-order { background: #fef9c3; color: #a16207; }
        .icon-user { background: #dcfce7; color: #15803d; }
        .icon-history { background: #f1f5f9; color: #94a3b8; }

        .notif-content { flex: 1; }
        .notif-content b { display: block; color: var(--text-main); font-size: 16px; margin-bottom: 4px; }
        .notif-content small { color: var(--text-muted); font-weight: 500; }

        .empty-state { text-align: center; padding: 50px; color: var(--text-muted); background: white; border-radius: 15px; border: 1px dashed #cbd5e1; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-box"><img src="../character.jpg" style="width: 100%; height: 100%; object-fit: cover;"></div>
            <h3 style="font-size: 14px; margin-top: 15px; letter-spacing: 1px;">SPARKVERSE ADMIN</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-gauge"></i> &nbsp; Dashboard</a></li>
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
            <a href="notifications.php" class="notif-btn">
                <i class="fa-solid fa-bell"></i>
                <?php if($unread_count > 0): ?>
                    <span class="notif-badge"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
            <a href="profile.php">
                <img src="../character.jpg" class="nav-profile-img">
            </a>
        </header>

        <div class="notif-wrapper">
            <div class="section-header">
                <h2>New Notifications</h2>
                <?php if(mysqli_num_rows($unread_res) > 0): ?>
                    <a href="?mark_all_read=1" class="btn-mark-all">Mark all as read</a>
                <?php endif; ?>
            </div>

            <?php if(mysqli_num_rows($unread_res) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($unread_res)): ?>
                    <div class="notif-card unread">
                        <div class="notif-icon <?= ($row['type']=='order') ? 'icon-order' : 'icon-user' ?>">
                            <i class="fa-solid <?= ($row['type']=='order') ? 'fa-cart-plus' : 'fa-user-plus' ?>"></i>
                        </div>
                        <div class="notif-content">
                            <b>
                                <?= ($row['type']=='order') ? "New Order: ₱".number_format($row['info'], 2) : "Welcome, ".htmlspecialchars($row['info'])."!"; ?>
                            </b>
                            <small><i class="fa-regular fa-clock"></i> <?= date('M d, Y • h:i A', strtotime($row['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-circle-check" style="font-size: 40px; margin-bottom: 15px; color: #16a34a;"></i>
                    <p>All caught up! No new notifications.</p>
                </div>
            <?php endif; ?>

            <div class="section-header" style="margin-top: 50px;">
                <h2>Recent History</h2>
            </div>

            <?php if(mysqli_num_rows($read_res) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($read_res)): ?>
                    <div class="notif-card">
                        <div class="notif-icon icon-history">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="notif-content">
                            <span style="display: block; color: var(--text-main); font-weight: 500;">
                                <?= ($row['type']=='order') ? "Order of ₱".number_format($row['info'], 2)." processed" : "User ".htmlspecialchars($row['info'])." joined Sparkverse"; ?>
                            </span>
                            <small><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: var(--text-muted); font-size: 14px;">No history recorded.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>