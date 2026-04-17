<?php
/**
 * UniMerch — Customer Storefront
 * Main landing page with hero, category filters, and product grid
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

// Get categories for filter pills
$categories = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get featured products for hero
$featuredStmt = db()->prepare("SELECT p.*, c.code AS category_code, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.status = 'active' LIMIT 3");
$featuredStmt->execute();
$featuredProducts = $featuredStmt->fetchAll();

// Stats
$totalProducts = db()->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$totalCategories = db()->query("SELECT COUNT(*) FROM categories")->fetchColumn();

$customer = getCustomer();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniMerch — Official Campus Merchandise | BulSU</title>
  <meta name="description" content="Shop official Bulacan State University college merchandise. T-shirts, hoodies, accessories and more from CICT, CAFA, COE, CBA, COED, and COS.">
  
  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- UniMerch Styles -->
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/storefront.css" rel="stylesheet">
</head>
<body>

  <!-- ============================================================
       Navbar
       ============================================================ -->
  <nav class="um-navbar navbar navbar-expand-lg" id="mainNavbar">
    <div class="container-fluid px-md-5">
      <a class="navbar-brand" href="<?= BASE_URL ?>/">
        <i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span>
      </a>
      
      <div class="d-flex align-items-center gap-2 order-lg-last">
        <!-- Cart -->
        <a href="<?= BASE_URL ?>/cart.php" class="btn btn-ghost cart-link" id="navCartBtn">
          <i class="bi bi-bag"></i>
          <span class="hide-mobile ms-1">Cart</span>
          <span class="cart-badge" id="navCartBadge" style="display:none;">0</span>
        </a>

        <?php if ($customer): ?>
          <!-- Logged-in user -->
          <div class="dropdown">
            <button class="btn btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i>
              <span class="hide-mobile ms-1"><?= sanitize($customer['first_name']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php?tab=orders"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login.php" class="btn btn-ghost">
            <i class="bi bi-person"></i>
            <span class="hide-mobile ms-1">Login</span>
          </a>
          <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary-gradient hide-mobile">
            Sign Up
          </a>
        <?php endif; ?>
      </div>

      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="#categories-section">Colleges</a></li>
          <li class="nav-item"><a class="nav-link" href="#products-section">Products</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- ============================================================
       Hero Section
       ============================================================ -->
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-7">
          <div class="hero-content">
            <p class="text-uppercase fw-bold mb-2" style="color: var(--accent-400); font-size: 0.8rem; letter-spacing: 0.15em;">
              <i class="bi bi-stars me-1"></i> Bulacan State University
            </p>
            <h1 class="hero-title">
              Wear Your <span class="highlight">College</span><br>
              With Pride.
            </h1>
            <p class="hero-subtitle">
              Official merchandise for every Bulacan State University college. 
              From CICT hoodies to CBA polos — represent your department in style.
            </p>
            <div class="d-flex gap-3 flex-wrap">
              <a href="#products-section" class="btn btn-accent btn-lg px-4">
                <i class="bi bi-bag-heart me-2"></i>Shop Now
              </a>
              <a href="#categories-section" class="btn btn-ghost btn-lg px-4" style="color:rgba(255,255,255,0.8); border-color:rgba(255,255,255,0.2);">
                Browse Colleges
              </a>
            </div>
            <div class="hero-stats">
              <div class="hero-stat">
                <div class="hero-stat-value"><?= $totalProducts ?>+</div>
                <div class="hero-stat-label">Products</div>
              </div>
              <div class="hero-stat">
                <div class="hero-stat-value"><?= $totalCategories ?></div>
                <div class="hero-stat-label">Colleges</div>
              </div>
              <div class="hero-stat">
                <div class="hero-stat-value">100%</div>
                <div class="hero-stat-label">Official</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-5 hero-visual">
          <?php if (!empty($featuredProducts)): ?>
            <div class="hero-product-card">
              <img src="<?= BASE_URL ?>/uploads/<?= $featuredProducts[0]['image'] ?>" 
                   alt="<?= sanitize($featuredProducts[0]['name']) ?>"
                   onerror="this.src='https://placehold.co/400x280/1e40af/ffffff?text=<?= urlencode($featuredProducts[0]['name']) ?>'">
              <div class="hero-product-info">
                <h4><?= sanitize($featuredProducts[0]['name']) ?></h4>
                <p class="price"><?= formatPrice($featuredProducts[0]['price']) ?></p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Category Filters
       ============================================================ -->
  <section class="category-section" id="categories-section">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div class="category-pills" id="categoryPills">
          <button class="category-pill active" data-category="all">
            <i class="bi bi-grid-3x3-gap"></i> All
          </button>
          <?php foreach ($categories as $cat): ?>
            <button class="category-pill" data-category="<?= $cat['id'] ?>">
              <i class="bi <?= $cat['icon'] ?>"></i> <?= sanitize($cat['code']) ?>
            </button>
          <?php endforeach; ?>
        </div>
        <div class="search-container">
          <i class="bi bi-search search-icon"></i>
          <input type="text" id="searchInput" placeholder="Search merchandise..." autocomplete="off">
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Products Grid
       ============================================================ -->
  <section class="products-section" id="products-section">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <h2 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.5rem; margin:0;">
            Campus Merch
          </h2>
          <p class="text-muted mb-0" id="productsCount" style="font-size:0.85rem;"></p>
        </div>
        <div class="dropdown">
          <button class="btn btn-ghost btn-sm dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-sort-down me-1"></i>Sort By
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item sort-option active" href="#" data-sort="newest">Newest</a></li>
            <li><a class="dropdown-item sort-option" href="#" data-sort="price_asc">Price: Low → High</a></li>
            <li><a class="dropdown-item sort-option" href="#" data-sort="price_desc">Price: High → Low</a></li>
            <li><a class="dropdown-item sort-option" href="#" data-sort="name_asc">Name: A → Z</a></li>
          </ul>
        </div>
      </div>

      <!-- Product Grid (AJAX-populated) -->
      <div class="products-grid" id="productsGrid">
        <!-- Skeleton loaders (shown while loading) -->
        <?php for ($i = 0; $i < 8; $i++): ?>
        <div class="product-card skeleton-card">
          <div class="skeleton" style="height: 260px;"></div>
          <div style="padding: 1.25rem;">
            <div class="skeleton" style="height: 14px; width: 60px; margin-bottom: 8px;"></div>
            <div class="skeleton" style="height: 18px; width: 80%; margin-bottom: 8px;"></div>
            <div class="skeleton" style="height: 22px; width: 40%;"></div>
          </div>
        </div>
        <?php endfor; ?>
      </div>

      <!-- Load More -->
      <div class="text-center mt-4" id="loadMoreContainer" style="display:none;">
        <button class="btn btn-ghost btn-lg px-5" id="loadMoreBtn">
          <i class="bi bi-arrow-down-circle me-2"></i>Load More
        </button>
      </div>

      <!-- Empty State -->
      <div class="text-center py-5" id="emptyState" style="display:none;">
        <i class="bi bi-search" style="font-size:3rem; color:var(--gray-300);"></i>
        <h4 class="mt-3" style="color:var(--gray-600);">No products found</h4>
        <p class="text-muted">Try adjusting your search or filter criteria.</p>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Product Detail Modal
       ============================================================ -->
  <div class="modal fade product-modal" id="productModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0 position-relative">
          <button type="button" class="btn-close position-absolute top-0 end-0 mt-3 me-3" style="z-index: 1055;" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-md-6">
              <div class="product-modal-img">
                <img id="modalProductImg" src="" alt="">
              </div>
            </div>
            <div class="col-md-6 pe-4">
              <span class="badge bg-primary-subtle text-primary" id="modalProductCategory"></span>
              <h3 class="product-modal-title" id="modalProductName"></h3>
              <div class="product-modal-price" id="modalProductPrice"></div>
              <p class="product-modal-desc" id="modalProductDesc"></p>
              
              <div id="modalSizeSection">
                <label class="fw-semibold mb-2 d-block">Select Size</label>
                <div class="size-selector" id="modalSizes"></div>
              </div>

              <div class="mt-3">
                <label class="fw-semibold mb-2 d-block">Quantity</label>
                <div class="qty-selector">
                  <button type="button" id="qtyMinus">−</button>
                  <input type="number" id="qtyInput" value="1" min="1" readonly>
                  <button type="button" id="qtyPlus">+</button>
                </div>
                <small class="text-muted d-block mt-1" id="modalStock"></small>
              </div>

              <div class="d-grid gap-2 mt-4">
                <button class="btn btn-accent btn-lg" id="addToCartBtn">
                  <i class="bi bi-bag-plus me-2"></i>Add to Cart
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================
       Footer
       ============================================================ -->
  <footer class="um-footer">
    <div class="container-fluid px-md-5">
      <div class="row g-4 justify-content-between">
        <div class="col-lg-4">
          <a href="<?= BASE_URL ?>/" class="footer-brand d-block mb-2">
            <i class="bi bi-mortarboard-fill me-1"></i>Uni<span>Merch</span>
          </a>
          <p style="font-size:0.9rem; max-width:300px;">
            The official campus merchandise platform for Bulacan State University. 
            Wear your college with pride.
          </p>
        </div>
        <div class="col-6 col-lg-2">
          <h5>Shop</h5>
          <ul class="list-unstyled" style="font-size:0.9rem;">
            <li class="mb-2"><a href="<?= BASE_URL ?>/">All Products</a></li>
            <li class="mb-2"><a href="#">New Arrivals</a></li>
            <li class="mb-2"><a href="#">Best Sellers</a></li>
          </ul>
        </div>
        <div class="col-6 col-lg-2">
          <h5>Colleges</h5>
          <ul class="list-unstyled" style="font-size:0.9rem;">
            <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
              <li class="mb-2"><a href="#"><?= sanitize($cat['code']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="col-lg-3 text-lg-end">
          <h5>Stay Connected</h5>
          <p style="font-size:0.85rem;">Follow us for new drops and exclusive deals.</p>
          <div class="d-flex gap-3 justify-content-lg-end">
            <a href="#" style="font-size:1.25rem;"><i class="bi bi-facebook"></i></a>
            <a href="#" style="font-size:1.25rem;"><i class="bi bi-instagram"></i></a>
            <a href="#" style="font-size:1.25rem;"><i class="bi bi-twitter-x"></i></a>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p class="mb-0">© <?= date('Y') ?> UniMerch × BulSU. All rights reserved. Built for IT211 Final Project.</p>
      </div>
    </div>
  </footer>

  <!-- Toast Container -->
  <div class="toast-container" id="toastContainer"></div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>const BASE_URL = '<?= BASE_URL ?>';</script>
  <script src="<?= BASE_URL ?>/assets/js/common.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/storefront.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/cart.js"></script>
</body>
</html>
