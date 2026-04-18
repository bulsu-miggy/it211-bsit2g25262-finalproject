<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php'); 

// --- NEW: NOTIFICATION LOGIC (Galing sa index.php) ---
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
// ---------------------------------------------------

// --- STATUS UPDATE LOGIC ---
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
    header("Location: orders.php?msg=updated");
    exit();
}

// --- SEARCH AT FILTER LOGIC ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$query = "SELECT orders.*, users.name as customer_name 
          FROM orders 
          LEFT JOIN users ON orders.user_id = users.id WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (users.name LIKE '%$search%' OR orders.id LIKE '%$search%')";
}

if (!empty($filter_status) && $filter_status != 'All Status') {
    $query .= " AND orders.status = '$filter_status'";
}

$query .= " ORDER BY orders.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Manage Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary-yellow: #facc15; 
            --dark-sidebar: #1e293b; 
            --bg-light: #f8fafc; 
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; display: flex; }

        /* Sidebar & Layout */
        .sidebar { width: 260px; background: var(--dark-sidebar); height: 100vh; position: fixed; color: white; padding: 20px 0; z-index: 100; }
        .sidebar-header { text-align: center; padding-bottom: 30px; }
        .logo-box { width: 80px; height: 80px; margin: 0 auto; border: 3px solid var(--primary-yellow); border-radius: 50%; overflow: hidden; }
        .sidebar-menu { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-menu li a { color: #94a3b8; text-decoration: none; padding: 15px 25px; display: block; font-weight: 500; transition: 0.3s; }
        .sidebar-menu li.active a, .sidebar-menu li a:hover { background: #334155; color: white; border-left: 4px solid var(--primary-yellow); }

        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        
        /* Updated Navbar Styling (Kopya sa index.php) */
        .top-navbar { 
            background: white; 
            padding: 15px 40px; 
            display: flex; 
            align-items: center; 
            justify-content: flex-end; 
            border-bottom: 1px solid #e2e8f0; 
            position: sticky; 
            top: 0; 
            z-index: 90; 
        }

        /* NEW: Badge Styling */
        .notif-btn { position: relative; color: #64748b; font-size: 20px; text-decoration: none; padding: 5px; display: flex; align-items: center; }
        .notif-badge { 
            position: absolute; top: -5px; right: -5px; 
            background: #ef4444; color: white; font-size: 10px; 
            font-weight: 800; padding: 2px 6px; border-radius: 50%; 
            border: 2px solid white;
        }
        
        .dashboard-container { padding: 40px; }
        .header-section { margin-bottom: 30px; }
        .header-section h1 { font-size: 28px; font-weight: 800; color: #0f172a; margin: 0; }
        .header-section p { color: #64748b; margin-top: 5px; }

        /* Filter Row Design */
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

        /* Table Styling */
        .box-placeholder { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0; }
        .inventory-table { width: 100%; border-collapse: collapse; }
        .inventory-table th { background: #f1f5f9; padding: 15px 20px; color: #475569; font-size: 12px; text-transform: uppercase; font-weight: 700; text-align: left; }
        .inventory-table td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

        /* Status Badges */
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-pending { background: #fef9c3; color: #a16207; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .view-btn { color: #3b82f6; background: #eff6ff; padding: 8px 12px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; }
        .view-btn:hover { background: #dbeafe; }
        
        .status-update-select { padding: 8px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: 'Inter'; font-size: 12px; cursor: pointer; background: #f8fafc; }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d1fae5; color: #10b981; }
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
            <li class="active"><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> &nbsp; Orders</a></li>
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
                <h1>Order Management</h1>
                <p>Track and process your customers' merchandise orders.</p>
            </div>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Order status updated successfully!</div>
            <?php endif; ?>

            <form action="" method="GET" class="filter-row">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" name="search" class="filter-input" style="width: 300px;" placeholder="Search Customer or Order ID..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <select name="status" class="filter-input">
                        <option value="All Status">All Status</option>
                        <option value="pending" <?php if($filter_status == 'pending') echo 'selected'; ?>>Pending</option>
                        <option value="completed" <?php if($filter_status == 'completed') echo 'selected'; ?>>Completed</option>
                        <option value="cancelled" <?php if($filter_status == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">Filters</button>
            </form>

            <div class="box-placeholder">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date Ordered</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td style="font-weight: 700; color: #1e293b;">#ORD-<?php echo $row['id']; ?></td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($row['customer_name'] ?? 'Guest User'); ?></span>
                                        <span style="font-size: 12px; color: #64748b;">Standard Delivery</span>
                                    </div>
                                </td>
                                <td style="font-weight: 700; color: #0f172a;">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                <td>
                                    <?php 
                                        $statusClass = 'status-pending';
                                        if($row['status'] == 'completed') $statusClass = 'status-completed';
                                        if($row['status'] == 'cancelled') $statusClass = 'status-cancelled';
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo $row['status']; ?></span>
                                </td>
                                <td style="color: #64748b; font-size: 13px;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                        <a href="order_details.php?id=<?php echo $row['id']; ?>" class="view-btn"><i class="fa-solid fa-eye"></i></a>
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                            <select name="new_status" class="status-update-select" onchange="this.form.submit()">
                                                <option value="pending" <?php echo ($row['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="completed" <?php echo ($row['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo ($row['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>