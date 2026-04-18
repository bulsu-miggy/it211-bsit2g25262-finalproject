<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php');

// 1. Kunin ang ID ng user mula sa URL
if(isset($_GET['id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 2. Query para sa Personal Info ng User
    $user_query = "SELECT * FROM users WHERE id = '$user_id'";
    $user_result = mysqli_query($conn, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);

    if(!$user_data) {
        echo "Customer not found!";
        exit();
    }

    // 3. Query para sa Order History ng User na ito
    $order_query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY id DESC";
    $order_result = mysqli_query($conn, $order_query);
} else {
    header("Location: customers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile | <?php echo htmlspecialchars($user_data['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-yellow: #facc15;
            --dark-sidebar: #1e293b;
            --bg-light: #f8fafc;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; padding: 40px; }
        
        .container { max-width: 1000px; margin: 0 auto; }
        
        .back-link { text-decoration: none; color: #64748b; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 25px; transition: 0.3s; }
        .back-link:hover { color: #1e293b; }

        .profile-header {
            background: white; border-radius: 20px; padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            display: flex; align-items: center; gap: 35px; margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .avatar-circle, .profile-img {
            width: 100px; height: 100px; 
            border-radius: 50%; display: flex; 
            align-items: center; justify-content: center;
            font-size: 40px; font-weight: 800;
            object-fit: cover;
            border: 3px solid var(--primary-yellow);
        }
        .avatar-circle { background: #dbeafe; color: #3b82f6; }

        .user-meta { flex-grow: 1; }
        .user-meta h1 { margin: 0; font-size: 28px; color: #0f172a; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
        .info-grid p { margin: 0; color: #64748b; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .info-grid i { color: #94a3b8; width: 16px; }

        .id-badge { background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #475569; display: inline-block; margin-top: 10px; }

        .history-card { background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0; }
        .card-title { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #1e293b; font-size: 18px; }

        .order-table { width: 100%; border-collapse: collapse; }
        .order-table th { background: #f8fafc; padding: 15px 25px; text-align: left; color: #64748b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .order-table td { padding: 18px 25px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #475569; }

        .status-pill { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: capitalize; }
        .status-pending { background: #fff7ed; color: #c2410c; }
        .status-completed { background: #ecfdf5; color: #047857; }
        .status-cancelled { background: #fef2f2; color: #b91c1c; }

        .price-text { font-weight: 700; color: #1e293b; }
    </style>
</head>
<body>

    <div class="container">
        <a href="customers.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Customer List
        </a>

        <div class="profile-header">
            <?php 
                // Inayos na logic para sa Profile Picture
                $profile_pic = $user_data['profile_picture'];
                $pic_path = "../uploads/profiles/" . $profile_pic;
                
                if(!empty($profile_pic) && file_exists($pic_path) && $profile_pic != 'default_avatar.png'): 
            ?>
                <img src="<?php echo $pic_path; ?>" class="profile-img" alt="Profile">
            <?php else: ?>
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($user_data['name'], 0, 1)); ?>
                </div>
            <?php endif; ?>

            <div class="user-meta">
                <h1><?php echo htmlspecialchars($user_data['name']); ?></h1>
                
                <div class="info-grid">
                    <p><i class="fa-regular fa-envelope"></i> <?php echo htmlspecialchars($user_data['email']); ?></p>
                    <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($user_data['phone_number'] ?? 'N/A'); ?></p>
                    <p><i class="fa-solid fa-venus-mars"></i> <?php echo htmlspecialchars($user_data['gender'] ?? 'Not specified'); ?></p>
                    <p><i class="fa-solid fa-cake-candles"></i> <?php echo htmlspecialchars($user_data['age'] ?? '0'); ?> years old</p>
                    <p style="grid-column: span 2;"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($user_data['address'] ?? 'No address provided'); ?></p>
                </div>

                <span class="id-badge">Customer ID: #<?php echo $user_data['id']; ?></span>
            </div>
        </div>

        <div class="history-card">
            <div class="card-title">Order History</div>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date and Time</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($order_result) > 0): ?>
                        <?php while($order = mysqli_fetch_assoc($order_result)): ?>
                        <tr>
                            <td style="font-weight: 700; color: #3b82f6;">#<?php echo $order['id']; ?></td>
                            <td>
                                <?php 
                                    // Gamitin ang database column name para sa date (defaulting to 'now' if null for safety)
                                    $o_date = $order['created_at'] ?? 'now'; 
                                    echo date('M d, Y | h:i A', strtotime($o_date)); 
                                ?>
                            </td>
                            <td class="price-text">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <?php 
                                    $status = $order['status'] ?? 'pending';
                                    $status_class = 'status-' . strtolower($status);
                                ?>
                                <span class="status-pill <?php echo $status_class; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fa-solid fa-box-open" style="font-size: 30px; display: block; margin-bottom: 10px;"></i>
                                This customer has no order history yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>