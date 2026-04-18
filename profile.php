<?php
session_start();
require "connect.php"; 

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// 2. Handle Profile Update
if (isset($_POST['update_profile'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email    = $_POST['email'];

    try {
        // Check if username is taken by someone else
        $check = $conn->prepare("SELECT id FROM customers WHERE username = ? AND id != ?");
        $check->execute([$username, $user_id]);
        
        if ($check->fetch()) {
            $error_msg = "Username is already taken by another user.";
        } else {
            // Update the database
            $stmt = $conn->prepare("UPDATE customers SET fullname = ?, username = ?, email = ? WHERE id = ?");
            $stmt->execute([$fullname, $username, $email, $user_id]);
            
            // Update session name for the header
            $_SESSION['username'] = $username;
            $success_msg = "Profile updated successfully!";
        }
    } catch (PDOException $e) {
        $error_msg = "Update failed: " . $e->getMessage();
    }
}

// 3. Fetch current user data
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$display_name = $_SESSION['username'] ?? "User";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - SPORTIFY</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-card {
            background: #1a1a1a;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #333;
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #888;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: #222;
            border: 1px solid #444;
            color: white;
            border-radius: 4px;
            font-size: 16px;
        }
        .save-btn {
            background: white;
            color: black;
            border: none;
            padding: 12px 25px;
            font-weight: 800;
            border-radius: 4px;
            cursor: pointer;
            text-transform: uppercase;
        }
        .msg {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
        .success { background: rgba(0, 255, 0, 0.1); color: #00ff00; border: 1px solid #00ff00; }
        .error { background: rgba(255, 0, 0, 0.1); color: #ff0000; border: 1px solid #ff0000; }
    </style>
</head>
<body>

<div class="app-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-box"><img src="image/logo.jpg" alt="Logo" style="width:100%;"></div>
           
            <h1 class="logo-text">SPORTIFY</h1>
        </div>
        <nav class="nav-menu">
            <a href="homepage.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="order.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="products.php" class="nav-item"><i class="fas fa-box"></i> Products</a>
            <a href="categories.php" class="nav-item"><i class="fas fa-filter"></i> Filters</a>
            <a href="customers.php" class="nav-item"><i class="fas fa-users"></i> Customers</a>
            <a href="analytics.php" class="nav-item"><i class="fas fa-chart-line"></i> Analytics</a>
            <div class="nav-divider"></div>
            <a href="profile.php" class="nav-item active"><i class="fas fa-user-gear"></i> Profile</a>
            <a href="logout.php" class="nav-item logout-btn"><i class="fas fa-power-off"></i> Logout</a>
        </nav>
    </aside>

    <main class="content-area">
        <header class="top-header">
            <div class="header-title-area">
                <h1>Account Settings</h1>
             
                <p>Manage your personal information</p>
            </div>
            <div class="header-actions">
                <div class="user-pill"><span><?php echo htmlspecialchars($display_name); ?></span></div>
            </div>
        </header>

        <section style="margin-top: 30px;">
            <?php if ($success_msg): ?>
                <div class="msg success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="msg error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="profile-card">
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
                </form>
            </div>
        </section>
    </main>
</div>

</body>
</html>