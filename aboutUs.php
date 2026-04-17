<?php
  require 'config.php';
  require 'db/action/dbconfig.php';

  session_start();

  if (!isset($_SESSION["username"]) || !isset($_SESSION["password"]))
  {
    header('Location: loginpage.php');
    exit();
  } 

  $username = $_SESSION["username"];
  $stmt = $conn->prepare("SELECT * FROM login WHERE username = :username");
  $stmt->execute([':username' => $username]);
  $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

  $userid = $user_data["id"];
  $image = !empty($user_data["avatar"]) ? $user_data["avatar"] : "$url/images/avatar.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Laces</title>
    <link rel="icon" type="image/png" href="assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/aboutUs.css">
</head>

<body>
    <!-- Navbar -->
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

    <!-- Profile Menu Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="profileMenu" aria-labelledby="profileMenuLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="profileMenuLabel">My Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="text-center mb-4">
                <img src="assets2/gg--profile.png" width="70" class="mb-2 opacity-75">
                <h6 class="fw-bold">Welcome back, <?php echo htmlspecialchars($username); ?>!</h6>
                <p class="small text-muted">Manage your account</p>
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

    <!-- Menu Row -->
    <div class="text-center mt-3 mb-4">
        <a href="index.php" class="mx-3 text-dark text-decoration-none custom-hover">Home</a>
        <a href="product-list.php?sort=sales&order=DESC" class="mx-3 text-dark text-decoration-none custom-hover">Trending</a>
        <a href="product-list.php" class="mx-3 text-dark text-decoration-none custom-hover">Product List</a>
        <a href="aboutUs.php" class="mx-3 text-dark text-decoration-none custom-hover fw-bold">About Us</a>
    </div>

    <!-- Hero Section -->
    <div class="about-hero">
        <h1>About Laces</h1>
        <p>Your Premier Destination for Athletic & Lifestyle Footwear</p>
    </div>

    <!-- Our Story Section -->
    <div class="about-section">
        <h2 class="section-title">Our Story</h2>
        <div class="mission-container" style="margin-top: 3rem;">
            <div class="mission-text">
                <h2>Founded with a Passion for Footwear</h2>
                <p>
                    Laces was born from a simple idea: to bring the world's best athletic and lifestyle footwear 
                    to customers in one convenient place. What started as a small vision has grown into a thriving 
                    e-commerce platform trusted by thousands of shoe enthusiasts across the region.
                </p>
                <p>
                    We believe that the right pair of shoes can make all the difference—whether you're hitting the 
                    track, playing on the court, or simply stepping out in style. That's why we curate every product 
                    with care, ensuring quality, performance, and style in every pair.
                </p>
            </div>
            <div class="mission-icon">
                <i class="bi bi-shop"></i>
            </div>
        </div>
    </div>

    <!-- Our Mission & Vision -->
    <div class="about-section">
        <h2 class="section-title">Our Mission & Vision</h2>
        <div class="values-grid" style="margin-top: 3rem;">
            <div class="value-card">
                <i class="bi bi-bullseye"></i>
                <h3>Our Mission</h3>
                <p>To provide exceptional footwear selection with unmatched customer service, making premium shoes accessible to everyone.</p>
            </div>
            <div class="value-card">
                <i class="bi bi-eye"></i>
                <h3>Our Vision</h3>
                <p>To become the leading online footwear destination, recognized for innovation, quality, and customer satisfaction.</p>
            </div>
            <div class="value-card">
                <i class="bi bi-heart"></i>
                <h3>Our Passion</h3>
                <p>We're passionate about helping our customers find the perfect shoes that match their lifestyle and aspirations.</p>
            </div>
        </div>
    </div>

    <!-- Core Values -->
    <div class="about-section">
        <h2 class="section-title">Our Core Values</h2>
        <div class="values-grid" style="margin-top: 3rem;">
            <div class="value-card">
                <i class="bi bi-check-circle"></i>
                <h3>Quality</h3>
                <p>We only offer authentic, high-quality products from trusted brands to ensure customer satisfaction.</p>
            </div>
            <div class="value-card">
                <i class="bi bi-person-check"></i>
                <h3>Integrity</h3>
                <p>Transparency and honesty guide every transaction. We believe in doing business the right way.</p>
            </div>
            <div class="value-card">
                <i class="bi bi-lightning"></i>
                <h3>Innovation</h3>
                <p>We continuously improve our platform and services to provide the best shopping experience.</p>
            </div>
            <div class="value-card">
                <i class="bi bi-people"></i>
                <h3>Community</h3>
                <p>We build lasting relationships with our customers and are committed to supporting our community.</p>
            </div>
        </div>
    </div>

    <!-- statistics -->
    <div class="about-section">
        <h2 class="section-title">By The Numbers</h2>
        <div class="stats-container" style="margin-top: 3rem;">
            <div class="stat-card">
                <div class="stat-number">50K+</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">1000+</div>
                <div class="stat-label">Product Styles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">50+</div>
                <div class="stat-label">Premium Brands</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Customer Support</div>
            </div>
        </div>
    </div>

    <!--  Journey -->
    <div class="about-section">
        <h2 class="section-title">Our Journey</h2>
        <div class="timeline" style="margin-top: 3rem;">
            <div class="timeline-item">
                <div class="timeline-year">2018</div>
                <div class="timeline-content">
                    <h3>The Beginning</h3>
                    <p>Laces was founded with a mission to revolutionize the footwear shopping experience online.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-year">2019</div>
                <div class="timeline-content">
                    <h3>Expansion</h3>
                    <p>We expanded our inventory to include over 500 unique shoe styles from top global brands.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-year">2021</div>
                <div class="timeline-content">
                    <h3>Milestone Achievement</h3>
                    <p>Reached 25,000 satisfied customers and launched our mobile app for seamless shopping.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-year">2023</div>
                <div class="timeline-content">
                    <h3>Market Leader</h3>
                    <p>Became the #1 choice for footwear enthusiasts with 50,000+ active customers in our community.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-year">2024</div>
                <div class="timeline-content">
                    <h3>Global Vision</h3>
                    <p>Expanding internationally and continuing to innovate our platform with AI-powered recommendations.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="about-section">
        <h2 class="section-title">Our Team</h2>
        <div class="team-grid" style="margin-top: 3rem; margin-bottom: 4rem;">
            <div class="team-member">
                <img src="images/allen.jpg" alt="Allen Drew" class="team-member-image-img">
                <div class="team-member-info">
                    <h4>Allen Drew</h4>
                    <p>Founder & CEO</p>
                    <span>Ensures the company's vision is realized</span>
                </div>
            </div>
            <div class="team-member">
                <img src="images/Aaron.jpg" alt="Aaron Tayug" class="team-member-image-img">
                <div class="team-member-info">
                    <h4>Aaron Tayug</h4>
                    <p>Chief Operations Officer</p>
                    <span>Expert in supply chain management</span>
                </div>
            </div>
            <div class="team-member">
                <img src="images/Jared.jpg" alt="Jared Almagro" class="team-member-image-img">
                <div class="team-member-info">
                    <h4>Jared Almagro</h4>
                    <p>Head of Technology</p>
                    <span>1 year in e-commerce development</span>
                </div>
            </div>
            <div class="team-member">
                <img src="images/Reinell.jpg" alt="Reinell Silverio" class="team-member-image-img">
                <div class="team-member-info">
                    <h4>Reinell Silverio</h4>
                    <p>Customer Experience Lead</p>
                    <span>Dedicated to customer satisfaction</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4">
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
                    <p class="text-white-50 small">Subscribe to our newsletter for exclusive offers</p>
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
