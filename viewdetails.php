<?php
session_start();
require_once 'auth.php';
require_once 'db/connection.php';

// Get product ID from URL parameter
$product_id = $_GET['id'] ?? 0;
$product = null;
$error = null;

// Fetch product from database
if ($product_id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        error_log($error);
    }
}

// Handle product not found
if (!$product) {
    $error = "Product not found. Please go back to listings.";
    // Set dummy values to prevent errors
    $product_name = "Product Not Found";
    $price = "0.00";
    $image_path = "images/default-product.png";
    $product_description = $error;
} else {
    // Use database product data
    $product_name = $product['name'];
    $price = number_format($product['price'], 2);
    $image_path = !empty($product['image_path']) ? $product['image_path'] : 'images/default-product.png';
    $product_description = $product['description'] ?? 'No description available.';
    
    // Color mapping based on product names
    $color_map = [
        'Grape Juice' => '#7D92E3',
        'Pistachio' => '#D7D9B1',
        'Plum' => '#8F7996',
        'Sage' => '#8FA160',
        'Lilac' => '#E3D7E9',
        'Soft Lilac' => '#E3D7E9',
        'Key Lime' => '#E2E48E',
        'Khaki' => '#D7D9B1',
        'Royal Blue' => '#6271D1',
        'Lavender' => '#D8CDE0',
        'Nori' => '#1A2421',
        'Dark Moss' => '#3D4429',
        'Slate Gray' => '#6B8077',
        'Muted Gold' => '#A68E34',
        'Magenta' => '#e83e8c'
    ];
    
    // Fetch related products from same category (for color/variant selection)
    $related_products = [];
    if (!empty($product['category_id'])) {
        try {
            $stmt = $conn->prepare("SELECT id, name, image_path FROM products WHERE category_id = ? AND status = 'active' ORDER BY name");
            $stmt->execute([$product['category_id']]);
            $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching related products: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Product Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="viewdetails-body">

    <nav class="navbar navbar-expand navbar-light bg-none sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/white.png" alt="SipFlask" height="49">
            </a>

            <div class="collapse navbar-collapse justify-content-end" id="sipNavbar">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item px-0"><a class="nav-link fw-bold" href="index.php">Home</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold" href="listings.php">Listings</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold" href="contactUs.php">Contact Us</a></li>
                    <li class="nav-item ms-tight px-0">
                        <a class="nav-link p-0" href="cart.php"><i class="bi bi-cart4 fs-5"></i></a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold" href="#">Hello, <?= htmlspecialchars(getUserName()) ?></a>
                        </li>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold" href="login.php">Login</a>
                        </li>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <p class="mt-2"><a href="listings.php" class="btn btn-sm btn-outline-danger">Back to Listings</a></p>
            </div>
        <?php endif; ?>

        <div class="product-container-card bg-white rounded p-4 p-md-5 shadow">
            <div class="row g-5">
                
                <div class="col-md-6">
                    <div class="product-main-img product-border rounded mb-3">
                        <img src="<?= htmlspecialchars($image_path) ?>" id="displayFlask" class="img-fluid p-4 product-image-border product-img-shadow" alt="Product Image">
                    </div>
                    <div class="row g-2">
                        </div>
                </div>

                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="listings.php">Listings</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($product_name) ?></li>
                        </ol>
                    </nav>

                    <h1 id="productName" class="text-dark fw-bold mb-1"><?= htmlspecialchars($product_name) ?></h1>
                    <h2 id="productPrice" class="text-dark fw-bold mb-2">₱<?= $price ?></h2>
                    <p class="text-muted small mb-4"><i class="bi bi-shield-check"></i> 1 YEAR LIMITED WARRANTY</p>

                    <?php if (!empty($related_products) && count($related_products) > 1): ?>
                    <div class="mb-4">
                        <h6 class="text-dark fw-bold mb-3">Available Colors</h6>
                        <div id="colorOptionsContainer" class="d-flex gap-3 flex-wrap">
                            <?php foreach ($related_products as $variant): ?>
                                <?php 
                                    $is_active = ($variant['id'] == $product['id']);
                                    // Extract color name from product name
                                    $product_color_name = preg_replace('/\s+(?:Flask|Bottle)\s+\d+oz$/', '', $variant['name']);
                                    $product_color_name = preg_replace('/^Soft\s+/', '', $product_color_name);
                                    
                                    // Find matching color
                                    $hex_color = '#39afaf'; // default teal
                                    foreach ($color_map as $color_key => $color_hex) {
                                        if (stripos($variant['name'], $color_key) !== false) {
                                            $hex_color = $color_hex;
                                            break;
                                        }
                                    }
                                ?>
                                <a href="?id=<?= $variant['id'] ?>" 
                                   class="text-decoration-none" 
                                   title="<?= htmlspecialchars($variant['name']) ?>"
                                   style="display: inline-block;">
                                    <div class="color-swatch-large" 
                                         style="width: 50px; height: 50px; border-radius: 50%; background-color: <?= $hex_color ?>; 
                                                border: 3px solid <?= $is_active ? '#000' : '#ddd' ?>; cursor: pointer;
                                                transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                                         onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'; this.style.transform='scale(1.1)'"
                                         onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'; this.style.transform='scale(1)'">
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ADD TO CART FORM -->
                    <form action="add_to_cart.php" method="POST" class="d-flex align-items-center gap-3 mb-5">
                        <input type="hidden" name="product_id" id="selectedProductId" value="<?= $product['id'] ?? 0 ?>">
                        <input type="hidden" name="name" id="selectedName" value="<?= htmlspecialchars($product_name) ?>">
                        <input type="hidden" name="price" id="selectedPrice" value="<?= $price ?>">
                        <input type="hidden" name="quantity" id="qtyInputHidden" value="1">
                        
                        <div class="input-group" style="width: 140px;">
                            <button type="button" class="btn btn-outline-dark" onclick="updateQty(-1)">-</button>
                            <input type="text" id="qtyInput" class="form-control text-center fw-bold" value="1" readonly>
                            <button type="button" class="btn btn-outline-dark" onclick="updateQty(1)">+</button>
                        </div>
                        <button type="submit" class="btn btn-pink btn-lg flex-grow-1">ADD TO CART</button>
                    </form>

                    <div class="product-description border-top pt-4 mb-4">
                        <p class="text-muted"><?= nl2br(htmlspecialchars($product_description)) ?></p>
                    </div>

                    <div class="product-specs-static border-top pt-4">
                        <h6 class="fw-bold mb-3">Additional Information</h6>
                        <div class="small">
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Material</div>
                                <div class="col-8">18/8 Food Grade Stainless Steel</div>
                            </div>
                            <div class="row">
                                <div class="col-4 text-muted">Collection</div>
                                <div class="col-8">Season 1: Classic Matte</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="custom-footer">
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
    
    <!-- Cart JavaScript -->
    <script>
    // Update hidden fields when quantity changes
    function updateQty(change) {
        const qtyInput = document.getElementById('qtyInput');
        let val = (parseInt(qtyInput.value) || 1) + change;
        val = val < 1 ? 1 : val;
        qtyInput.value = val;
        const qtyHidden = document.getElementById('qtyInputHidden');
        if (qtyHidden) {
            qtyHidden.value = val;
        }
    }

    // Update hidden fields when color changes
    function changeColor(folder, img, name) {
        document.getElementById('displayFlask').src = `images/${folder}/${img}`;
        document.getElementById('productName').innerText = name;
        
        // Update hidden form fields
        const selectedImg = document.getElementById('selectedImg');
        const selectedName = document.getElementById('selectedName');
        const selectedSize = document.getElementById('selectedSize');
        
        if (selectedImg) selectedImg.value = img;
        if (selectedName) selectedName.value = name;
        if (selectedSize) selectedSize.value = folder;
        
        // Update price based on size
        let price = '';
        if (folder === '16oz') price = '850.00';
        else if (folder === '25oz') price = '890.00';
        else if (folder === '32oz') price = '950.00';
        
        const selectedPrice = document.getElementById('selectedPrice');
        const productPrice = document.getElementById('productPrice');
        
        if (selectedPrice) selectedPrice.value = price;
        if (productPrice) productPrice.innerText = `₱${price}`;
        
        // Update spec size
        const specSize = document.getElementById('specSize');
        if (specSize) {
            let ml = '';
            if (folder === '16oz') ml = '473ml';
            else if (folder === '25oz') ml = '739ml';
            else if (folder === '32oz') ml = '946ml';
            specSize.innerText = `${folder} / ${ml}`;
        }
        
        // Update breadcrumb
        const breadcrumbSize = document.getElementById('breadcrumbSize');
        if (breadcrumbSize) {
            breadcrumbSize.innerText = `${folder} Collection`;
        }
    }
    </script>
</body>
</html>