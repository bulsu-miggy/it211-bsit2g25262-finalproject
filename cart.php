<?php
session_start();
require "connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 1. Initialize the cart session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

//  2. Handle Cart Actions (Add, Remove, Decrease)
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'] ?? 'add'; 

    if ($action === 'add') {
        
        if ($action === 'add') {
     $current_qty = isset($_SESSION['cart'][$id]) ? (int)$_SESSION['cart'][$id] : 0;
    $_SESSION['cart'][$id] = $current_qty + 1;
}
    } elseif ($action === 'remove') {
        // Remove item entirely
        unset($_SESSION['cart'][$id]);
    } elseif ($action === 'decrease') {
        // Decrease quantity, remove if it hits 0
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]--;
            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
    }
    
    // Redirect to clean the URL and prevent duplicate actions on refresh
    header("Location: cart.php");
    exit();
}

// 3. Fetch Cart Items from Database
$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    // Create an array of question marks for the prepared statement: ?,?,?
    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    
    // Fetch only the products that are currently in the session cart
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($_SESSION['cart']));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $p['quantity'] = $qty;
        $p['total_price'] = $p['price'] * $qty;
        $subtotal += $p['total_price'];
        $cart_items[] = $p;
    }
}

// 💰 4. Calculate Totals
$delivery = 200;
 $total = ($subtotal > 0) ? $subtotal + $delivery : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <title>Sportify - Cart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

 <header>
    <div class="container nav-flex">

        <a href="homepage.php" class="logo">SPORTIFY</a>
        <div class="nav-icons">
            <a href="profile.php"><i class="fas fa-user"></i></a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i>
                <?php if(count($_SESSION['cart']) > 0): ?>
                    <span style="background:red; color:white; border-radius:50%; padding:2px 6px; font-size:12px;">
                        <?php echo array_sum($_SESSION['cart']); ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</header>

<main class="checkout-container">
    <h1>Your Cart</h1>

    <div class="cart-layout">

        <div class="cart-items-wrapper">
            
            <?php if (empty($cart_items)): ?>
                <div class="cart-item" style="display:block; text-align:center; padding: 40px;">
                    <h3>Your cart is empty!</h3>
                    <br>
                    <a href="homepage.php" class="btn-dark" style="text-decoration:none; padding:10px 20px;">Continue Shopping</a>
                </div>
            <?php else: ?>

                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">

                    <div class="item-details">
                        <img src="<?php echo htmlspecialchars($item['img']); ?>" alt="product" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">

                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($item['product']); ?></h4>
                            <p>Status: <?php echo htmlspecialchars($item['stock_status']); ?></p>
                            <p style="font-weight:700; font-size:18px;">
                                ₱ <?php echo number_format($item['price'], 2); ?>
                            </p>
                        </div>
                    </div>

                    <div class="item-price-qty">
                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" style="color:red; text-decoration:none;" title="Remove Item">
                            <i class="fas fa-trash"></i>
                        </a>

                        <div class="qty-selector" style="display:flex; align-items:center; gap:10px; margin-top:10px;">
                            <a href="cart.php?action=decrease&id=<?php echo $item['id']; ?>" style="text-decoration:none; padding: 5px 10px; background:#eee; border-radius:4px; color:black;">-</a>
                            <span style="font-weight:bold;"><?php echo $item['quantity']; ?></span>

                            <a href="cart.php?action=add&id=<?php echo $item['id']; ?>" style="text-decoration:none; padding: 5px 10px; background:#eee; border-radius:4px; color:black;">+</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>

        <div class="summary-box">
            <h3>Order Summary</h3>

            <div class="summary-row">
                <span>Subtotal</span>
                <span>₱ <?php echo number_format($subtotal, 2); ?></span>
            </div>

            <div class="summary-row">
                <span>Delivery Fee</span>
                <span>₱ <?php echo number_format($subtotal > 0 ? $delivery : 0, 2); ?></span>
            </div>

            <div class="summary-row total">
                <span>Total</span>
                <span>₱ <?php echo number_format($total, 2); ?></span>
            </div>

            <?php if ($subtotal > 0): ?>
            <a href="checkout_process.php" class="btn-checkout" style="text-decoration:none; text-align:center; display:block;">
    Confirm Order <i class="fas fa-check"></i>
</a>
            <?php else: ?>
                
            <button class="btn-checkout" style="background:#ccc; cursor:not-allowed;" disabled>
                Cart is Empty
            </button>
            <?php endif; ?>
        </div>

    </div>
</main>

</body>
</html>