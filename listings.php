<?php
session_start();
require_once 'auth.php';
require_once 'db/connection.php';

$selectedSize = $_GET['size'] ?? 'all';
if (!in_array($selectedSize, ['all', '16oz', '25oz', '32oz'], true)) {
    $selectedSize = 'all';
}

// Fetch products from database
try {
    $stmt = $conn->prepare("SELECT id, name, description, price, image_path, status FROM products WHERE status = 'active' ORDER BY created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    error_log("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="listings-body">
    <nav class="navbar navbar-expand navbar-light bg-none sticky-top shadow-sm" style="background-color: #39afaf;">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/white.png" alt="SipFlask" height="49">
            </a>

            <div class="collapse navbar-collapse justify-content-end" id="sipNavbar">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item px-0"><a class="nav-link fw-bold text-white" href="index.php">Home</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold text-white" href="listings.php">Listings</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold text-white" href="contactUs.php">Contact Us</a></li>
                    <li class="nav-item ms-tight px-0">
                        <a class="nav-link p-0 text-white" href="cart.php"><i class="bi bi-cart4 fs-5"></i></a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold text-white" href="#">Hello, <?= htmlspecialchars(getUserName()) ?></a>
                        </li>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold text-white" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold text-white" href="login.php">Login</a>
                        </li>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold text-white" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">

        <div class="filter-section d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="filter-group">
                <span class="label fw-bold me-2">Category:</span>
                <div class="button-group d-inline-block">
                    <button class="btn btn-pink btn-sm">Classic</button>
                </div>
            </div>
            <div class="filter-group text-md-end">
                <span class="label fw-bold me-2">Filter By Sizes:</span>
                <select class="custom-select" id="sizeFilter">
                    <option value="all" <?= $selectedSize === 'all' ? 'selected' : '' ?>>Featured (All)</option>
                    <option value="16oz" <?= $selectedSize === '16oz' ? 'selected' : '' ?>>16oz</option>
                    <option value="25oz" <?= $selectedSize === '25oz' ? 'selected' : '' ?>>25oz</option>
                    <option value="32oz" <?= $selectedSize === '32oz' ? 'selected' : '' ?>>32oz</option>
                </select>
            </div>
        </div>

        <div id="productGrid">
            <div class="row g-3">
                <?php
                if (!empty($products)):
                    foreach ($products as $product):
                        // Extract size from product name (e.g., "16oz", "25oz", "32oz")
                        $size = 'all';
                        if (preg_match('/16oz/i', $product['name'])) {
                            $size = '16oz';
                        } elseif (preg_match('/25oz/i', $product['name'])) {
                            $size = '25oz';
                        } elseif (preg_match('/32oz/i', $product['name'])) {
                            $size = '32oz';
                        }
                        
                        // Default image if none specified
                        $imagePath = !empty($product['image_path']) ? $product['image_path'] : 'images/default-product.png';
                        $imageAlt = htmlspecialchars($product['name']);
                        $productPrice = number_format($product['price'], 2);
                        ?>
                        <div class="filter-item size-<?= htmlspecialchars($size) ?> col-lg-3 col-md-4 col-sm-6">
                            <div class="product-card text-start">
                                <div class="product-img-container">
                                    <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= $imageAlt ?>">
                                </div>
                                <div class="product-details">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="category">CLASSIC</span>
                                        <span class="color-swatch" style="background-color: #39afaf;"></span>
                                    </div>
                                    <h5 class="product-title"><?= htmlspecialchars($product['name']) ?></h5>
                                    <span class="price">₱<?= $productPrice ?></span>
                                    <p class="product-desc"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?></p>
                                    <a href="viewdetails.php?id=<?= $product['id'] ?>" class="btn btn-pink">VIEW DETAILS</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;
                else:
                    echo '<div class="col-12"><p class="text-center text-muted">No products available at the moment.</p></div>';
                endif;
                ?>
            </div>
        </div> </div>

    <footer class="custom-footer mt-5">
        <div class="container">
            <div class="footer-logo text-white border-bottom pb-1 mb-5 fw-bold d-flex justify-content-center align-items-center">
                <img src="images/exactlogo.png" alt="SipFlask" height="55" class="me-2">
                SipFlask
            </div>
            
            <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="listings.php">Listings</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="contactUs.php">Contact Us</a></li>
            </ul>

            <div class="footer-tagline">#KeepItSipFlask</div>
            
            <div class="footer-socials">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-tiktok"></i></a>
            </div>

            <div class="footer-bottom">
                <div class="footer-credit">Website - SipFlask Website</div>
                <div class="footer-copyright">All Rights Reserved © 2026 SipFlask</div>
                <div class="footer-privacy"><a href="#" class="text-white text-decoration-none">Privacy</a></div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
   
</body>
</html>