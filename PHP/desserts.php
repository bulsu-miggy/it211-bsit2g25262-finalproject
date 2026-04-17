<?php
session_start();
require_once 'ProductManager.php';
$productManager = new ProductManager();

// Get desserts from database
$allProducts = $productManager->getAllProducts();
$desserts = array_filter($allProducts, function($p) {
    if (!empty($p['category_slug'])) {
        return strtolower($p['category_slug']) === 'desserts';
    }
    return !empty($p['category_name']) && strtolower($p['category_name']) === 'desserts';
});


if (count($desserts) < 10) {
    $sampleDesserts = [
        ['id' => 12001, 'name' => 'Leche Flan', 'category_name' => 'Desserts', 'price' => 120, 'description' => 'Creamy caramel custard - smooth and decadent.', 'image_emoji' => '🍮', 'is_active' => 1],
        ['id' => 12002, 'name' => 'Ube Halaya', 'category_name' => 'Desserts', 'price' => 150, 'description' => 'Purple yam jam - sweet, creamy, and vibrant.', 'image_emoji' => '🍠', 'is_active' => 1],
        ['id' => 12003, 'name' => 'Buko Pandan', 'category_name' => 'Desserts', 'price' => 100, 'description' => 'Coconut and pandan jelly salad - refreshing and sweet.', 'image_emoji' => '🥥', 'is_active' => 1],
        ['id' => 12004, 'name' => 'Halo-Halo', 'category_name' => 'Desserts', 'price' => 130, 'description' => 'Famous Filipino shaved ice dessert with various toppings.', 'image_emoji' => '🍧', 'is_active' => 1],
        ['id' => 12005, 'name' => 'Turon', 'category_name' => 'Desserts', 'price' => 60, 'description' => 'Fried banana spring rolls with jackfruit - crispy and sweet.', 'image_emoji' => '🍌', 'is_active' => 1],
        ['id' => 12006, 'name' => 'Cassava Cake', 'category_name' => 'Desserts', 'price' => 140, 'description' => 'Baked cassava cake with coconut milk - dense and chewy.', 'image_emoji' => '🍰', 'is_active' => 1],
        ['id' => 12007, 'name' => 'Mango Float', 'category_name' => 'Desserts', 'price' => 160, 'description' => 'Layered graham crackers, cream, and fresh mangoes - no-bake delight.', 'image_emoji' => '🥭', 'is_active' => 1],
        ['id' => 12008, 'name' => 'Bibingka', 'category_name' => 'Desserts', 'price' => 110, 'description' => 'Rice cake baked with salted egg and cheese - soft and flavorful.', 'image_emoji' => '🍰', 'is_active' => 1],
        ['id' => 12009, 'name' => 'Suman', 'category_name' => 'Desserts', 'price' => 70, 'description' => 'Glutinous rice wrapped in banana leaves - served with cocoa.', 'image_emoji' => '🍙', 'is_active' => 1],
        ['id' => 12010, 'name' => 'Maja Blanca', 'category_name' => 'Desserts', 'price' => 110, 'description' => 'Coconut pudding with corn - creamy and light.', 'image_emoji' => '🍮', 'is_active' => 1]
    ];
    $desserts = array_merge($desserts, $sampleDesserts);
}

// Get cart count
$cartCount = 0;
$cartItems = $productManager->getCartItems();
foreach ($cartItems as $item) {
    $cartCount += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desserts - Lasa Filipina</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Same styles as previous pages */
        .carousel-item { height: 350px; background-color: #f8f9fa; }
        .carousel-item img { object-fit: cover; height: 100%; width: 100%; }
        .carousel { border-radius: 25px; overflow: hidden; width: 100%; max-width: 100%; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.2); position: relative; }
        .navbar-nav .nav-link { font-size: 24px; padding: 8px 20px; margin: 0 5px; }
        .navbar-nav .nav-link:hover { width: 100%; background-color: rgba(141, 85, 36, 0.50); border-radius: 10px; }
        .navbar { margin-top: 20px; margin-bottom: 50px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 100%; max-width: 100%; position: relative; left: 0; border-radius: 25px; }
        .navbar-collapse { justify-content: center; }
        .navbar-nav { gap: 30px; }
        .navbar-brand { font-family: 'Times New Roman', serif; font-size: 36px; font-weight: 700; color: #000000; margin-right: auto; }
        .nav-item { font-family: 'Verdana', sans-serif; font-size: 16px; font-weight: 400; color: #000000; margin-right: auto; }
        .container-fluid { padding-left: 15px; padding-right: 15px; }
        /* Display.php navbar style */
        .navbar-custom {
            width: 100%;
            max-width: 100%;
            margin: 20px auto 0;
            padding: 12px 20px;
            background: rgba(255, 248, 240, 0.98);
            border-radius: 25px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }
        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .navbar-brand-custom {
            font-family: 'Times New Roman', serif;
            font-size: 28px;
            font-weight: 700;
            color: #2f241b;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        .navbar-brand-custom:hover {
            transform: scale(1.02);
            color: #bc6f3b;
        }
        .nav-links-custom {
            display: flex;
            gap: 30px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .nav-links-custom a {
            text-decoration: none;
            color: #2f241b;
            font-size: 18px;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 8px 16px;
            border-radius: 10px;
        }
        .nav-links-custom a:hover {
            color: #bc6f3b;
            background-color: rgba(188, 111, 59, 0.1);
        }
        .nav-links-custom a.active {
            background-color: #bc6f3b;
            color: white;
        }
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .cart-icon-btn {
            background: none;
            border: none;
            font-size: 28px;
            color: #bc6f3b;
            cursor: pointer;
            padding: 8px;
            text-decoration: none;
            position: relative;
            display: inline-flex;
            align-items: center;
            transition: transform 0.2s;
        }
        .cart-icon-btn:hover {
            transform: scale(1.1);
            color: #a55828;
        }
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        .avatar-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
            background: #f0e2d6;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .avatar-icon:hover {
            transform: scale(1.05);
        }
        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 2px;
            color: #8b735b;
        }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; overflow-x: hidden; }
        body { background-image: url('../Imges/bg.jpg'); background-size: cover; position: relative; margin: 0; padding: 0; overflow-x: hidden; }
        body::before { content: ''; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(107deg, rgba(245, 229, 214, 0.85) 80%, rgba(255, 245, 235, 0.9) 100%); z-index: -1; }
        
        .custom-card { width: 100%; border: 2px solid #ddd; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; background: white; }
        .custom-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-color: #423717; }
        .custom-card .btn { display: block; margin: 10px auto; width: 90%; padding: 10px; font-size: 14px; }
        .custom-card .card-title { font-family: 'Times New Roman', serif; font-size: 24px; font-weight: 700; color: #000000; text-align: center; margin-bottom: 10px; }
        .custom-card .card-img-top { width: 100%; height: 250px; object-fit: cover; border-top-left-radius: 8px; border-top-right-radius: 8px; background-color: #f0f0f0; }
        .custom-card .card-img-top img { width: 100%; height: 100%; object-fit: cover; border-top-left-radius: 8px; border-top-right-radius: 8px; }
        .custom-card .card-text { font-size: 14px; color: #666; margin-bottom: 10px; height: 60px; overflow: hidden; }
        .price { font-size: 20px; font-weight: bold; color: #bc6f3b; text-align: center; margin: 10px 0; }
        .quantity-control { display: flex; align-items: center; justify-content: center; gap: 10px; margin: 10px 0; }
        .qty-btn { background: #c97e2a; color: white; border: none; width: 30px; height: 30px; border-radius: 50%; font-weight: bold; }
        .qty-btn:hover { background: #b0681c; }
        .qty-display { min-width: 30px; text-align: center; font-weight: bold; }
        .btn-add-cart { background: #2f241b; color: white; border: none; padding: 8px; border-radius: 5px; width: 90%; margin: 5px auto; }
        .btn-add-cart:hover { background: #bc6f3b; }
        .btn-buy-now { background: #bc6f3b; color: white; border: none; padding: 8px; border-radius: 5px; width: 90%; margin: 5px auto; }
        .btn-buy-now:hover { background: #a55828; }
        .cart-wrapper { margin-left: auto; display: flex; align-items: center; }
        .cart-icon-btn { background: none; border: none; font-size: 28px; color: #8b5a2b; cursor: pointer; padding: 8px; text-decoration: none; position: relative; display: inline-flex; align-items: center; transition: transform 0.2s; }
        .cart-icon-btn:hover { transform: scale(1.1); color: #b87c4f; }
        .cart-count { position: absolute; top: -5px; right: -5px; background-color: #dc3545; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold; min-width: 18px; text-align: center; }
        .alert-primary { height: 125px; width: 100%; max-width: 100%; margin: 100px auto; position: relative; background-color: rgba(141, 85, 36, 100); border: none; border-radius: 20px; }
        .alert-heading { padding-left: 30px; margin-top: 15px; font-family: 'Times New Roman', serif; font-size: 32px; font-weight: 300; color: #ffffff; }
        .alert-subheading { padding-left: 30px; font-family: 'Verdana', sans-serif; font-size: 18px; color: #ffffff; font-weight: 100; }
        .text-left { padding-left: 30px; max-width: 100%; margin: 0 auto; width: 100%; position: relative; left: 0; }
        .text-left h1 { font-family: 'Times New Roman', serif; font-size: 52px; font-weight: 700; color: #000000; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
        .text-left .lead { font-family: 'Verdana', sans-serif; font-size: 18px; color: #000000; font-weight: 300; }
        
        @media (max-width: 768px) {
            .navbar { width: 100%; left: 0; }
            .carousel { width: 100%; }
            .text-left { width: 100%; left: 0; padding-left: 15px; }
            .alert-primary { width: 100%; }
        }
        .product-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 25px; padding: 20px; width: 100%; max-width: 100%; margin: 0 auto; }
        @media (max-width: 768px) { .product-grid { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; } }
    </style>
</head>
<body>

    <nav class="navbar-custom">
        <div class="navbar-inner">
            <a href="home.php" class="navbar-brand-custom">🇵🇭 Lasa Filipina</a>
            <ul class="nav-links-custom">
                <li><a href="best-sellers.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'best-sellers.php' ? 'active' : ''; ?>">Best Sellers</a></li>
                <li><a href="dishes.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dishes.php' ? 'active' : ''; ?>">Dishes</a></li>
                <li><a href="beverages.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'beverages.php' ? 'active' : ''; ?>">Beverages</a></li>
                <li><a href="desserts.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'desserts.php' ? 'active' : ''; ?>">Desserts</a></li>
            </ul>
            <div class="navbar-actions">
                <span class="since-badge">SINCE 1920</span>
                <a href="cart.php" class="cart-icon-btn">
                    <i class="bi bi-cart"></i>
                    <span class="cart-count"><?php echo $cartCount; ?></span>
                </a>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                <div class="dropdown">
                    <button class="avatar-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: transparent; padding: 0;">
                        <img src="../images/logi.png" alt="User Avatar" class="avatar-img">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="myaccount.php">My Account</a></li>
                        <li><a class="dropdown-item" href="myorders.php">My Orders</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="loginpage.php" class="btn btn-outline-primary btn-sm">Login</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../Admin/" class="btn btn-outline-secondary btn-sm">Admin</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="../images/carouselone.jpg" class="d-block w-100" alt="Filipino Foods">
        </div>
        <div class="carousel-item">
            <img src="../images/carouseltwo.jpg" class="d-block w-100" alt="Filipino Delicacies">
        </div>
        <div class="carousel-item">
            <img src="../images/carouselthree.jpg" class="d-block w-100" alt="Lechon">
        </div>
        <!-- <a href="best-sellers.php" class="btn btn-primary" style="position: absolute; bottom: 20px; right: 20px; z-index: 10;">Order Now</a> -->
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
    </div>

    <div class="text-left mt-30 p-5">
        <h1>Sweet Delights</h1>
        <p class="lead">Traditional Filipino desserts that will satisfy your sweet tooth</p>
    </div>

    <div class="product-grid">
        <?php 
        $displayed = 0;
        foreach ($desserts as $product): 
            if ($displayed >= 10) break;
            $displayed++;
            $productId = $product['id'];
            $placeholderImage = "../images/desserts/" . strtolower(str_replace([' ', '-', "'"], '_', $product['name'])) . ".jpg";
        ?>
        <div class="card custom-card h-100" data-product-id="<?php echo htmlspecialchars($productId); ?>" onclick="onCardClick(event, '<?php echo addslashes($productId); ?>')" style="cursor: pointer;">
            <div class="card-img-top">
                <img src="<?php echo $placeholderImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='https://placehold.co/400x250/f8e0e8/c65a8c?text=Dessert'">
            </div>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                <p class="card-text"><?php echo htmlspecialchars($product['description'] ?? 'A sweet Filipino dessert that will satisfy your cravings.'); ?></p>
                <div class="price mt-auto">₱<?php echo number_format($product['price'], 2); ?></div>
                <!-- <div class="quantity-control">
                    <button class="qty-btn" onclick="changeQuantity(this, -1)">-</button>
                    <span class="qty-display">1</span>
                    <button class="qty-btn" onclick="changeQuantity(this, 1)">+</button>
                </div> -->
                <button class="btn-add-cart" onclick="event.stopPropagation(); addToCart('<?php echo addslashes($productId); ?>', this)">Add to Cart</button>
                <button class="btn-buy-now" onclick="event.stopPropagation(); buyNow('<?php echo addslashes($productId); ?>', this)">Buy Now</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="alert alert-primary" role="alert">
        <h1 class="alert-heading">Lasa Filipina</h1>
        <p class="alert-subheading">Discover the rich tapestry of Filipino cuisine with us.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Same scripts as previous pages
        function onCardClick(event, productId) {
            if (event.target.closest('button')) {
                return;
            }
            window.location.href = 'display.php?id=' + encodeURIComponent(productId);
        }

        function changeQuantity(btn, delta) {
            const card = btn.closest('.custom-card');
            const qtySpan = card.querySelector('.qty-display');
            let currentQty = parseInt(qtySpan.innerText);
            let newQty = currentQty + delta;
            if (newQty < 1) newQty = 1;
            qtySpan.innerText = newQty;
        }

        async function addToCart(productId, btn) {
    console.log('Adding product:', productId);
    btn.disabled = true;
    btn.textContent = 'Adding...';
    
    try {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        
        console.log('Sending request...');
        const response = await fetch('cart-handler.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        const result = await response.json();
        console.log('Response data:', result);
        
        if (result.success) {
            btn.textContent = 'Added!';
            setTimeout(() => {
                btn.textContent = 'Add to Cart';
                btn.disabled = false;
            }, 1500);
            updateCartCount();
        } else {
            btn.textContent = 'Failed';
            if (result.message) {
                alert('Error: ' + result.message);
            }
            setTimeout(() => {
                btn.textContent = 'Add to Cart';
                btn.disabled = false;
            }, 1500);
        }
    } catch (error) {
        console.error('Error:', error);
        btn.textContent = 'Add to Cart';
        btn.disabled = false;
        alert('Error adding to cart: ' + error.message);
    }
}

async function buyNow(productId, btn) {
    btn.disabled = true;
    btn.textContent = 'Processing...';
    
    // Directly go to checkout without adding to cart
    window.location.href = 'checkout.php?product_id=' + encodeURIComponent(productId);
}
        async function updateCartCount() {
            try {
                const response = await fetch('cart-handler.php?action=get_count');
                const result = await response.json();
                const cartCountElements = document.querySelectorAll('.cart-count');
                cartCountElements.forEach(el => {
                    el.textContent = result.count || 0;
                });
            } catch (error) {
                console.error('Error:', error);
            }
        }
    </script>
</body>
</html>