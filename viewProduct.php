<?php
  session_start();
  if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
    header('Location: login.php');
    exit();
  }
  include 'db/connection.php';

  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

  if ($id > 0) {
      $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
      $stmt->execute([$id]);
      $product = $stmt->fetch(PDO::FETCH_ASSOC);
  }

  if (!$product) {
      header('Location: index.php');
      exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> | LYNX</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;700&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #fff; font-family: 'Rubik', sans-serif; }
        .product-section { max-width: 1200px; margin: 60px auto; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; padding: 0 40px; }
        .image-container img { width: 100%; height: 700px; object-fit: cover; border-radius: 20px; }
        .product-title { font-family: 'Rubik Mono One', sans-serif; font-size: 2.5rem; margin: 10px 0; text-transform: uppercase; }
        
        /* Size Options */
        .size-options { display: flex; gap: 10px; margin-top: 10px; }
        .size-radio { display: none; }
        .size-label { width: 50px; height: 50px; border: 2px solid #eee; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .size-radio:checked + .size-label { background: black; color: white; border-color: black; }

        /* Quantity Counter */
        .qty-container { display: flex; align-items: center; gap: 15px; background: #f4f4f4; width: fit-content; padding: 8px 15px; border-radius: 50px; margin-top: 10px; }
        .qty-btn { background: white; border: none; width: 30px; height: 30px; border-radius: 50%; font-weight: bold; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #000000 font-size: 1.2rem }
        .qty-btn:hover { background: #000000; color: #ffffff; transition: 0.3s; }

        .btn-main-cart { width: 100%; padding: 20px; background: black; color: white; border: none; border-radius: 50px; font-weight: bold; font-size: 1rem; cursor: pointer; margin-top: 30px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s; }
        .btn-main-cart:hover { background: #333; transform: translateY(-2px); }
    </style>
<header class="header">
    <a href="index.php" style="text-decoration: none; color: black;"><h1 class="logo">LYNX</h1></a>
    
    <nav class="nav">
        <a href="women.php" style="text-decoration: none; color: black; font-weight: 700;">WOMEN</a>
        <a href="men.php" style="text-decoration: none; color: black; font-weight: 500;">MEN</a>
    </nav>

    <div class="icons">
        <span class="material-symbols-outlined">search</span>
        <span class="material-symbols-outlined">shopping_cart</span>
        <a href="profiles.php" title="Profile" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">account_circle</span>
        </a>
    </div>
  </header>

    <main class="product-section">
        <div class="image-container">
            <img src="images/products/<?php echo htmlspecialchars($product['image_url']); ?>">
        </div>

        <div class="details-container">
            <p style="color: gray; text-transform: uppercase; letter-spacing: 1px;"><?php echo htmlspecialchars($product['sub_category']); ?></p>
            <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
            <h2 style="font-weight: 700; font-size: 1.8rem;">₱<?php echo number_format($product['price'], 2); ?></h2>

            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <h4 style="font-size: 0.8rem; color: #999; margin-bottom: 10px;">PRODUCT DESCRIPTION</h4>
                <p style="color: #555; line-height: 1.6;">
                    <?php echo !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : "Premium quality apparel by LYNX. Designed for style and comfort."; ?>
                </p>
            </div>

            <div style="margin-top: 30px;">
                <h4 style="font-size: 0.8rem; color: #999;">SELECT SIZE</h4>
                <div class="size-options">
                    <?php foreach(['S', 'M', 'L', 'XL'] as $size): ?>
                        <input type="radio" name="product_size" value="<?php echo $size; ?>" id="size-<?php echo $size; ?>" class="size-radio" <?php echo $size == 'M' ? 'checked' : ''; ?>>
                        <label for="size-<?php echo $size; ?>" class="size-label"><?php echo $size; ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <h4 style="font-size: 0.8rem; color: #999;">QUANTITY</h4>
                <div class="qty-container">
                    <button type="button" class="qty-btn" id="minus-qty">-</button>
                    <span id="qty-val" style="font-weight: bold; min-width: 20px; text-align: center;">1</span>
                    <button type="button" class="qty-btn" id="plus-qty">+</button>
                </div>
            </div>

            <button class="btn-main-cart" id="add-to-cart" data-id="<?php echo $product['id']; ?>">
                <span class="material-symbols-outlined">add_shopping_cart</span>
                ADD TO CART
            </button>
        </div>
    </main>

    <script>
        $(document).ready(function() {
            let qty = 1;

            $('#plus-qty').click(function() {
                if(qty < 10) { qty++; $('#qty-val').text(qty); }
            });

            $('#minus-qty').click(function() {
                if(qty > 1) { qty--; $('#qty-val').text(qty); }
            });

            $('#add-to-cart').on('click', function() {
                const productId = $(this).data('id');
                const selectedSize = $('input[name="product_size"]:checked').val();
                
                $.ajax({
                    url: 'db/action/cart.php',
                    method: 'POST',
                    data: { 
                        action: 'add', 
                        product_id: productId, 
                        quantity: qty,
                        size: selectedSize 
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Added to your bag.',
                            icon: 'success',
                            confirmButtonColor: '#000'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>