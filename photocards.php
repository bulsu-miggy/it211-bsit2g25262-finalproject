<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// --- AJAX LOGIC PARA SA ADD TO CART ---
if (isset($_POST['ajax_add'])) {
    $u_id = $_SESSION['user_id'];
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $p_price = $_POST['p_price'];
    $p_image = $_POST['p_image'];

    $check_cart = mysqli_query($conn, "SELECT * FROM cart WHERE product_name = '$p_name' AND user_id = '$u_id'");

    if ($check_cart && mysqli_num_rows($check_cart) > 0) {
        mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE product_name = '$p_name' AND user_id = '$u_id'");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, product_name, product_price, product_image, quantity)
                            VALUES ('$u_id', '$p_name', '$p_price', '$p_image', 1)");
    }
    echo "success";
    exit();
}

// --- DATABASE FETCHING (Clean & Dynamic) ---
$query = "SELECT p.* FROM products p 
          JOIN categories c ON p.category_id = c.id 
          WHERE c.name = 'Photocards' AND p.stock > 0 
          ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sparkverse | Photocards</title>
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
        .nav-links a.active { color: #00B4D8; border-bottom: 2px solid #00B4D8; }
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .nav-right a { text-decoration: none; font-size: 24px; color: #333; }
        .logout-btn { color: #C1121F !important; font-weight: 700; }
        .main-wrapper { padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .page-title { color: #00B4D8; font-size: 32px; font-weight: bold; margin-bottom: 25px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .product-card { background: white; border-radius: 15px; padding: 20px; position: relative; border: 1px solid #f0f0f0; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .product-card:hover { transform: translateY(-5px); }
        .product-card img { width: 100%; height: 250px; object-fit: contain; }
        .info h3 { font-size: 13px; color: #333; height: 40px; overflow: hidden; margin-bottom: 10px; text-transform: uppercase; font-weight: 700; line-height: 1.4; }
        .price { color: #00B4D8; font-weight: bold; font-size: 18px; margin: 10px 0; }
        .add-btn { position: absolute; right: 20px; bottom: 20px; background: #FFD700; border: none; width: 40px; height: 40px; border-radius: 50%; color: white; cursor: pointer; font-size: 20px; transition: 0.3s; }
        .add-btn:hover { background: #e6c200; transform: scale(1.1); }
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
            <a href="home.php">Home</a>
            <a href="albums.php">Albums</a>
            <a href="photocards.php" class="active">Photocards</a>
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

<div class="main-wrapper">
    <div class="container">
        <h1 class="page-title">K-Pop Photocards</h1>
        <div class="product-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($pc = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($pc['image']); ?>" alt="PC">
                        <div class="info">
                            <h3><?php echo htmlspecialchars($pc['name']); ?></h3>
                            <p class="price">₱ <?php echo number_format($pc['price'], 2); ?></p>
                            <button class="add-btn add-to-cart-ajax"
                                data-name="<?php echo htmlspecialchars($pc['name']); ?>"
                                data-price="<?php echo $pc['price']; ?>"
                                data-image="<?php echo htmlspecialchars($pc['image']); ?>">+</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No photocards found in the database.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.add-to-cart-ajax').forEach(button => {
    button.addEventListener('click', function() {
        const name = this.getAttribute('data-name');
        const price = this.getAttribute('data-price');
        const image = this.getAttribute('data-image');
        
        const formData = new FormData();
        formData.append('ajax_add', '1');
        formData.append('p_name', name);
        formData.append('p_price', price);
        formData.append('p_image', image);

        fetch(window.location.href, { method: 'POST', body: formData })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                this.innerHTML = '✔';
                this.style.background = '#00B4D8';
                setTimeout(() => {
                    this.innerHTML = '+';
                    this.style.background = '#FFD700';
                }, 1500);
                alert(name + " added to your Sparkverse cart!");
            }
        });
    });
});
</script>
</body>
</html>