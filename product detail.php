<?php
require 'config.php';
require 'db/action/dbconfig.php';
function escape_product_detail($value)
{
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function resolve_product_image_path_product_detail($rawImage)
{
  $value = trim((string) $rawImage);
  if ($value === '') {
    return 'assets2/adidasblablabla.png';
  }

  $value = str_replace('\\', '/', $value);
  if (preg_match('#^(https?:)?//#i', $value) || strpos($value, 'data:') === 0) {
    return $value;
  }

  if (strpos($value, 'assets2/') === 0 || strpos($value, 'images/') === 0) {
    return $value;
  }

  return 'images/products/' . basename($value);
}
$product = null;
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId > 0) {
  try {
    $stmt = $conn->prepare("SELECT p.*
      FROM products p
      WHERE p.id = :id
      LIMIT 1");
    $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $product = null;
    // var_dump($e->getMessage());
    //  die("Error fetching product details.");
  }
}

if (!$product) {
  header("Location: index.php");
  exit();
}

$rawImage = resolve_product_image_path_product_detail($product['image'] ?? '');

$displayName = escape_product_detail($product['name'] ?? 'Unknown Product');
$displayPrice = number_format((float) ($product['price'] ?? 0), 2);
$displayColor = escape_product_detail($product['color'] ?? 'N/A');
$displaySize = escape_product_detail($product['size'] ?? 'N/A');
$displayCategory = escape_product_detail($product['category'] ?? 'N/A');
$displayImage = escape_product_detail($rawImage);
$displaySales = number_format((int) ($product['sales'] ?? 0));
$displayStock = (int) ($product['stock'] ?? 0);
$displayDescription = escape_product_detail($product['description'] ?? 'No description available.');
$displayProductId = (int) ($product['id'] ?? 0);


// fetch similar products (same category, excluding current product)
function get_similar_products(PDO $conn, int $currentProductId, string $category, int $limit = 4)
{
    try {
        $stmt = $conn->prepare(
            "SELECT p.id, p.name, p.category, p.color, p.size, p.price, p.image, p.sales
             FROM products p
             WHERE p.category = :category AND p.id != :currentId 
             ORDER BY p.sales DESC 
             LIMIT :limit"
        );
        $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':currentId', $currentProductId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function product_detail_url(array $product)
{
    return !empty($product['id']) ? 'product detail.php?id=' . urlencode((string) $product['id']) : 'product detail.php';
}

$similarProducts = get_similar_products($conn, $displayProductId, $product['category'], 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Details - Laces</title>
  <link rel="icon" type="image/png" href="assets2/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/master.css">
  <link rel="stylesheet" href="css/productpage.css">
  <!-- header fix -->
  <style>
    .navbar .container {
      max-width: 1320px !important;
      padding-right: 12px !important;
      padding-left: 12px !important;
    }
  </style>
  
  <!-- Carousel -->
  <style>
    .product-carousel .carousel-item img {
      height: auto;
      max-height: 500px;
      object-fit: contain;
      width: 100%;
      border-radius: 16px;
    }
    .thumbnail-carousel {
      margin-top: 20px;
    }
    .thumbnail-item {
      cursor: pointer;
      opacity: 0.6;
      transition: opacity 0.2s, transform 0.2s;
      border-radius: 8px;
      overflow: hidden;
    }
    .thumbnail-item:hover {
      opacity: 0.8;
      transform: scale(1.02);
    }
    .thumbnail-item.active {
      opacity: 1;
      border: 2px solid #f4d35e;
    }
    .thumbnail-item img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
</head>
<body class="bg-white">
    <nav class="navbar bg-white border-bottom py-3">
    <div class="container d-flex align-items-center">
        <a href="index.php" class="d-flex align-items-center text-decoration-none text-dark fw-bold fs-4 me-3">
          <img src="assets2/logo.png" alt="Laces" style="width:35px;" class="me-2">
          Laces
        </a>
        <form class="flex-grow-1 mx-3 d-flex justify-content-center" role="search">
          <div class="position-relative w-100" style="max-width: 900px;">
            <input class="form-control rounded-pill border-dark ps-3 pe-5" type="search" placeholder="Search...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
          </div>
        </form>
        <div class="d-flex align-items-center gap-3">
                <a href="cart/cart.php"> <button class="btn p-0 border-0 bg-transparent">
            <img src="assets2/cart.png" width="20">
        </button></a>
          <button class="btn p-0 border-0 bg-transparent"><img src="assets2/world.png" width="20"></button>
          <button class="btn p-0 border-0 bg-transparent"><img src="assets2/si--notifications-alt-2-fill.png" width="20"></button>
          <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileMenu"><img src="assets2/gg--profile.png" width="20"></button>
        </div>
    </div>
    </nav>

    <div class="text-center mt-3">
      <a href="index.php" class="mx-3 text-dark text-decoration-none custom-hover">Home</a>
      <a href="product-list.php?sort=sales&order=DESC" class="mx-3 text-dark text-decoration-none custom-hover">Trending</a>
      <a href="product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Categories</a>
      <a href="product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Product List</a>
    </div>
         <div class="offcanvas offcanvas-end" tabindex="-1" id="profileMenu" aria-labelledby="profileMenuLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="profileMenuLabel">My Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="text-center mb-4">
                <img src="assets2/gg--profile.png" width="70" class="mb-2 opacity-75">
                <h6 class="fw-bold">Welcome back!</h6>
                <p class="small text-muted">Manage your orders and preferences</p>
            </div>
            <div class="list-group list-group-flush">
              <div class="list-group-item border-0 py-3 theme-toggle-item" data-theme-toggle-item>
                <label class="theme-toggle-label mb-0 form-check form-switch">
                  <span class="theme-toggle-copy">
                    <i class="bi bi-moon-stars me-3"></i> Dark mode
                  </span>
                  <input class="form-check-input theme-toggle-input" type="checkbox" role="switch" aria-label="Toggle dark mode">
                </label>
              </div>
                <a href="profilepage.php" class="list-group-item list-group-item-action border-0 py-3">
                    <i class="bi bi-person-circle me-3"></i> View Profile
                </a>
                <a href="cart/orderHistory.php" class="list-group-item list-group-item-action border-0 py-3">
                    <i class="bi bi-box-seam me-3"></i> My Orders
                </a>
                <a href="db/action/logout.php" class="list-group-item list-group-item-action border-0 py-3 text-danger">
                    <i class="bi bi-box-arrow-right me-3"></i> Sign Out
                </a>
            </div>
        </div>
    </div>
    <main>
      <div class="product-container">
        <!-- Product -->
        <div class="product">
          <div class="product-images">
            <!-- Bootstrap Carousel for main images -->
            <div id="productCarousel" class="carousel slide product-carousel" data-bs-ride="false">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <img src="<?php echo $displayImage; ?>" class="d-block w-100" alt="<?php echo $displayName; ?>">
                </div>
              </div>
            </div>

            <!-- pwede to remove since wala naman na carousel-->
            <div class="thumbnail-carousel d-flex justify-content-center gap-2 mt-3">
              <div class="thumbnail-item active" data-slide-to="0">
                <img src="<?php echo $displayImage; ?>" alt="Thumb 1">
              </div>
            </div>
          </div>

          <div class="product-info">
            <h1><?php echo $displayName; ?></h1>
            <div class="badge"><?php echo $displayCategory; ?></div>
            <div class="price">₱<?php echo $displayPrice; ?></div>
            <div class="color"><?php echo $displayColor; ?></div>

            <div class="row">
              <div><label>Size</label><div class="value"><?php echo $displaySize; ?></div></div>
              <div><label>Quantity</label>
            <div class="value d-flex align-items-center gap-2">
              <button type="button" id="qtyMinus" class="btn btn-sm btn-outline-secondary">−</button>
              <span id="qtyValue">1</span>
              <button type="button" id="qtyPlus" class="btn btn-sm btn-outline-secondary">+</button>
                  </div>
              </div>
            </div>

            <button class="btn-add add-to-cart-btn" data-product-id="<?php echo $displayProductId; ?>">Add to Basket</button>

            <details open>
              <summary><span><?php echo $displayName; ?></span><span class="arrow">▼</span></summary>
              <p><?php echo !empty($displayDescription) ? $displayDescription : 'No description available.'; ?></p>
            </details>

            <details>
              <summary><span>Details</span><span class="arrow">▼</span></summary>
              <div class="details-list">
                <div><strong>Color:</strong> <?php echo $displayColor; ?></div>
                <div><strong>Size:</strong> <?php echo $displaySize; ?></div>
                <div><strong>Category:</strong> <?php echo $displayCategory; ?></div>
                <div><strong>Stock:</strong> <?php echo $displayStock; ?></div>
              </div>
            </details>

            <button
              type="button"
              class="btn-fit"
              data-bs-toggle="modal"
              data-bs-target="#sizeFitModal"
            >
              Size and Fit
            </button>
          </div>
        </div>

        <!-- similar items -->
        <div class="similar">
          <h3>Similar Items</h3>
          <?php if (empty($similarProducts)): ?>
            <div class="text-center py-5 text-muted">
              <p class="fs-5"> Curating new shoes for you — coming soon!</p>
            </div>
          <?php else: ?>
          <div class="similar-grid">
            <?php foreach ($similarProducts as $sim): ?>
            <?php
              $simName  = escape_product_detail($sim['name']  ?? 'Similar Product');
              $simColor = escape_product_detail($sim['color'] ?? 'N/A');
              $simSize  = escape_product_detail($sim['size']  ?? 'N/A');
              $simPrice = number_format((float)($sim['price'] ?? 0), 2);
              $simSales = number_format((int)($sim['sales']   ?? 0));
              $simLink  = escape_product_detail(product_detail_url($sim));

              $simRawImage = resolve_product_image_path_product_detail($sim['image'] ?? '');
              $simImage = escape_product_detail($simRawImage);
            ?>
            <div class="item">
              <a href="<?php echo $simLink; ?>" style="text-decoration:none;color:inherit;">
                <img src="<?php echo $simImage; ?>" alt="<?php echo $simName; ?>">
                <h4><?php echo $simName; ?></h4>
                <p>Color: <?php echo $simColor; ?> | Size: <?php echo $simSize; ?></p>
                <p class="price">₱<?php echo $simPrice; ?></p>
                <p class="small text-muted mb-2"><?php echo $simSales; ?> sales</p>
                <div class="buttons">
                  <button class="add-to-cart-btn" data-product-id="<?php echo (int)($sim['id'] ?? 0); ?>">Add to Basket</button>
                  <button>Buy Now</button>
                </div>
              </a>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </main>

    <div class="modal fade" id="sizeFitModal" tabindex="-1" aria-labelledby="sizeFitModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content size-fit-modal">
          <div class="modal-header">
            <h5 class="modal-title fw-bold" id="sizeFitModalLabel">Size and Fit Guide</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small mb-3">Use this quick conversion chart for common sneaker sizes.</p>
            <div class="table-responsive">
              <table class="table align-middle mb-0 size-fit-table">
                <thead>
                  <tr>
                    <th scope="col">US</th>
                    <th scope="col">EU</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td>6</td><td>39</td></tr>
                  <tr><td>6.5</td><td>39.5</td></tr>
                  <tr><td>7</td><td>40</td></tr>
                  <tr><td>7.5</td><td>40.5</td></tr>
                  <tr><td>8</td><td>41</td></tr>
                  <tr><td>8.5</td><td>42</td></tr>
                  <tr><td>9</td><td>42.5</td></tr>
                  <tr><td>9.5</td><td>43</td></tr>
                  <tr><td>10</td><td>44</td></tr>
                  <tr><td>10.5</td><td>44.5</td></tr>
                  <tr><td>11</td><td>45</td></tr>
                  <tr><td>12</td><td>46</td></tr>
                </tbody>
              </table>
            </div>

            <div class="size-feel-section mt-4">
              <h6 class="fw-bold mb-3">Fit Notes</h6>
              <p class="mb-3">Sizing can vary by design, so here is a quick guide to help you choose more confidently:</p>
              <ul class="size-feel-list mb-0">
                <li><strong>Standard fit:</strong> If you usually wear this size, start there first.</li>
                <li><strong>Snug feel:</strong> Prefer extra toe room? Consider going up by half a size.</li>
                <li><strong>Shape profile:</strong> This style is best for regular-to-slim foot shapes.</li>
                <li><strong>Break-in:</strong> Upper materials may soften slightly after a few wears.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php
      $lacesFooterYear = '2024';
      require __DIR__ . '/includes/user/root_footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets2/js/master.js"></script>
    <script>
      // Thumbnail carousel controller (kept because it's specific to this page)
      const productCarousel = document.getElementById('productCarousel');
      const thumbnails = document.querySelectorAll('.thumbnail-item');
      
      thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
          const slideTo = parseInt(this.getAttribute('data-slide-to'));
          const carousel = bootstrap.Carousel.getInstance(productCarousel);
          carousel.to(slideTo);
          thumbnails.forEach(t => t.classList.remove('active'));
          this.classList.add('active');
        });
      });
      
      productCarousel.addEventListener('slid.bs.carousel', function(event) {
        const newIndex = event.to;
        thumbnails.forEach((thumb, idx) => {
          if (idx === newIndex) {
            thumb.classList.add('active');
          } else {
            thumb.classList.remove('active');
          }
        });
      });

      //for adding to bnasket
      let qty = 1;
      const qtyValue = document.getElementById('qtyValue');
      qtyValue.textContent = qty;
      document.getElementById('qtyPlus').addEventListener('click', function() {
              const max = <?php echo $displayStock; ?>;
              if (qty < max) {
                  qty++;
                  qtyValue.textContent = qty;
              }
      });
      document.getElementById('qtyMinus').addEventListener('click', function() {
              if (qty > 1) {
                  qty--;
                  qtyValue.textContent = String(qty);
              }
      });
    </script>
</body>
</html> 