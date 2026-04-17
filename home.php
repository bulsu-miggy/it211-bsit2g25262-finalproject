<?php
session_start();
include 'db.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Alert logic
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    echo "<script>alert('Item added to cart!');</script>";
}

// --- FETCH PRODUCTS ---
$new_arrival_query = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock > 0 ORDER BY p.id DESC LIMIT 6");
$limited_query = mysqli_query($conn, "SELECT p.* FROM products p JOIN categories c ON p.category_id = c.id WHERE c.name = 'Limited Edition' AND p.stock > 0 ORDER BY p.id DESC");

// --- ADDED: FETCH FEATURED CATEGORIES ---
$featured_categories = mysqli_query($conn, "SELECT * FROM categories WHERE is_active = 1 AND is_featured = 1");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --spark-blue: #00B4D8; --spark-yellow: #FFD700; --spark-red: #C1121F; }
        body { font-family: 'Poppins', sans-serif; background-color: #fdfdfd; margin: 0; padding-top: 80px; }

        /* Navbar Design */
        .navbar { position: fixed; top: 0; left: 0; right: 0; background: white; border-top: 5px solid var(--spark-yellow); border-bottom: 2px solid var(--spark-yellow); box-shadow: 0 2px 10px rgba(0,0,0,0.05); z-index: 9999; height: 80px; display: flex; align-items: center; }
        .nav-container { width: 95%; max-width: 1400px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-section img { height: 55px; width: 55px; border-radius: 50%; }
        .logo-section span { font-weight: 800; font-size: 22px; color: #000; letter-spacing: 0.5px; }
        .nav-links { display: flex; gap: 30px; align-items: center; }
        .nav-links a { text-decoration: none; color: var(--spark-blue); font-weight: 700; font-size: 16px; }
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .nav-right a { text-decoration: none; font-size: 24px; color: #333; }
        .logout-btn { color: var(--spark-red) !important; font-weight: 700; font-size: 18px !important; }

    
        /* Product Slideshow */
        .slideshow-wrapper { width: 100%; overflow: hidden; position: relative; background: #eee; }
        .slides-track { display: flex; width: 300%; animation: slideAnimation 12s infinite ease-in-out; }
        .slides-track img { width: 33.333%; display: block; object-fit: cover; max-height: 500px; }
        @keyframes slideAnimation { 0% { transform: translateX(0); } 30% { transform: translateX(0); } 33% { transform: translateX(-33.33%); } 63% { transform: translateX(-33.33%); } 66% { transform: translateX(-66.66%); } 96% { transform: translateX(-66.66%); } 100% { transform: translateX(0); } }
        .main-wrapper { padding: 40px 20px; min-height: 600px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section-title { color: var(--spark-blue); font-size: 28px; font-weight: 800; margin-bottom: 25px; text-transform: uppercase; border-left: 5px solid var(--spark-yellow); padding-left: 15px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-bottom: 50px; }
        .product-card { background: white; border-radius: 15px; padding: 20px; position: relative; border: 1px solid #f0f0f0; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .product-card img { width: 100%; height: 250px; object-fit: contain; }
        .info h3 { font-size: 14px; color: #333; height: 45px; overflow: hidden; margin: 15px 0 5px 0; font-weight: 700; }
        .price { color: var(--spark-blue); font-weight: 800; font-size: 18px; }
        .add-btn { position: absolute; right: 20px; bottom: 20px; background: var(--spark-yellow); border: none; width: 40px; height: 40px; border-radius: 50%; color: white; cursor: pointer; font-size: 22px; display: flex; align-items: center; justify-content: center; }
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
            <a href="home.php" style="color: var(--spark-blue); border-bottom: 2px solid var(--spark-blue);">Home</a>
            <a href="albums.php">Albums</a>
            <a href="photocards.php">Photocards</a>
            <a href="lightsticks.php">Lightsticks</a>
            <a href="merchandise.php">Merchandise</a>
            <a href="giftcards.php">Gift Cards</a>
            <a href="my_orders.php">My Orders</a>
        </nav>
        <div class="nav-right">
            <a href="cart.php">🛒</a>
            <a href="profile.php">👤</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</header>

<div class="slideshow-wrapper">
    <div class="slides-track">
        <img src="HOMEPAGE/banner1.jpg" alt="Banner 1">
        <img src="HOMEPAGE/banner2.png" alt="Banner 2">
        <img src="HOMEPAGE/banner3.png" alt="Banner 3">
    </div>
</div>

<div class="main-wrapper">
    <div class="container">
        <h2 class="section-title">New Arrival</h2>
        <div class="product-grid">
            <?php if($new_arrival_query): while($row = mysqli_fetch_assoc($new_arrival_query)): 
                $img_path = (strpos($row['image'], '/') !== false) ? $row['image'] : 'uploads/' . $row['image'];
            ?>
            <div class="product-card">
                <img src="<?php echo $img_path; ?>" alt="Product">
                <div class="info">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p class="price">₱ <?php echo number_format($row['price'], 2); ?></p>
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                        <input type="hidden" name="product_image" value="<?php echo $img_path; ?>">
                        <button type="submit" name="add_to_cart" class="add-btn">+</button>
                    </form>
                </div>
            </div>
            <?php endwhile; endif; ?>
        </div>

        <?php while($cat = mysqli_fetch_assoc($featured_categories)): 
            $cat_id = $cat['id'];
            $cat_name = $cat['name'];
            $p_query = mysqli_query($conn, "SELECT * FROM products WHERE category_id = '$cat_id' AND stock > 0 ORDER BY id DESC LIMIT 4");
            if(mysqli_num_rows($p_query) > 0): 
        ?>
            <h2 class="section-title"><?php echo htmlspecialchars($cat_name); ?></h2>
            <div class="product-grid">
                <?php while($p = mysqli_fetch_assoc($p_query)): 
                    $p_img = (strpos($p['image'], '/') !== false) ? $p['image'] : 'uploads/' . $p['image']; 
                ?>
                <div class="product-card">
                    <img src="<?php echo $p_img; ?>" alt="Product">
                    <div class="info">
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <p class="price">₱ <?php echo number_format($p['price'], 2); ?></p>
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($p['name']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo $p['price']; ?>">
                            <input type="hidden" name="product_image" value="<?php echo $p_img; ?>">
                            <button type="submit" name="add_to_cart" class="add-btn">+</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; endwhile; ?>
    </div>
</div>
</script>

</body>
</html>