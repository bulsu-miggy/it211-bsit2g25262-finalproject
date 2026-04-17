<?php
session_start();
include 'db.php';

// Proteksyon para sa mga hindi naka-login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Logic para sa Cancel Order
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    // Pwede lang i-cancel kung pending pa
    mysqli_query($conn, "UPDATE orders SET status = 'cancelled' WHERE id = '$order_id' AND user_id = '$user_id' AND status = 'pending'");
    header("Location: my_orders.php");
    exit();
}

$query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sparkverse | My Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fdfdfd; margin: 0; padding-top: 100px; }
        
        /* --- NAVIGATION BAR (EXACT COPY FROM MERCHANDISE.PHP) --- */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0;
            background: white; border-top: 5px solid #FFD700; 
            border-bottom: 2px solid #FFD700; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); z-index: 1000;
            height: 80px;
            display: flex;
            align-items: center;
        }

        .nav-container { 
            width: 95%; max-width: 1400px; margin: 0 auto; 
            display: flex; align-items: center; justify-content: space-between;
        }

        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-section img { height: 55px; width: 55px; border-radius: 50%; }
        .logo-section span { font-weight: 800; font-size: 22px; color: #000; letter-spacing: 0.5px; }

        .nav-links { display: flex; gap: 30px; align-items: center; }
        .nav-links a { 
            text-decoration: none; color: #00B4D8; font-weight: 700; font-size: 16px; 
            transition: 0.3s;
        }
        .nav-links a.active { color: #00B4D8; border-bottom: 2px solid #00B4D8; }

        .nav-right { display: flex; align-items: center; gap: 20px; }
        .nav-right a { text-decoration: none; font-size: 24px; color: #333; display: flex; align-items: center; }
        .logout-btn { 
            color: #C1121F !important; font-weight: 700; font-size: 18px !important; 
            margin-left: 5px;
        }

        /* --- ORDERS LAYOUT STYLING --- */
        .main-wrapper { padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .page-title { color: #00B4D8; font-size: 32px; font-weight: bold; margin-bottom: 25px; }
        
        .order-card { 
            background: white; border-radius: 15px; padding: 25px; 
            margin-bottom: 20px; border: 1px solid #f0f0f0; 
            transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            display: flex; flex-direction: column; gap: 15px;
        }
        .order-header { 
            display: flex; justify-content: space-between; 
            align-items: center; border-bottom: 2px solid #f8fafc; 
            padding-bottom: 15px; 
        }

        .order-id { font-weight: 800; color: #333; font-size: 18px; }
        
        /* Status Badge Styling */
        .status-badge { 
            padding: 8px 16px; border-radius: 25px; font-size: 12px; 
            font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
        }
        .pending { background: #FFF9C4; color: #FBC02D; }
        .completed { background: #C8E6C9; color: #388E3C; }
        .cancelled { background: #FFCDD2; color: #D32F2F; }

        .order-details { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .order-details p { margin: 0; color: #666; font-size: 14px; }
        .order-details strong { color: #333; }
        .price-total { color: #00B4D8; font-size: 20px; font-weight: 800; }

        /* Cancel Button Styling (Yellow like the Add Button) */
        .cancel-btn { 
            align-self: flex-start; background: #FFD700; border: none; 
            padding: 10px 20px; border-radius: 8px; color: white; 
            cursor: pointer; font-weight: 700; font-size: 14px; 
            transition: 0.3s; box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
        }
        .cancel-btn:hover { background: #e6c200; transform: translateY(-2px); }
        
        .empty-state { text-align: center; padding: 50px; color: #94a3b8; }
    </style>
</head>
<body>

<header class="navbar">
    <div class="nav-container">
        <div class="logo-section">
            <img src="character.png" alt="Logo">
            <span>SPARKVERSE</span>
        </div>

        <nav class="nav-links">
            <a href="home.php">Home</a>
            <a href="albums.php">Albums</a>
            <a href="photocards.php">Photocards</a>
            <a href="lightsticks.php">Lightsticks</a>
            <a href="merchandise.php">Merchandise</a>
            <a href="giftcards.php">Gift Cards</a>
            <a href="my_orders.php" class="active">My Orders</a>
        </nav>

        <div class="nav-right">
            <a href="cart.php" title="Cart">🛒</a>
            <a href="profile.php" title="Profile">👤</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</header>

<div class="main-wrapper">
    <div class="container">
        <h1 class="page-title">My Orders</h1>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">#ORD-<?php echo $row['id']; ?></div>
                        <div class="status-badge <?php echo strtolower($row['status']); ?>">
                            <?php echo strtoupper($row['status']); ?>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <p><strong>Order Date:</strong> <?php echo date('M d, Y', strtotime($row['created_at'])); ?></p>
                        <p><strong>Shipping:</strong> Standard Delivery</p>
                        <p><strong>Total Amount:</strong> <span class="price-total">₱ <?php echo number_format($row['total_amount'], 2); ?></span></p>
                    </div>

                    <?php if($row['status'] == 'pending'): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="cancel_order" class="cancel-btn">
                                <i class="fa-solid fa-xmark"></i> CANCEL ORDER
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-box-open" style="font-size: 60px; margin-bottom: 20px;"></i>
                <p>You haven't placed any orders yet.</p>
                <a href="merchandise.php" style="color: #00B4D8; font-weight: 700; text-decoration: none;">Go Shopping →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>