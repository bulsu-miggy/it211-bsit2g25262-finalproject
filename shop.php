<?php
// 1. Start session to check login status
session_start();
$is_logged_in = isset($_SESSION['user_id']);

include 'db/connection.php';

// 2. Get the category from the URL (if it exists), but treat blank as All
$category = isset($_GET['category']) && trim($_GET['category']) !== '' ? trim($_GET['category']) : 'All';

// 3. Load available categories for the website nav
$categoryStmt = $conn->prepare("SELECT name FROM categories ORDER BY created_at ASC");
$categoryStmt->execute();
$navCategories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Build the SQL query based on the category
if ($category !== 'All') {
    // Make sure category matching is case-insensitive and reliable
    $stmt = $conn->prepare("SELECT * FROM candles WHERE LOWER(category) = LOWER(?)");
    $stmt->execute([$category]);
} else {
    $stmt = $conn->prepare("SELECT * FROM candles");
    $stmt->execute();
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLIS | Our Collection</title>

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

    <main class="container">
        <section class="shop-header">
            <h1>Our Collection</h1>
            <p>Explore scents by season</p>
            <div class="category-nav">
                <a href="shop.php?category=All" class="<?= $category == 'All' ? 'active' : '' ?>">ALL</a>
                <?php foreach ($navCategories as $navCat):
                    $navName = htmlspecialchars($navCat['name']);
                ?>
                    <a href="shop.php?category=<?= urlencode($navName) ?>" class="<?= $category === $navName ? 'active' : '' ?>"><?= strtoupper($navName) ?></a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="product-grid">
            <?php if ($products): ?>
                <?php foreach($products as $row): ?>
                    <?php 
                        $id = $row['product_id'] ?? $row['id'] ?? null; // Fallback for potential old schema 'id'
                        $img = htmlspecialchars($row['image_url'] ?? $row['img_path'] ?? ''); // Fallback for potential old schema 'img_path'
                        $name = htmlspecialchars($row['name']);
                        $scent_profile = htmlspecialchars($row['scent_notes'] ?? '');
                        $price = number_format($row['price'], 2);
                        $stock_qty = isset($row['stock_qty']) ? (int)$row['stock_qty'] : 0;
                        $is_out_of_stock = $stock_qty <= 0;
                    ?>
                    <div class="product-card<?= $is_out_of_stock ? ' out-of-stock' : '' ?>">
                        <div class="product-image-wrap">
                            <img src="<?= $img ?>" class="product-img" alt="<?= $name ?>">
                            <?php if ($is_out_of_stock): ?>
                                <div class="out-of-stock-badge">Sold Out</div>
                            <?php endif; ?>
                        </div>
                        <h3><?= $name ?></h3>
                        <p class="scent"><?= $scent_profile ?></p>
                        <p class="price">₱<?= $price ?></p>
                        <?php if ($is_logged_in): ?>
                            <form onsubmit="if (<?= $is_out_of_stock ? 'true' : 'false' ?>) { event.preventDefault(); return false; } addToBasket(event, this)">
                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="size" value="Small"> 
                                <button type="submit" class="btn-add<?= $is_out_of_stock ? ' disabled sold-out' : '' ?>"<?= $is_out_of_stock ? ' disabled' : '' ?>><?= $is_out_of_stock ? 'Sold Out' : 'Add to Basket' ?></button>
                            </form>
                            <a href="product_details.php?id=<?= $id ?>" class="view-details">View Details</a>
                        <?php else: ?>
                            <a href="login.php" class="btn-add<?= $is_out_of_stock ? ' disabled' : '' ?>"<?= $is_out_of_stock ? ' aria-disabled="true"' : '' ?>>LOGIN TO PURCHASE</a>
                            <a href="product_details.php?id=<?= $id ?>" class="view-details">View Details</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="shop-empty-message">
                    <h2 class="shop-empty-title">New scents coming soon.</h2>
                    <p>Our artisans are currently hand-pouring the next batch for the <?= htmlspecialchars($category) ?> collection.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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