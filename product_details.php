<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);

include 'db/connection.php'; 

$id = isset($_GET['id']) ? $_GET['id'] : 1;

// Defensive check: Detect if the column is 'product_id' or 'id' to prevent the 1054 error
$pk = "product_id";
$check = $conn->query("SHOW COLUMNS FROM candles LIKE 'product_id'");
if ($check->rowCount() == 0) $pk = "id";

$stmt = $conn->prepare("SELECT * FROM candles WHERE $pk = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

$base_price  = (float)($product['price'] ?? 0); // Added fallback
$name        = htmlspecialchars($product['name'] ?? 'Unknown Product'); // Added fallback
$img_path    = htmlspecialchars($product['image_url'] ?? $product['img_path'] ?? ''); // Fallback for potential old schema 'img_path'
$scent_notes = isset($product['scent_notes']) ? $product['scent_notes'] : '';
$category    = isset($product['category']) ? htmlspecialchars($product['category']) : 'Seasonal';
$description = isset($product['description']) ? htmlspecialchars($product['description']) : 'Description coming soon.';
$stock_qty  = isset($product['stock_qty']) ? (int)$product['stock_qty'] : 0;
$available_stock = $stock_qty;
$stock_label = $available_stock > 0 ? ($available_stock <= 5 ? 'Only ' . $available_stock . ' left in stock' : $available_stock . ' in stock') : 'Out of stock';

$note_tags = [];
if (!empty($scent_notes)) {
    foreach (explode(',', $scent_notes) as $note) {
        $tag = trim($note);
        if ($tag !== '') {
            $note_tags[] = htmlspecialchars($tag);
        }
    }
}
$note_tags = array_values(array_unique($note_tags));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLIS | <?= $name ?></title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php 
if ($is_logged_in) {
    include 'includes/member_header.php';
} else {
    include 'guest_header/guest_header.php';
}
?>

<div class="product-page-wrapper">
    <div class="back-nav-container">
        <a href="shop.php" class="back-nav">← BACK TO SHOP</a>
    </div>

    <div class="product-flex-layout">
        <div class="left-column">
            <div class="image-slider-container">
                <button class="slider-btn prev-btn" id="prevSlide">‹</button>
                <button class="slider-btn next-btn" id="nextSlide">›</button>
                
                <div class="slider-wrapper" id="sliderWrapper">
                    <img src="<?= $img_path ?>" alt="<?= $name ?>" class="slider-image" data-error-src="https://via.placeholder.com/800x600/FDFDFD/CCC?text=Solis+Detail+View">
                    <img src="<?= str_replace(['.png', '.jpg'], ['_2.png', '_2.jpg'], $img_path) ?>" alt="<?= $name ?> Detail" class="slider-image" data-error-src="https://via.placeholder.com/800x600/FDFDFD/CCC?text=Solis+Detail+View">
                </div>

                <div class="slider-dots">
                    <div class="dot active" data-index="0"></div>
                    <div class="dot" data-index="1"></div>
                </div>
            </div>
        </div>

        <div class="right-column">
            <h1 class="product-name"><?= $name ?></h1>
            <p class="current-price">₱<span id="display-price"><?= number_format($base_price, 2) ?></span></p>
            
            <p class="product-bio"><?= $description ?></p>

            <div class="scent-notes-container">
                <span class="scent-title">Fragrance Profile</span>
                <div class="note-tags">
                    <span class="tag-style"><?= $category ?> Collection</span>
                    <?php foreach ($note_tags as $tag): ?>
                        <span class="tag-style"><?= $tag ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <form id="purchase-form" onsubmit="addToBasket(event, this)">
                <input type="hidden" name="product_id" value="<?= $id ?>">
                
                <label class="select-size-label">SELECT SIZE</label>
                <select id="size-dropdown" name="size">
                    <option value="Small" data-price="<?= $base_price ?>">Small (4oz)</option>
                    <option value="Medium" data-price="<?= $base_price + 20 ?>">Medium (9oz)</option>
                    <option value="Large" data-price="<?= $base_price + 40 ?>">Large (16oz)</option>
                </select>

                <div class="purchase-row">
                    <div class="quantity-control">
                        <button type="button" id="minus-btn" <?= $stock_qty <= 0 ? 'disabled' : '' ?>>-</button>
                        <input type="text" id="qty-input" name="quantity" value="1" readonly>
                        <button type="button" id="plus-btn" <?= $stock_qty <= 0 ? 'disabled' : '' ?>>+</button>
                    </div>

                    <span class="stock-tag <?= $stock_qty > 0 ? 'stock-available' : 'stock-unavailable' ?>"><?= htmlspecialchars($stock_label) ?></span>

                    <?php if ($stock_qty > 0): ?>
                        <?php if ($is_logged_in): ?>
                            <button type="submit" class="add-to-cart-btn">Add to Basket</button>
                        <?php else: ?>
                            <a href="login.php" class="add-to-cart-btn">Login to Purchase</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="add-to-cart-btn" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
                <input type="hidden" id="stock-qty" value="<?= $stock_qty ?>">
            </form>
        </div>
    </div>
</div>

<script src="js/product_details.js"></script>

<script src="js/auth.js?v=1"></script>
<script src="js/cart.js?v=1"></script>

<?php 
if ($is_logged_in) {
    include 'includes/member_footer.php';
} else {
    include 'guest_header/guestfooter.php';
}
?>
</body>
</html>