<?php
// 1. Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../config/db_connect.php'); 

// 2. Auth Check - Siguraduhin na 'admin_id' ang session variable mo
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// --- LOGIC: Pag-save ng inedit na info ---
if (isset($_POST['save_profile'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $pcode = mysqli_real_escape_string($conn, $_POST['postal_code']);

    // UPDATE: Pinalitan ang table name tungong 'admin'
    $update_sql = "UPDATE admin SET 
                    first_name = '$fname', 
                    last_name = '$lname', 
                    email = '$email', 
                    country = '$country', 
                    city = '$city', 
                    postal_code = '$pcode' 
                  WHERE id = '$admin_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        // Update session name para mag-reflect agad sa UI
        $_SESSION['admin_name'] = $fname . ' ' . $lname;
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile: " . mysqli_error($conn) . "');</script>";
    }
}

// 3. Fetch Admin Data - Pinalitan ang table name tungong 'admin'
$admin_query = mysqli_query($conn, "SELECT * FROM admin WHERE id = '$admin_id'");
$admin = mysqli_fetch_assoc($admin_query);

// 4. Notification Count (Stays the same based on your existing logic)
$unread_res = mysqli_query($conn, "SELECT (
    (SELECT COUNT(*) FROM orders WHERE is_read = 0) + 
    (SELECT COUNT(*) FROM users WHERE is_read = 0)
) as total");
$unread_row = mysqli_fetch_assoc($unread_res);
$unread_count = $unread_row['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-yellow: #facc15;
            --dark-sidebar: #1e293b;
            --bg-light: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --orange-btn: #f97316;
            --green-btn: #16a34a;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; display: flex; }

        .sidebar { width: 260px; background: var(--dark-sidebar); height: 100vh; position: fixed; color: white; padding: 20px 0; z-index: 100; }
        .sidebar-header { text-align: center; padding-bottom: 30px; }
        .logo-box { width: 80px; height: 80px; margin: 0 auto; border: 3px solid var(--primary-yellow); border-radius: 50%; overflow: hidden; }
        .sidebar-menu { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-menu li a { color: #94a3b8; text-decoration: none; padding: 15px 25px; display: block; font-weight: 500; transition: 0.3s; }
        .sidebar-menu li a:hover { background: #334155; color: white; border-left: 4px solid var(--primary-yellow); }

        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .top-navbar { background: white; padding: 15px 40px; display: flex; align-items: center; justify-content: flex-end; border-bottom: 1px solid #e2e8f0; }
        .notif-btn { position: relative; color: #64748b; font-size: 20px; text-decoration: none; padding: 5px; display: flex; align-items: center; margin-right: 20px; }
        .notif-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 50%; border: 2px solid white; }
        .nav-profile-img { width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary-yellow); object-fit: cover; }

        .profile-wrapper { padding: 40px; }
        .profile-banner { background: white; border-radius: 15px; padding: 30px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .banner-avatar { width: 100px; height: 100px; border-radius: 50%; border: 4px solid var(--primary-yellow); object-fit: cover; }
        .banner-info h2 { margin: 0; font-size: 32px; font-weight: 700; color: #1e293b; }
        .banner-info p { margin: 5px 0 0; color: var(--text-muted); font-size: 18px; }

        .info-card { background: white; border-radius: 15px; padding: 35px; border: 1px solid #e2e8f0; }
        .info-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .info-header h3 { margin: 0; font-size: 22px; font-weight: 700; color: #000; }
        
        .edit-btn, .save-btn { border: none; cursor: pointer; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; font-size: 14px; font-family: 'Inter'; }
        .edit-btn { background: var(--orange-btn); text-decoration: none; }
        .save-btn { background: var(--green-btn); display: none; }

        .data-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        .data-item { margin-bottom: 5px; }
        .data-label { color: var(--text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: block; }
        
        .editable-input { 
            width: 100%; border: 1px solid transparent; background: transparent; 
            color: #1e293b; font-size: 16px; font-weight: 700; padding: 8px 0;
            outline: none; transition: 0.2s;
        }
        .editable-input.active { border-bottom: 2px solid var(--primary-yellow); background: #fffde7; padding: 8px; border-radius: 4px; }
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
                <i class="fa-regular fa-bell"></i>
                <?php if($unread_count > 0): ?> <span class="notif-badge"><?= $unread_count ?></span> <?php endif; ?>
            </a>
            <img src="../character.jpg" class="nav-profile-img">
        </header>

        <div class="profile-wrapper">
            <form action="profile.php" method="POST" id="profileForm">
                <div class="profile-banner">
                    <img src="../character.jpg" class="banner-avatar">
                    <div class="banner-info">
                        <h2><?= htmlspecialchars(($admin['first_name'] ?? 'Admin') . ' ' . ($admin['last_name'] ?? '')) ?></h2>
                        <p>Administrator (ID: <?= htmlspecialchars($admin['id'] ?? 'N/A') ?>)</p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-header">
                        <h3>Personal Information</h3>
                        <button type="button" class="edit-btn" id="editToggle">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Profile
                        </button>
                        <button type="submit" name="save_profile" class="save-btn" id="saveBtn">
                            <i class="fa-solid fa-check"></i> Save Changes
                        </button>
                    </div>

                    <div class="data-grid">
                        <div class="data-item">
                            <span class="data-label">First Name</span>
                            <input type="text" name="first_name" class="editable-input" value="<?= htmlspecialchars($admin['first_name'] ?? '') ?>" readonly>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Last Name</span>
                            <input type="text" name="last_name" class="editable-input" value="<?= htmlspecialchars($admin['last_name'] ?? '') ?>" readonly>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Email Address</span>
                            <input type="email" name="email" class="editable-input" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" readonly>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Country</span>
                            <input type="text" name="country" class="editable-input" value="<?= htmlspecialchars($admin['country'] ?? '') ?>" readonly>
                        </div>
                        <div class="data-item">
                            <span class="data-label">City</span>
                            <input type="text" name="city" class="editable-input" value="<?= htmlspecialchars($admin['city'] ?? '') ?>" readonly>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Postal Code</span>
                            <input type="text" name="postal_code" class="editable-input" value="<?= htmlspecialchars($admin['postal_code'] ?? '') ?>" readonly>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const editToggle = document.getElementById('editToggle');
        const saveBtn = document.getElementById('saveBtn');
        const inputs = document.querySelectorAll('.editable-input');

        editToggle.addEventListener('click', () => {
            editToggle.style.display = 'none';
            saveBtn.style.display = 'flex';
            inputs.forEach(input => {
                input.readOnly = false;
                input.classList.add('active');
            });
        });
    </script>
</body>
</html>