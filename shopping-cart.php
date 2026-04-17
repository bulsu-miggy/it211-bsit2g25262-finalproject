<?php
  session_start();
  if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
    header('Location: login.php');
    exit();
  }

  include 'db/connection.php';

  $cart_items = $_SESSION['cart'] ?? [];
  $cart_products = [];
  $subtotal = 0;

  if (!empty($cart_items)) {
    foreach ($cart_items as $key => $item) {
      try {
        if (!is_array($item)) {
            continue; 
        }

        $p_id = isset($item['product_id']) ? $item['product_id'] : (isset($item['id']) ? $item['id'] : 0);
        $qty = isset($item['quantity']) ? (int)$item['quantity'] : (isset($item['qty']) ? (int)$item['qty'] : 1);
        $size = isset($item['size']) ? $item['size'] : 'M';

        if ($p_id > 0) {
            $stmt = $conn->prepare("SELECT id, title, price, image_url, sub_category FROM products WHERE id = ?");
            $stmt->execute([$p_id]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($prod) {
              $prod['cart_key'] = $key;
              $prod['qty'] = $qty;
              $prod['size'] = $size;
              $cart_products[] = $prod;
              $subtotal += ($prod['price'] * $qty);
            }
        }
      } catch (PDOException $e) {
          // Silent catch
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAG - LYNX</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;700&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #fff; font-family: 'Rubik', sans-serif; }
        .cart-wrapper { max-width: 1100px; margin: 60px auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr; gap: 50px; }
        
        .cart-header { font-family: 'Rubik Mono One', sans-serif; font-size: 1.5rem; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        
        .cart-item { display: flex; gap: 20px; padding: 25px 0; border-bottom: 1px solid #eee; }
        .item-img { width: 140px; height: 180px; object-fit: cover; border-radius: 10px; background: #f5f5f5; }
        
        .item-details { flex: 1; }
        .item-title { font-weight: 700; font-size: 1.1rem; text-transform: uppercase; margin-bottom: 5px; }
        .item-meta { color: #888; font-size: 0.9rem; margin-bottom: 5px; }
        .item-price { font-weight: 700; font-size: 1rem; margin-top: 10px; }
        
        .remove-btn { cursor: pointer; color: #888; transition: 0.3s; display: flex; align-items: center; gap: 5px; font-size: 0.8rem; margin-top: 15px; background: none; border: none; padding: 0; }
        .remove-btn:hover { color: #ff4d4d; }

        .summary-card { background: #f9f9f9; padding: 30px; border-radius: 20px; height: fit-content; position: sticky; top: 100px; }
        .summary-title { font-weight: 700; margin-bottom: 20px; font-size: 1.2rem; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; color: #555; }
        .total-row { border-top: 1px solid #ddd; padding-top: 15px; margin-top: 15px; font-weight: 700; font-size: 1.2rem; color: #000; }
        
        .checkout-btn { width: 100%; padding: 18px; background: #000; color: #fff; border: none; border-radius: 50px; font-weight: 700; font-size: 1rem; cursor: pointer; margin-top: 25px; transition: 0.3s; }
        .checkout-btn:hover { background: #333; transform: translateY(-3px); }
        
        .empty-cart { text-align: center; padding: 100px 0; grid-column: span 2; }
        
        @media (max-width: 850px) {
            .cart-wrapper { grid-template-columns: 1fr; }
            .summary-card { position: static; }
        }
    </style>
</head>
<body>

    <header class="header">
    <a href="index.php" style="text-decoration: none; color: black;"><h1 class="logo">LYNX</h1></a>
    
    <nav class="nav">
        <a href="women.php" style="text-decoration: none; color: black; font-weight: 700;">WOMEN</a>
        <a href="men.php" style="text-decoration: none; color: black; font-weight: 500;">MEN</a>
    </nav>

    <div class="icons">
        <span class="material-symbols-outlined">search</span>
        <a href="shopping-cart.php" title="Cart" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">shopping_cart</span>
        </a>
        <a href="profiles.php" title="Profile" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">account_circle</span>
        </a>
    </div>
  </header>

    <div class="cart-wrapper">
        <?php if (empty($cart_products)): ?>
            <div class="empty-cart">
                <span class="material-symbols-outlined" style="font-size: 4rem; color: #ccc;">shopping_bag</span>
                <h2 style="margin-top: 20px; font-family: 'Rubik Mono One';">YOUR BAG IS EMPTY</h2>
                <p style="color: #888; margin-bottom: 30px;">Looks like you haven't added anything yet.</p>
                <a href="index.php" style="padding: 15px 40px; background: #000; color: #fff; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block;">GO SHOPPING</a>
            </div>
        <?php else: ?>
            <div>
                <h1 class="cart-header">YOUR BAG</h1>
                <?php foreach ($cart_products as $item): ?>
                    <div class="cart-item">
                        <img src="images/products/<?php echo htmlspecialchars($item['image_url']); ?>" class="item-img" onerror="this.src='images/placeholder.png'">
                        <div class="item-details">
                            <h3 class="item-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="item-meta"><?php echo htmlspecialchars($item['sub_category']); ?></p>
                            <p class="item-meta">Size: <strong><?php echo $item['size']; ?></strong></p>
                            <p class="item-meta">Qty: <?php echo $item['qty']; ?></p>
                            <p class="item-price">₱<?php echo number_format($item['price'] * $item['qty'], 2); ?></p>
                            
                            <button class="remove-btn" onclick="removeItem('<?php echo $item['cart_key']; ?>')">
                                <span class="material-symbols-outlined" style="font-size: 1.2rem;">delete</span>
                                REMOVE
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-card">
                <h2 class="summary-title">ORDER SUMMARY</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Estimated Shipping</span>
                    <span style="color: #2ecc71; font-weight: 500;">FREE</span>
                </div>
                <div class="total-row summary-row">
                    <span>Total</span>
                    <span>₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <button class="checkout-btn" onclick="processCheckout()">CHECKOUT</button>
                
                <div style="margin-top: 20px; display: flex; align-items: center; gap: 10px; justify-content: center; color: #aaa;">
                    <span class="material-symbols-outlined" style="font-size: 1.2rem;">verified_user</span>
                    <span style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">Secure Checkout</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
  <footer class="footer-banner" style="background: black; padding: 60px 20px; color: white; font-family: 'Rubik', sans-serif;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 60px; align-items: start;">
      <div>
        <h1 style="font-family: 'Rubik Mono One', sans-serif; font-size: 3rem; font-weight: bold; letter-spacing: 2px; margin: 0; color: white;">LYNX</h1>
      </div>
      <div>
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">SHOP</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="women.php" style="color: white; text-decoration: none;">WOMEN</a></li>
          <li style="margin-bottom: 10px;"><a href="men.php" style="color: white; text-decoration: none;">MEN</a></li>
        </ul>
      </div>
      <div>
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">COMPANY</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="about.php" style="color: white; text-decoration: none;">ABOUT US</a></li>
        </ul>
      </div>
      <div style="text-align: right;">
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">BECOME A MEMBER</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="register.php" style="color: white; text-decoration: none;">JOIN US</a></li>
        </ul>
      </div>
    </div>
  </footer>
  
    <script>
        function removeItem(cartKey) {
            Swal.fire({
                title: 'Remove item?',
                text: "Do you want to remove this item from your bag?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#000',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'db/action/cart.php',
                        method: 'POST',
                        data: {
                            action: 'remove',
                            cart_key: cartKey
                        },
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });
        }

        function processCheckout() {
            Swal.fire({
                title: 'Proceed to Checkout?',
                text: "You will be redirected to the payment page.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#000',
                confirmButtonText: 'Proceed'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'checkout.php';
                }
            });
        }
    </script>
</body>
</html>