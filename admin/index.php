<?php
  require 'config.php';
  require 'db/action/dbconfig.php';

  session_start();

  if (!isset($_SESSION["username"]) || !isset($_SESSION["password"]))
  {
    echo "hello";
    session_destroy();
    header('Location: loginpage.php');
    exit();
  } 

  $username = $_SESSION["username"];
  $stmt = $conn->prepare("SELECT * FROM login WHERE username = :username");
  $stmt->execute([':username' => $username]);
  $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

  $userid = $user_data["id"];
  $image = !empty($user_data["avatar"]) ? $user_data["avatar"] : "$url/images/avatar.png";

    function escape_html($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    function get_display_products(PDO $conn, array $fallbackProducts, int $limit)
    {
        try {
            $stmt = $conn->prepare("SELECT id, name, color, size, price, image, sales FROM products ORDER BY sales DESC, id DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $products = [];
        }

        if (empty($products)) {
            return array_slice($fallbackProducts, 0, $limit);
        }

        return $products;
    }

    function product_detail_link(array $product)
    {
        return !empty($product['id']) ? 'product detail.php?id=' . urlencode((string) $product['id']) : 'product detail.php';
    }

    $placeholderTrendingProducts = [
        [
            'name' => 'Nike Air Max',
            'category' => 'Trending',
            'color' => 'Black/White',
            'size' => '10',
            'price' => 120.00,
            'image' => 'assets2/adidasblablabla.png',
            'sales' => 1240,
        ],
        [
            'name' => 'Adidas Ultraboost',
            'category' => 'Trending',
            'color' => 'White/Gray',
            'size' => '9',
            'price' => 150.00,
            'image' => 'assets2/adidasblablabla.png',
            'sales' => 1180,
        ],
        [
            'name' => 'Puma Suede',
            'category' => 'Trending',
            'color' => 'Blue/Red',
            'size' => '11',
            'price' => 85.00,
            'image' => 'assets2/adidasblablabla.png',
            'sales' => 980,
        ],
        [
            'name' => 'New Balance 990',
            'category' => 'Trending',
            'color' => 'Grey/Navy',
            'size' => '10',
            'price' => 175.00,
            'image' => 'assets2/adidasblablabla.png',
            'sales' => 930,
        ],
    ];

    $trendingProducts = get_display_products($conn, $placeholderTrendingProducts, 4);


  try{    
    
    require 'db/action/dbconfig.php';

    $stmt = "SELECT * FROM login WHERE username='$username'";
    
    $query = $conn->query($stmt);
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    // var_dump($result);

    $user_data = array_shift($result);


  } catch(PDOException $e) {

  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laces - Home Page</title>
    <link rel="icon" type="image/png" href="assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/landing.css">
    <link rel="stylesheet" href="css/master.css">
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body id="top">
    <nav class="navbar bg-white border-bottom py-3 sticky-top">
        <div class="container d-flex align-items-center">
            <a href="<?php echo "$url/index.php"; ?>" class="d-flex align-items-center text-decoration-none text-dark fw-bold fs-4 me-3">
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
                <a href="cart/cart.php"><button class="btn p-0 border-0 bg-transparent"><img src="assets2/cart.png" width="20"></button></a>
                <button class="btn p-0 border-0 bg-transparent"><img src="assets2/world.png" width="20"></button>
                <button class="btn p-0 border-0 bg-transparent"><img src="assets2/si--notifications-alt-2-fill.png" width="20"></button>
                <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileMenu"><img src="assets2/gg--profile.png" width="20"></button>
            </div>
        </div>
    </nav>

    <div class="nav-row">
        <div class="nav-container">
            <a href="#top" class="nav-link custom-hover">Home</a>
            <a href="#trending" class="nav-link custom-hover">Trending</a>
            <a href="#categories" class="nav-link custom-hover">Categories</a>
            <a href="product-list.php" class="nav-link custom-hover">Product List</a>
        </div>
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
                <a href="<?php echo "$url/profilepage.php"; ?>" class="list-group-item list-group-item-action border-0 py-3">
                    <i class="bi bi-person-circle me-3"></i> View Profile
                </a>
                <a href="<?php echo "$url/cart/orderHistory.php"; ?>" class="list-group-item list-group-item-action border-0 py-3">
                    <i class="bi bi-box-seam me-3"></i> My Orders
                </a>
                <a href="<?php echo "$url/db/action/logout.php"; ?>" class="list-group-item list-group-item-action border-0 py-3 text-danger">
                    <i class="bi bi-box-arrow-right me-3"></i> Sign Out
                </a>
            </div>
        </div>
    </div>

    <!-- Hero Carousel with 3 slides -->
    <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel" data-bs-interval="3000" data-bs-pause="hover">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets2/shoe.jpg" class="d-block w-100 hero-image" alt="Hero 1">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Step into Style</h5>
                    <p>Discover the latest collection</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets2/abibas.jpg" class="d-block w-100 hero-image" alt="Hero 2">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Comfort Meets Performance</h5>
                    <p>Shop the best sneakers</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets2/asics.png" class="d-block w-100 hero-image" alt="Hero 3">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Limited Editions</h5>
                    <p>Exclusive drops available now</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Trending Section -->
    <section id="trending" class="content-section">
        <div class="section-container">
            <h2 class="section-title">Trending</h2>
            <div class="trending-grid">
                <?php foreach ($trendingProducts as $product): ?>
                <?php
                    $productName = escape_html($product['name'] ?? 'Product Name');
                    $productColor = escape_html($product['color'] ?? 'Black/White');
                    $productSize = escape_html($product['size'] ?? 'N/A');
                    $productPrice = number_format((float) ($product['price'] ?? 0), 2);
                    $productImage = escape_html($product['image'] ?? 'assets2/adidasblablabla.png');
                    // Prepend 'images/' if the path doesn't already contain it
                    if (!empty($product['image']) && strpos($productImage, 'images/') === false && strpos($productImage, 'assets2/') === false) {
                        $productImage = 'images/' . $productImage;
                    }
                    $productSales = number_format((int) ($product['sales'] ?? 0));
                    $productLink = escape_html(product_detail_link($product));
                ?>
                <div class="trending-card">
                    <div class="card-inner">
                        <div class="image-container">
                            <div class="image-placeholder">
                                <img src="<?php echo $productImage; ?>" alt="<?php echo $productName; ?>" class="card-image">
                                <button class="heart-icon">♡</button>
                                <div class="sales-container">
                                    <button class="sales-icon" type="button" aria-label="Sales" style="color:#22c55e;"><i class="bi bi-graph-up-arrow"></i></button>
                                    <span class="sales-number"><?php echo $productSales; ?> sales</span>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $productLink; ?>" style="text-decoration: none;">
                            <div class="product-details">
                                <h3 class="product-name"><?php echo $productName; ?></h3>
                                <p class="product-specs">Color: <?php echo $productColor; ?> | Size: <?php echo $productSize; ?></p>
                                <p class="product-price">₱<?php echo $productPrice; ?></p>
                            </div>
                        </a>
                        <div class="button-group">
                            <button class="add-to-cart-btn" data-product-id="<?php echo (int)$product['id']; ?>">Add to Basket</button>
                            <button class="buy-now-btn">Buy Now</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
<section id="categories" class="content-section">
    <div class="section-container">
        <h2 class="section-title">Categories</h2>
        <div class="categories-grid">
            <a href="product-list.php?category=Running%20Shoes&sort=price&order=ASC" class="category-card text-decoration-none" style="background-image: url('assets2/run.webp');">
                <h3 class="category-name">Running Shoes</h3>
            </a>
            <a href="product-list.php?category=Court%20Shoes&sort=price&order=ASC" class="category-card text-decoration-none" style="background-image: url('assets2/court.jpg');">
                <h3 class="category-name">Court Shoes</h3>
            </a>
            <a href="product-list.php?category=Field%20Shoes&sort=price&order=ASC" class="category-card text-decoration-none" style="background-image: url('assets2/field.webp');">
                <h3 class="category-name">Field Shoes</h3>
            </a>
            <a href="product-list.php?category=Gym%20Shoes&sort=price&order=ASC" class="category-card text-decoration-none" style="background-image: url('assets2/gym.webp');">
                <h3 class="category-name">Gym Shoes</h3>
            </a>
            <a href="product-list.php?category=Sneakers&sort=price&order=ASC" class="category-card text-decoration-none" style="background-image: url('assets2/sneakers.webp');">
                <h3 class="category-name">Sneakers</h3>
            </a>
            <a href="product-list.php?category=Hiking%20Shoes&sort=price&order=ASC" class="category-card text-decoration-none" style="background-image: url('assets2/hike.jpg');">
                <h3 class="category-name">Hiking Shoes</h3>
            </a>
        </div>
    </div>
</section>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="index.php" class="text-white-50 text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="product-list.php" class="text-white-50 text-decoration-none">Product List</a></li>
                        <li class="mb-2"><a href="aboutUs.php" class="text-white-50 text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="profilepage.php" class="text-white-50 text-decoration-none">Profile</a></li>
                        <li class="mb-2"><a href="cart/orderHistory.php" class="text-white-50 text-decoration-none">Order History</a></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Stay Connected</h6>
                    <p class="text-white-50 small">Subscribe to our newsletter</p>
                    <div class="input-group">
                        <input type="email" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Email address">
                        <button class="btn btn-warning btn-sm">Subscribe</button>
                    </div>
                </div>
            </div>
            <hr class="border-secondary mt-4">
            <div class="row">
                <div class="col text-center text-white-50 small py-3">
                    &copy; 2026 Laces. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets2/js/master.js"></script>
</body>
</html>