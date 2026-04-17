<?php
  session_start();
  if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
    header('Location: login.php');
    exit();
  }
  include 'db/connection.php';

  $sub = isset($_GET['sub']) ? $_GET['sub'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LYNX - Men Collection</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

  <header class="header">
    <a href="index.php" style="text-decoration: none; color: black;"><h1 class="logo">LYNX</h1></a>
    
    <nav class="nav">
        <a href="women.php" style="text-decoration: none; color: black; font-weight: 500;">WOMEN</a>
        <a href="men.php" style="text-decoration: none; color: black; font-weight: 700;">MEN</a>
    </nav>

    <div class="icons">
        <span class="material-symbols-outlined">search</span>
        <span class="material-symbols-outlined">shopping_cart</span>
        <a href="profiles.php" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">person</span>
        </a>
    </div>
  </header>

    <!-- Main Banner -->
  <div class="main-banner">
      <img src="images/MAN.png" alt="Model" class="model">

  </div>

  <main class="home container">
    <h2 style="font-family: 'Rubik Mono One'; margin-bottom: 20px;">MEN'S COLLECTION</h2>
    
    <div class="sub-nav" style="margin-bottom: 40px; display: flex; gap: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
        <a href="men.php" style="text-decoration: none; color: <?php echo $sub == '' ? 'black' : 'gray'; ?>; font-weight: bold;">ALL</a>
        <a href="men.php?sub=basic tops" style="text-decoration: none; color: <?php echo $sub == 'basic tops' ? 'black' : 'gray'; ?>;">BASIC TOPS</a>
        <a href="men.php?sub=shorts" style="text-decoration: none; color: <?php echo $sub == 'shorts' ? 'black' : 'gray'; ?>;">SHORTS</a>
        <a href="men.php?sub=pants" style="text-decoration: none; color: <?php echo $sub == 'pants' ? 'black' : 'gray'; ?>;">PANTS</a>
        <a href="men.php?sub=outerwears" style="text-decoration: none; color: <?php echo $sub == 'outerwears' ? 'black' : 'gray'; ?>;">OUTERWEARS</a>
    </div>

    <div class="product-grid">
      <?php
        // DYNAMIC QUERY
        if ($sub != '') {
            $stmt = $conn->prepare("SELECT * FROM products WHERE main_category = 'Men' AND sub_category = ? ORDER BY id DESC");
            $stmt->execute([$sub]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM products WHERE main_category = 'Men' ORDER BY id DESC");
            $stmt->execute();
        }
        
        if($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      ?>
          <div class="product-card">
              <img src="images/products/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
              <div class="product-info" style="padding: 20px;">
                  <h3 class="product-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                  <p class="product-category" style="color: gray; font-size: 0.8rem;"><?php echo strtoupper(htmlspecialchars($row['sub_category'])); ?></p>
                  <p class="product-price">₱<?php echo number_format($row['price'], 2); ?></p>
                  
                  <div class="product-buttons" style="display: flex; gap: 10px;">
                      <a href="viewProduct.php?id=<?php echo $row['id']; ?>" 
                        class="btn-view" 
                        style="flex: 1; padding: 10px; background: #000; color: #fff; border: none; border-radius: 25px; text-decoration: none; text-align: center; font-size: 0.8rem; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                        VIEW
                      </a>
                      <button class="btn-cart" 
                              data-id="<?php echo $row['id']; ?>" 
                              style="flex: 1; padding: 10px; background: transparent; border: 2px solid #000; border-radius: 25px; cursor: pointer; font-weight: bold; font-size: 0.8rem;">
                              ADD TO CART
                      </button>
                  </div>
              </div>
          </div>
      <?php 
            } 
        } else {
            echo "<p>No products available in this category.</p>";
        }
      ?>
    </div>
  </main>

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

</body>
</html>

<script>
  $(document).ready(function() {
    // --- VIEW BUTTON ---
    $('.btn-view').on('click', function() {
        const data = $(this).data();
        $('#modalTitle').text(data.title);
        $('#modalDesc').text(data.desc);
        $('#modalPrice').text('₱' + parseFloat(data.price).toLocaleString());
        $('#modalImg').attr('src', data.image);
        $('#modalAddToCart').attr('data-id', data.id); // Ipasa ang ID sa modal button
        $('#productModal').css('display', 'flex');
    });

    $(document).on('click', '.btn-cart', function() {
        const productId = $(this).data('id');
        
        $.ajax({
            url: 'db/action/cart.php', 
            method: 'POST',
            data: { 
                action: 'add',
                product_id: productId,
                quantity: 1
            },
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Item added to your cart.',
                    icon: 'success',
                    confirmButtonColor: '#000'
                });
            },
            error: function() {
                alert('There was an error adding to the cart.');
            }
        });
    });
});

function closeModal() {
    $('#productModal').hide();
}
</script>