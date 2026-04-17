<?php
/**
 * UniMerch — Product Single Page (PDP)
 * Displays full details for a specific merchandise item.
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

// Fetch product with category info
$stmt = db()->prepare("
    SELECT p.*, c.code AS category_code, c.name AS category_name, c.color AS category_color, c.icon AS category_icon
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.status = 'active'
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$sizes = $product['sizes'] ? json_decode($product['sizes'], true) : null;
$customer = getCustomer();

// Get related products from the same college
$relatedStmt = db()->prepare("
    SELECT p.*, c.code AS category_code 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
    LIMIT 4
");
$relatedStmt->execute([$product['category_id'], $productId]);
$relatedProducts = $relatedStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= sanitize($product['name']) ?> — UniMerch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/storefront.css" rel="stylesheet">
  <style>
    .pdp-category-badge {
      background-color: <?= $product['category_color'] ?>20;
      color: <?= $product['category_color'] ?>;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }
    .pdp-price {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--primary-700);
      margin-bottom: 1.5rem;
    }
    .pdp-img-container {
      background: #f8fafc;
      border-radius: 24px;
      padding: 2rem;
      position: sticky;
      top: 100px;
    }
    .pdp-img-container img {
      width: 100%;
      height: auto;
      mix-blend-mode: multiply;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="um-navbar navbar navbar-expand-lg scrolled">
    <div class="container-fluid px-md-5">
      <a class="navbar-brand" href="<?= BASE_URL ?>/">
        <i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span>
      </a>
      
      <div class="d-flex align-items-center gap-2 order-lg-last">
        <a href="<?= BASE_URL ?>/cart.php" class="btn btn-ghost cart-link" id="navCartBtn">
          <i class="bi bi-bag"></i>
          <span class="cart-badge" id="navCartBadge" style="display:none;">0</span>
        </a>
        <?php if ($customer): ?>
          <div class="dropdown">
            <button class="btn btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i>
              <span class="hide-mobile ms-1"><?= sanitize($customer['first_name']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php?tab=orders"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php" onclick="handleLogout(event, 'customer'); return false;"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login.php" class="btn btn-ghost"><i class="bi bi-person"></i></a>
        <?php endif; ?>
      </div>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/#categories-section">Colleges</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main class="py-5 mt-5">
    <div class="container">
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/#categories-section"><?= sanitize($product['category_code']) ?></a></li>
          <li class="breadcrumb-item active"><?= sanitize($product['name']) ?></li>
        </ol>
      </nav>

      <div class="row g-5">
        <!-- Product Image -->
        <div class="col-lg-6">
          <div class="pdp-img-container">
            <img src="<?= BASE_URL ?>/uploads/<?= $product['image'] ?>" 
                 alt="<?= sanitize($product['name']) ?>"
                 onerror="this.src='https://placehold.co/600x600/<?= str_replace('#', '', $product['category_color']) ?>/ffffff?text=<?= urlencode($product['name']) ?>'">
          </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
          <div class="pdp-content">
            <div class="pdp-category-badge">
              <i class="bi <?= $product['category_icon'] ?>"></i>
              <?= sanitize($product['category_name']) ?> (<?= sanitize($product['category_code']) ?>)
            </div>
            <h1 class="display-5 fw-bold mb-3"><?= sanitize($product['name']) ?></h1>
            <div class="pdp-price"><?= formatPrice($product['price']) ?></div>
            
            <p class="text-muted fs-5 mb-4">
              <?= nl2br(sanitize($product['description'])) ?>
            </p>

            <hr class="my-4">

            <!-- Options Form -->
            <form id="pdpAddToCartForm">
              <?php if ($sizes): ?>
                <div class="mb-4">
                  <label class="fw-bold mb-2 d-block">Select Size</label>
                  <div class="size-selector d-flex gap-2 flex-wrap">
                    <?php foreach ($sizes as $size): ?>
                      <div class="size-option">
                        <input type="radio" class="btn-check" name="size" id="size-<?= $size ?>" value="<?= $size ?>" required>
                        <label class="btn btn-outline-primary px-3 py-2" for="size-<?= $size ?>"><?= $size ?></label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <div class="mb-4">
                <label class="fw-bold mb-2 d-block">Quantity</label>
                <div class="d-flex align-items-center gap-3">
                  <div class="qty-selector" style="max-width: 150px;">
                    <button type="button" id="qtyMinus" class="btn btn-outline-secondary">−</button>
                    <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="form-control text-center mx-2" readonly>
                    <button type="button" id="qtyPlus" class="btn btn-outline-secondary">+</button>
                  </div>
                  <span class="text-muted small"><?= $product['stock'] ?> units available</span>
                </div>
              </div>

              <div class="d-grid gap-3">
                <button type="submit" class="btn btn-primary-gradient btn-lg py-3 fw-bold" id="pdpAddBtn">
                  <i class="bi bi-bag-plus-fill me-2"></i>Add to Cart
                </button>
                <button type="button" class="btn btn-outline-accent btn-lg py-3 fw-bold">
                  <i class="bi bi-heart me-2"></i>Save to Wishlist
                </button>
              </div>
            </form>

            <div class="mt-5 p-4 bg-light rounded-4">
              <div class="d-flex gap-4">
                <div class="text-center">
                  <i class="bi bi-shield-check fs-2 text-primary"></i>
                  <div class="small fw-bold mt-1">Official Merch</div>
                </div>
                <div class="text-center">
                  <i class="bi bi-truck fs-2 text-primary"></i>
                  <div class="small fw-bold mt-1">Campus Pickup</div>
                </div>
                <div class="text-center">
                  <i class="bi bi-arrow-repeat fs-2 text-primary"></i>
                  <div class="small fw-bold mt-1">Easy Returns</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <?php if ($relatedProducts): ?>
        <section class="mt-5 pt-5">
          <h3 class="fw-bold mb-4">More from <?= sanitize($product['category_code']) ?></h3>
          <div class="row g-4">
            <?php foreach ($relatedProducts as $rp): ?>
              <div class="col-6 col-md-3">
                <a href="<?= BASE_URL ?>/product.php?id=<?= $rp['id'] ?>" class="text-decoration-none text-dark">
                  <div class="card border-0 h-100 shadow-sm rounded-4 overflow-hidden">
                    <img src="<?= BASE_URL ?>/uploads/<?= $rp['image'] ?>" class="card-img-top" alt="<?= sanitize($rp['name']) ?>"
                         onerror="this.src='https://placehold.co/300x300?text=Product'">
                    <div class="card-body p-3">
                      <div class="small text-primary fw-bold mb-1"><?= $rp['category_code'] ?></div>
                      <h6 class="card-title fw-bold mb-2"><?= sanitize($rp['name']) ?></h6>
                      <div class="fw-bold text-primary"><?= formatPrice($rp['price']) ?></div>
                    </div>
                  </div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <footer class="um-footer mt-5">
    <div class="container-fluid px-md-5">
      <div class="footer-bottom">
        <p class="mb-0">© <?= date('Y') ?> UniMerch × BulSU. Built for IT211 Compliance.</p>
      </div>
    </div>
  </footer>

  <div class="toast-container" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>const BASE_URL = '<?= BASE_URL ?>';</script>
  <script src="<?= BASE_URL ?>/assets/js/common.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/cart.js"></script>
  <script>
    $(document).ready(function() {
      $('#qtyMinus').click(function() {
        let v = parseInt($('#qtyInput').val());
        if (v > 1) $('#qtyInput').val(v - 1);
      });
      $('#qtyPlus').click(function() {
        let v = parseInt($('#qtyInput').val());
        if (v < <?= $product['stock'] ?>) $('#qtyInput').val(v + 1);
      });

      $('#pdpAddToCartForm').submit(function(e) {
        e.preventDefault();
        const btn = $('#pdpAddBtn');
        const formData = {
          product_id: <?= $productId ?>,
          quantity: parseInt($('#qtyInput').val()),
          size: $('input[name="size"]:checked').val() || null
        };

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');

        $.ajax({
          url: BASE_URL + '/api/cart.php',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(formData),
          success: function(res) {
            if (res.success) {
              showToast(res.message, 'success');
              updateCartBadge(res.cart_count);
            } else {
              showToast(res.message, 'error');
            }
          },
          complete: function() {
            btn.prop('disabled', false).html('<i class="bi bi-bag-plus-fill me-2"></i>Add to Cart');
          }
        });
      });
    });
  </script>
</body>
</html>
