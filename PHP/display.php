<?php
require_once __DIR__ . '/ProductManager.php';

$productManager = new ProductManager();
$productId = $_GET['id'] ?? null;
$product = null;
if ($productId) {
    $product = $productManager->getProductById($productId);
}

if (!$product) {
    $product = [
        'id' => 'halo_halo',
        'name' => 'Halo-Halo',
        'brand' => 'dessert',
        'price' => 49,
        'description' => 'Cool down with a refreshing taste of the Philippines with Lasa Filipina\'s signature Halo-Halo! This vibrant dessert is a delightful mix of crushed ice, sweetened fruits, creamy leche flan, soft ube, and chewy jellies, all layered together and topped with rich evaporated milk. Every spoonful offers a perfect balance of sweetness, creaminess, and texture that melts in your mouth. Perfectly portioned for one, this indulgent treat is your go-to for beating the heat and satisfying your sweet cravings—whether after a meal or as a midday pick-me-up. A true Filipino classic, made with love in every layer.',
    ];
}

$brand = strtolower(trim($product['category_name'] ?? ''));
$categoryDirectory = 'dishes';
if (strpos($brand, 'beverage') !== false || $brand === 'drink') {
    $categoryDirectory = 'beverages';
} elseif (strpos($brand, 'dessert') !== false) {
    $categoryDirectory = 'desserts';
} elseif (strpos($brand, 'dish') !== false) {
    $categoryDirectory = 'dishes';
}

// Use image from database if available, else fallback to old logic
if (!empty($product['image'])) {
    // If image path is already relative, use as is; else, prepend directory
    $productImage = '../images/' . $categoryDirectory . '/' . $product['image'];
    $imagePath = __DIR__ . '/../images/' . $categoryDirectory . '/' . $product['image'];
    if (!file_exists($imagePath)) {
        $productImage = 'https://placehold.co/544x420?text=' . urlencode($product['name']);
    }
} else {
    $imageName = strtolower(str_replace([' ', '-', "'"], '_', $product['name'])) . '.jpg';
    $imagePath = __DIR__ . '/../images/' . $categoryDirectory . '/' . $imageName;
    $productImage = file_exists($imagePath)
        ? '../images/' . $categoryDirectory . '/' . $imageName
        : 'https://placehold.co/544x420?text=' . urlencode($product['name']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | Lasa Filipina</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fefaf5;
            overflow-x: hidden;
        }

        /* Navigation Bar Styles - Matching frontend */
        .navbar-custom {
            width: 100%;
            max-width: 100%;
            margin: 20px auto 0;
            padding: 12px 20px;
            background: rgba(255, 248, 240, 0.98);
            border-radius: 25px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
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

        /* Main Container */
        .product-container {
            width: 100%;
            max-width: 100%;
            margin: 20px auto;
            padding: 0 20px;
        }

        .product-card {
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0e2d6;
        }

        .product-image-section {
            background: linear-gradient(135deg, #fefaf5, #fff5ed);
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image {
            width: 100%;
            max-width: 100%;
            height: auto;
            border-radius: 24px;
            object-fit: cover;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .product-info-section {
            padding: 32px 20px;
        }

        .product-name {
            font-size: 3rem;
            font-weight: 800;
            color: #2f241b;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .product-price {
            font-size: 2rem;
            font-weight: 700;
            color: #bc6f3b;
            margin-bottom: 24px;
        }

        .product-description {
            font-size: 1rem;
            line-height: 1.7;
            color: #6f553e;
            margin-bottom: 32px;
        }

        .btn-order {
            background: linear-gradient(135deg, #bc6f3b, #a55828);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 16px 48px;
            font-size: 1.25rem;
            font-weight: 700;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 16px;
        }

        .btn-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(188, 111, 59, 0.3);
        }

        .btn-order:disabled {
            opacity: 0.6;
            transform: none;
        }

        .btn-back {
            background: #f5ede5;
            color: #2f241b;
            border: none;
            border-radius: 50px;
            padding: 16px 48px;
            font-size: 1.25rem;
            font-weight: 700;
            transition: all 0.3s ease;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-back:hover {
            background: #e7cfbc;
            color: #2f241b;
        }

        /* Footer */
        .footer {
            background: #2f241b;
            color: #e8d9cc;
            padding: 40px 0;
            margin-top: 60px;
        }

        .footer-content {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-logo {
            font-family: 'Times New Roman', serif;
            font-size: 24px;
            font-weight: 700;
            color: #ffd966;
        }

        .footer-copyright {
            font-size: 14px;
            color: #a6907c;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-custom {
                margin: 10px;
                padding: 12px 20px;
            }
            
            .nav-links-custom {
                gap: 10px;
            }
            
            .nav-links-custom a {
                font-size: 14px;
                padding: 6px 12px;
            }
            
            .product-name {
                font-size: 2rem;
            }
            
            .product-price {
                font-size: 1.5rem;
            }
            
            .product-info-section {
                padding: 32px 24px;
            }
            
            .product-image-section {
                padding: 24px;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand-custom {
                font-size: 20px;
            }
            
            .product-name {
                font-size: 1.5rem;
            }
            
            .btn-order, .btn-back {
                padding: 12px 24px;
                font-size: 1rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-card {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>

<!-- Navigation Bar - Matching frontend design -->
<nav class="navbar-custom">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <a href="home.php" class="navbar-brand-custom">🇵🇭 Lasa Filipina</a>
        
        <ul class="nav-links-custom">
            <li><a href="home.php">Home</a></li>
            <li><a href="dishes.php" class="active">Menu</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#contact">Contact Us</a></li>
        </ul>
        
        <div class="d-flex align-items-center gap-3">
            <span class="since-badge">SINCE 1920</span>
            <a href="cart.php" class="cart-icon-btn">
                <i class="bi bi-cart"></i>
                <span class="cart-count" id="cartCount">0</span>
            </a>
            <div class="dropdown">
                <button class="avatar-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: transparent; padding: 0;">
                    <img src="../images/logi.png" alt="User Avatar" class="avatar-img">
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <li><a class="dropdown-item" href="myaccount.php">My Account</a></li>
                    <li><a class="dropdown-item" href="myorders.php">My Orders</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    <?php else: ?>
                    <li><a class="dropdown-item" href="loginpage.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
    </div>
</nav>

<!-- Product Display Section -->
<div class="product-container">
    <div class="product-card">
        <div class="row g-0">
            <!-- Left Column - Product Image -->
            <div class="col-lg-6 product-image-section">
                <img class="product-image" src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            
            <!-- Right Column - Product Info -->
            <div class="col-lg-6 product-info-section">
                <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
                
                <button id="addToCartBtn" class="btn-order" onclick="addToCart('<?php echo addslashes($product['id']); ?>', this)">
                    ADD TO CART
                </button>
                
                <a href="dishes.php" class="btn-back">
                    ← BACK TO MENU
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-logo">🇵🇭 Lasa Filipina</div>
        <div class="footer-copyright">© 2024 Lasa Filipina. All rights reserved.</div>
        <div class="footer-copyright">https://www.LasaFilipina.com</div>
    </div>
</footer>

<script>
    // Load cart count on page load
    async function loadCartCount() {
        try {
            const response = await fetch('cart-handler.php?action=get_count');
            const result = await response.json();
            if (result.count !== undefined) {
                document.getElementById('cartCount').textContent = result.count;
            }
        } catch (error) {
            console.error('Error loading cart count:', error);
        }
    }
    
    // Add to cart function
    async function addToCart(productId, btn) {
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Adding...';

        try {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('quantity', 1);

            const response = await fetch('cart-handler.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                btn.textContent = '✓ Added!';
                btn.style.background = '#2e7d32';
                loadCartCount(); // Update cart count
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '';
                    btn.disabled = false;
                }, 1500);
            } else {
                btn.textContent = 'Failed';
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.disabled = false;
                }, 1500);
            }
        } catch (error) {
            console.error('Error:', error);
            btn.textContent = 'Error';
            setTimeout(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            }, 1500);
        }
    }
    
    // Load cart count when page loads
    loadCartCount();
</script>

<!-- Bootstrap JS (optional, for any Bootstrap features) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>