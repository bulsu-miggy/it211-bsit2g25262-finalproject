<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['save_shipping'])) {
    $_SESSION['shipping_name'] = $_POST['receiver_name'];
    $_SESSION['shipping_phone'] = $_POST['phone'];
    $_SESSION['shipping_address'] = $_POST['address'];
    echo "<script>alert('Shipping details saved!');</script>";
}

$discount_rate = 0;
$applied_voucher_title = "";

if (isset($_POST['apply_voucher'])) {
    $voucher_info = explode('|', $_POST['voucher_data']); 
    if(count($voucher_info) == 2) {
        $applied_voucher_title = $voucher_info[0];
        $discount_rate = (float)$voucher_info[1];
    }
}

if (isset($_POST['update_cart'])) {
    $cart_id = $_POST['cart_id'];
    $new_qty = $_POST['quantity'];
    mysqli_query($conn, "UPDATE cart SET quantity = '$new_qty' WHERE id = '$cart_id'");
}

if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM cart WHERE id = '$remove_id'");
    header("Location: cart.php");
}

$cart_query = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id'");
$subtotal = 0;

// Only show claimed vouchers that are NOT yet used
$vouchers_query = mysqli_query($conn, "SELECT * FROM user_vouchers WHERE user_id = '$user_id' AND is_used = 0");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Your Cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fdfdfd; margin: 0; padding-top: 100px; }
        .navbar { position: fixed; top: 0; left: 0; right: 0; background: white; border-top: 5px solid #FFD700; border-bottom: 2px solid #FFD700; box-shadow: 0 2px 10px rgba(0,0,0,0.05); z-index: 1000; height: 80px; display: flex; align-items: center; }
        .nav-container { width: 95%; max-width: 1400px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-section img { height: 55px; width: 55px; border-radius: 50%; }
        .logo-section span { font-weight: 800; font-size: 22px; color: #000; letter-spacing: 0.5px; }
        .nav-links { display: flex; gap: 30px; align-items: center; }
        .nav-links a { text-decoration: none; color: #00B4D8; font-weight: 700; font-size: 16px; transition: 0.3s; }
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .nav-right a { text-decoration: none; font-size: 24px; color: #333; display: flex; align-items: center; }
        .logout-btn { color: #C1121F !important; font-weight: 700; font-size: 18px !important; margin-left: 5px; }
        .cart-container { max-width: 1200px; margin: 20px auto; padding: 20px; display: flex; gap: 30px; align-items: flex-start; }
        .cart-left-section { flex: 2; }
        .cart-summary { flex: 1; background: #f9f9f9; padding: 30px; border-radius: 15px; border: 1px solid #eee; position: sticky; top: 120px; }
        .page-title { color: #00B4D8; font-size: 42px; font-weight: bold; margin-bottom: 20px; }
        .item-row { display: flex; align-items: center; background: white; padding: 20px; border-radius: 15px; margin-bottom: 15px; border: 1px solid #eee; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .item-img { width: 100px; height: 100px; object-fit: contain; border-radius: 10px; margin-right: 20px; border: 1px solid #f0f0f0; }
        .item-details { flex: 1; }
        .item-details h3 { font-size: 15px; margin: 0; color: #333; }
        .shipping-details, .payment-details { background: white; padding: 25px; border-radius: 15px; margin-top: 30px; border: 1px solid #eee; }
        .shipping-details h2, .payment-details h2 { color: #00B4D8; font-size: 24px; margin-bottom: 20px; }
        .form-group { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .input-box { display: flex; flex-direction: column; margin-bottom: 15px; }
        .input-box label { font-size: 12px; color: #666; margin-bottom: 5px; font-weight: bold; }
        .input-box input, .input-box textarea, .gift-card-input { padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 14px; }
        .cod-tag { background: #e0f7fa; color: #00B4D8; padding: 10px 15px; border-radius: 8px; font-weight: bold; display: inline-block; border: 1px dashed #00B4D8; }
        .btn-checkout { background-color: #00B4D8; color: white; border: none; width: 100%; padding: 15px; border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 18px; transition: 0.3s; }
        .btn-checkout:hover { background-color: #007791; }
        .btn-action { background: #FFD700; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 12px; font-weight: bold; }
        .btn-save { background: #00B4D8; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px; transition: 0.3s; }
        .remove-link { color: #ff4d4d; text-decoration: none; font-size: 12px; margin-left: 15px; font-weight: bold; }
    </style>
</head>
<body>
<header class="navbar">
    <div class="nav-container">
        <div class="logo-section"><img src="character.png" alt="Logo"><span>SPARKVERSE</span></div>
        <nav class="nav-links">
            <a href="home.php">Home</a><a href="albums.php">Albums</a><a href="photocards.php">Photocards</a>
            <a href="lightsticks.php">Lightsticks</a><a href="merchandise.php">Merchandise</a><a href="giftcards.php">Gift Cards</a>
            <a href="my_orders.php">My Orders</a>
        </nav>
        <div class="nav-right"><a href="cart.php" style="color: #00B4D8;">🛒</a><a href="profile.php">👤</a><a href="logout.php" class="logout-btn">Logout</a></div>
    </div>
</header>

<div class="cart-container">
    <div class="cart-left-section">
        <h1 class="page-title">Your cart</h1>
        <?php if (mysqli_num_rows($cart_query) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($cart_query)): 
                $item_total = $row['product_price'] * $row['quantity'];
                $subtotal += $item_total;
            ?>
                <div class="item-row">
                    <img src="<?php echo $row['product_image']; ?>" class="item-img">
                    <div class="item-details">
                        <h3><?php echo $row['product_name']; ?></h3>
                        <p class="price-text">₱ <?php echo number_format($row['product_price'], 2); ?></p>
                        <form method="POST" style="display:flex; align-items:center; gap: 10px;">
                            <input type="hidden" name="cart_id" value="<?php echo $row['id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" min="1" style="width:50px; padding:5px; border-radius:5px; border:1px solid #ddd;">
                            <button type="submit" name="update_cart" class="btn-action">UPDATE</button>
                            <a href="cart.php?remove=<?php echo $row['id']; ?>" class="remove-link">REMOVE</a>
                        </form>
                    </div>
                    <div style="text-align: right;"><p style="font-weight:bold; color:#00B4D8; font-size: 18px;">₱ <?php echo number_format($item_total, 2); ?></p></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="item-row"><p>Your cart is empty. <a href="home.php" style="color:#00B4D8;">Shop now!</a></p></div>
        <?php endif; ?>

        <div class="shipping-details">
            <h2>Shipping Details</h2>
            <form method="POST" action="cart.php">
                <div class="form-group">
                    <div class="input-box">
                        <label>Receiver's Name</label>
                        <input type="text" name="receiver_name" value="<?php echo isset($_SESSION['shipping_name']) ? $_SESSION['shipping_name'] : 'Celene'; ?>" required>
                    </div>
                    <div class="input-box">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo isset($_SESSION['shipping_phone']) ? $_SESSION['shipping_phone'] : ''; ?>" placeholder="09XXXXXXXXX" required>
                    </div>
                </div>
                <div class="input-box">
                    <label>Complete Shipping Address</label>
                    <textarea name="address" rows="3" required><?php echo isset($_SESSION['shipping_address']) ? $_SESSION['shipping_address'] : ''; ?></textarea>
                </div>
                <button type="submit" name="save_shipping" class="btn-save">SAVE CHANGES</button>
            </form>
        </div>
        <div class="payment-details">
            <h2>Mode of Payment</h2>
            <div class="input-box">
                <label>Available Method:</label>
                <div><span class="cod-tag">💵 Cash on Delivery (COD)</span></div>
            </div>
        </div>
    </div>

    <div class="cart-summary">
        <h2 style="color: #333; margin-top: 0;">Order Summary</h2>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <div style="display: flex; justify-content: space-between; font-size: 18px; margin-bottom: 10px;">
            <span>Subtotal</span><span>₱ <?php echo number_format($subtotal, 2); ?></span>
        </div>

        <?php if ($discount_rate > 0): 
            $discount_amount = $subtotal * $discount_rate;
            $subtotal -= $discount_amount; ?>
            <div style="display: flex; justify-content: space-between; font-size: 16px; margin-bottom: 10px; color: #ff4d4d;">
                <span>Discount (<?php echo $discount_rate * 100; ?>%)</span>
                <span>- ₱ <?php echo number_format($discount_amount, 2); ?></span>
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; font-size: 22px; font-weight: bold; color: #00B4D8; margin-top: 10px;">
            <span>Total</span><span>₱ <?php echo number_format($subtotal, 2); ?></span>
        </div>
        
        <form method="POST" style="margin-top: 20px;">
            <div class="input-box">
                <label style="color: #00B4D8;">Select Claimed Voucher</label>
                <div style="display: flex; gap: 5px;">
                    <select class="gift-card-input" name="voucher_data" style="flex: 1;">
                        <option value="0|0">No voucher selected</option>
                        <?php while($v = mysqli_fetch_assoc($vouchers_query)): 
                            $rate = 0;
                            if(strpos($v['voucher_title'], '10%') !== false) $rate = 0.10;
                            elseif(strpos($v['voucher_title'], '20%') !== false) $rate = 0.20;
                            elseif(strpos($v['voucher_title'], '30%') !== false) $rate = 0.30;
                        ?>
                            <option value="<?php echo $v['voucher_title'].'|'.$rate; ?>" <?php if($applied_voucher_title == $v['voucher_title']) echo 'selected'; ?>>
                                <?php echo $v['voucher_title']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" name="apply_voucher" class="btn-action">APPLY</button>
                </div>
            </div>
        </form>

        <form method="POST" action="checkout_process.php" style="margin-top: 20px;">
            <input type="hidden" name="applied_voucher" value="<?php echo $applied_voucher_title; ?>">
            <input type="hidden" name="final_total" value="<?php echo $subtotal; ?>">
            <button type="submit" class="btn-checkout">Check Out</button>
        </form>
    </div>
</div>
</body>
</html>