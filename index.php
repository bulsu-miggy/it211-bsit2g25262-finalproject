<?php
session_start();
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"> 
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-none sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/white.png" alt="SipFlask" height="49">
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sipNavbar" aria-controls="sipNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="sipNavbar">
                <?php if (isLoggedIn()): ?>
                    <span class="navbar-text text-white fw-bold me-3 d-lg-block" style="font-size: 1.2em;">Hello, <?= htmlspecialchars(getUserName()) ?></span>
                <?php endif; ?>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item px-0"><a class="nav-link fw-bold" href="index.php">Home</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold" href="listings.php">Listings</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold" href="contactUs.php">Contact Us</a></li>
                    <li class="nav-item ms-tight px-0">
                        <a class="nav-link p-0" href="cart.php"><i class="bi bi-cart4 fs-5"></i></a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item px-0">
                                <a class="nav-link fw-bold" href="Admin/index.php">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold" href="login.php">Login</a>
                        </li>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['order_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> <?= $_SESSION['order_success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['order_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['order_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> <?= $_SESSION['order_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['order_error']); ?>
    <?php endif; ?>

    <main>
        <section class="hero text-white">
            <div class="container">
                <div id="bottleCarousel" class="carousel slide bg-white p-100 rounded shadow-lg mx-auto" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="images/sample2.png" class="d-block w-100 rounded" alt="Front">
                        </div>
                        <div class="carousel-item">
                            <img src="images/sample.png" class="d-block w-100 rounded" alt="Side">
                        </div>
                        <div class="carousel-item">
                            <img src="images/sample3.png" class="d-block w-100 rounded" alt="Back">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#bottleCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#bottleCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </section>
    </main>

    <div class="container-fluid py-4 feature-bg">
        <div class="row row-cols-auto g-0 justify-content-center align-items-center text-center text-white">
            <div class="col px-3">
                <div class="feature-box">
                    <i class="bi bi-fire h3"></i>
                    <div class="small-text">KEEPS BEVERAGE <br><strong>HOT</strong></div>
                </div>
            </div>
            <div class="vr opacity-50 my-3" style="height: 40px; background-color: white; width: 1px;"></div>
            <div class="col px-3">
                <div class="feature-box">
                    <i class="bi bi-snow h3"></i>
                    <div class="small-text">KEEPS BEVERAGE <br><strong>COLD</strong></div>
                </div>
            </div>
            <div class="vr opacity-50 my-3" style="height: 40px; background-color: white; width: 1px;"></div>
            <div class="col px-3">
                <div class="feature-box">
                    <div class="h3 mb-0 fw-bold">BPA</div>
                    <div class="small-text">FREE</div>
                </div>
            </div>
            <div class="vr opacity-50 my-3" style="height: 40px; background-color: white; width: 1px;"></div>
            <div class="col px-3">
                <div class="feature-box">
                    <div class="h3 mb-0 fw-bold">18/8</div>
                    <div class="small-text">STAINLESS STEEL</div>
                </div>
            </div>
            <div class="vr opacity-50 my-3" style="height: 40px; background-color: white; width: 1px;"></div>
            <div class="col px-3">
                <div class="feature-box">
                    <i class="bi bi-droplet-half h3"></i>
                    <div class="small-text">DISHWASHER <br><strong>SAFE</strong></div>
                </div>
            </div>
            <div class="vr opacity-50 my-3" style="height: 40px; background-color: white; width: 1px;"></div>
            <div class="col px-3">
                <div class="feature-box">
                    <i class="bi bi-car-front h3"></i>
                    <div class="small-text">CUP HOLDER <br>COMPATIBLE</div>
                </div>
            </div>
        </div>
    </div>

    <section class="arrivals py-3 text-center">
        <div class="container">
            <h2 class="text-white border-bottom pb-3 mb-3 fw-bold">Featured Products</h2>
            <div class="row g-3 justify-content-center">
                
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product-card text-start">
                        <div class="product-img-container">
                            <img src="images/16oz/grapejuice.png" alt="Grape Juice" class="img-fluid">
                        </div>
                        <div class="product-details">
                            <div class="d-flex align-items-center mb-1">
                                <span class="category">CLASSIC</span>
                                <span class="color-swatch pink-border" style="background-color: #7D92E3;"></span>
                                <span class="color-swatch pink-border" style="background-color: #D7D9B1;"></span>
                                <span class="color-swatch pink-border" style="background-color: #8B6F8A;"></span>
                                <span class="color-swatch pink-border" style="background-color: #e83e8c;"></span>
                                <span class="color-swatch pink-border" style="background-color: #DBD0E6;"></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="product-title">Grape Juice Flask 16oz</h5>
                                <span class="price">P850</span>
                            </div>
                            <div class="rating">
                                <span class="stars">★★★★☆</span>
                                <span class="count">(143)</span>
                            </div>
                            <p class="product-desc">Your perfect daily companion. Vacuum-insulated, leak-proof, and built to last.</p>
                            <a href="viewdetails.php?size=16oz" class="btn btn-pink w-100">VIEW DETAILS</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product-card text-start">
                        <div class="product-img-container">
                            <img src="images/25oz/keylime.png" alt="Key Lime" class="img-fluid">
                        </div>
                        <div class="product-details">
                            <div class="d-flex align-items-center mb-1">
                                <span class="category">CLASSIC</span>
                                <span class="color-swatch pink-border" style="background-color: #E2E48E;"></span>
                                <span class="color-swatch pink-border" style="background-color: #B7A982;"></span>
                                <span class="color-swatch pink-border" style="background-color: #5D6BCF;"></span>
                                <span class="color-swatch pink-border" style="background-color: #8F7996;"></span>
                                <span class="color-swatch pink-border" style="background-color: #D8CDE0;"></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="product-title">KeyLime Flask 25oz</h5>
                                <span class="price">P890</span>
                            </div>
                            <div class="rating">
                                <span class="stars">★★★★★</span>
                                <span class="count">(256)</span>
                            </div>
                            <p class="product-desc">Ditch the plastic. A sustainable and stylish way to enjoy your favorite drinks.</p>
                            <a href="viewdetails.php?size=25oz" class="btn btn-pink w-100">VIEW DETAILS</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product-card text-start">
                        <div class="product-img-container">
                            <img src="images/32oz/noriflask.png" alt="Noriflask" class="img-fluid">
                        </div>
                        <div class="product-details">
                            <div class="d-flex align-items-center mb-1">
                                <span class="category">CLASSIC</span>
                                <span class="color-swatch pink-border" style="background-color: #1A2421;"></span>
                                <span class="color-swatch pink-border" style="background-color: #3D4626;"></span>
                                <span class="color-swatch pink-border" style="background-color: #6B8077;"></span>
                                <span class="color-swatch pink-border" style="background-color: #A68E34;"></span>
                                <span class="color-swatch pink-border" style="background-color: #e83e8c;"></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="product-title">Nori Flask 32oz</h5>
                                <span class="price">P950</span>
                            </div>
                            <div class="rating">
                                <span class="stars">★★★★☆</span>
                                <span class="count">(89)</span>
                            </div>
                            <p class="product-desc">Built for the rugged outdoors. Sweat-proof design with a durable finish.</p>
                            <a href="viewdetails.php?size=32oz" class="btn btn-pink w-100">VIEW DETAILS</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-center mt-4">
            <a href="listings.php" class="btn btn-pink btn-lg">SHOP MORE</a>
        </div>
    </section>

    <section class="container-fluid py-3" style="background-color: #36a094;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="color: #ffffff;">Choose Your Flask Size</h2>
                <div class="footer-logo text-white border-bottom pb-1 mb-3 fw-bold d-flex justify-content-center align-items-center"></div>

                <div class="row justify-content-center g-4 text-center">
                    <div class="col-6 col-md-3">
                        <a href="listings.php?size=16oz" class="text-decoration-none size-item">
                            <div class="product-img-container d-flex align-items-end justify-content-center mb-3" style="height: 250px; background: none; box-shadow: none;">
                                <img src="images/14oz.png" alt="16oz" class="img-fluid" style="max-height: 160px;">
                            </div>
                            <div class="size-badge fw-bold">16OZ</div>
                        </a>
                    </div>

                    <div class="col-6 col-md-3">
                        <a href="listings.php?size=25oz" class="text-decoration-none size-item">
                            <div class="product-img-container d-flex align-items-end justify-content-center mb-3" style="height: 250px; background: none; box-shadow: none;">
                                <img src="images/14oz.png" alt="25oz" class="img-fluid" style="max-height: 200px;">
                            </div>
                            <div class="size-badge fw-bold">25OZ</div>
                        </a>
                    </div>

                    <div class="col-6 col-md-3">
                        <a href="listings.php?size=32oz" class="text-decoration-none size-item">
                            <div class="product-img-container d-flex align-items-end justify-content-center mb-3" style="height: 250px; background: none; box-shadow: none;">
                                <img src="images/14oz.png" alt="32oz" class="img-fluid" style="max-height: 240px;">
                            </div>
                            <div class="size-badge fw-bold">32OZ</div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-logo text-white border-bottom pb-1 mb-3 fw-bold d-flex justify-content-center align-items-center"></div>
        </div>
    </section>

     <footer class="custom-footer">
        <div class="container">
            <div class="footer-logo text-white border-bottom pb-1 mb-5 fw-bold d-flex justify-content-center align-items-center">
                <img src="images/exactlogo.png" alt="SipFlask" height="55" class="me-2">
                SipFlask
            </div>
            <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="listings.php">Listings</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="contactUs.php">Contact Us</a></li>
            </ul>
            <div class="footer-tagline">#KeepItSipFlask</div>
            <div class="footer-socials">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-tiktok"></i></a>
            </div>
            <div class="footer-bottom">
                <div class="footer-credit">Website - SipFlask Website</div>
                <div class="footer-copyright">All Rights Reserved © 2026 SipFlask</div>
                <div class="footer-privacy"><a href="#" class="text-white text-decoration-none">Privacy</a></div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>

</body>

</html>