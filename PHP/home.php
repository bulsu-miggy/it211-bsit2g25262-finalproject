<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lasa Filipina</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .carousel-item {
            height: 350px;
            background-color: #f8f9fa;
        }
        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }
        .carousel {
            border-radius: 25px;
            overflow: hidden;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            position: relative;
        }
        .carousel-caption {
            background: rgba(0,0,0,0.5);
            border-radius: 10px;
            padding: 20px;
        }
        .navbar-nav .nav-link {
            font-size: 24px;
            padding: 8px 20px;
            margin: 0 5px;
        }
        .navbar-nav .nav-link:hover {
            width: 100%;
            background-color: rgba(141, 85, 36, 0.50);
            border-radius: 10px;
        }
        .navbar {
            margin-top: 20px;
            margin-bottom: 50px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 100%;
            position: relative;
            left: 0;
            border-radius: 25px;
        }
        .navbar-collapse {
            justify-content: center;
        }
        .navbar-nav {
            gap: 30px;
        }
        .navbar-brand {
            font-family: 'Times New Roman', serif;
            font-size: 36px;
            font-weight: 700;
            color: #000000;
            margin-right: auto;
        }
        .nav-item {
            font-family: 'Verdana', sans-serif;
            font-size: 16px;
            font-weight: 400;
            color: #000000;
            margin-right: auto;
        }
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
        }
        /* Display.php navbar style */
        .navbar-custom {
            width: 100%;
            max-width: 100%;
            margin: 20px auto 0;
            padding: 12px 20px;
            background: rgba(255, 248, 240, 0.98);
            border-radius: 25px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }
        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .navbar-brand-custom {
            font-family: 'Times New Roman', serif;
            font-size: 28px;
            font-weight: 700;
            color: #2f241b;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        .navbar-brand-custom:hover {
            transform: scale(1.02);
            color: #bc6f3b;
        }
        .nav-links-custom {
            display: flex;
            gap: 30px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .nav-links-custom a {
            text-decoration: none;
            color: #2f241b;
            font-size: 18px;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 8px 16px;
            border-radius: 10px;
        }
        .nav-links-custom a:hover {
            color: #bc6f3b;
            background-color: rgba(188, 111, 59, 0.1);
        }
        .nav-links-custom a.active {
            background-color: #bc6f3b;
            color: white;
        }
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .cart-icon-btn {
            background: none;
            border: none;
            font-size: 28px;
            color: #bc6f3b;
            cursor: pointer;
            padding: 8px;
            text-decoration: none;
            position: relative;
            display: inline-flex;
            align-items: center;
            transition: transform 0.2s;
        }
        .cart-icon-btn:hover {
            transform: scale(1.1);
            color: #a55828;
        }
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        .avatar-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
            background: #f0e2d6;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .avatar-icon:hover {
            transform: scale(1.05);
        }
        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 2px;
            color: #8b735b;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        .spacing {
            height: 20px;
        }
        .custom-card {
            width: 100%;
            border: 2px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #423717;
        }
        .cards-wrapper {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            position: relative;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .card-container,
        .card2-container,
        .card3-container,
        .card4-container {
            padding: 0;
            margin: 0;
            width: 100%;
            flex-shrink: 1;
        }
        .custom-card .btn {
            display: block;
            margin: 0 auto;
            width: fit-content;
            padding: 20px 50px;
            font-size: 16px;
        }
        .custom-card .card-title {
            font-family: 'Times New Roman', serif;
            font-size: 36px;
            font-weight: 700;
            color: #000000;
            margin-right: auto;
            text-align: center;
        }
        .custom-card .card-img-top {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .alert-primary {
            height: 125px;
            width: 100%;
            max-width: 100%;
            margin: 100px auto;
            position: relative;
            background-color: rgba(141, 85, 36, 0.9);
            border: none;
            border-radius: 20px;
        }
        .alert-heading {
            padding-left: 30px;
            left: -1.5%;
            margin-top: 15px;
            font-family: 'Times New Roman', serif;
            font-size: 32px;
            font-weight: 300;
            color: #ffffff;
        }
        .alert-subheading {
            padding-left: 30px;
            left: -1.5%;
            font-family: 'Verdana', sans-serif;
            font-size: 18px;
            color: #ffffff;
            font-weight: 100;
        }
        .text-left {
            padding-left: 15px;
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
            position: relative;
            left: 0;
        }
        .text-left h1 {
            font-family: 'Times New Roman', serif;
            font-size: 52px;
            font-weight: 700;
            color: #000000;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .text-left .lead {
            font-family: 'Verdana', sans-serif;
            font-size: 18px;
            color: #000000;
            font-weight: 300;
        }
        .btn-primary {
            font-family: 'Verdana', sans-serif;
            font-size: 20px;
            width: auto;
            min-width: 200px;
            height: 60px;
            background-color: rgba(141, 85, 36, 0.9);
            border: none;
            border-radius: 20px;
            margin: 2rem auto 0;
            padding: 15px 40px;
            position: relative;
            display: inline-flex;
            justify-content: center;
        }
        .cart-wrapper {
            margin-left: auto;
            display: flex;
            align-items: center;
        }
        .cart-icon-btn {
            background: none;
            border: none;
            font-size: 28px;
            color: #8b5a2b;
            cursor: pointer;
            padding: 8px;
            text-decoration: none;
            position: relative;
            display: inline-flex;
            align-items: center;
            transition: transform 0.2s;
        }
        .cart-icon-btn:hover {
            transform: scale(1.1);
            color: #b87c4f;
        }
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        body {
            background-image: url('../Imges/bg.jpg');
            background-size: cover;
            position: relative;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(107deg, rgba(245, 229, 214, 0.85) 80%, rgba(255, 245, 235, 0.9) 100%);
            z-index: -1;
        }
        /* Card link styling */
        .card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .card-link:hover .custom-card {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #423717;
        }
        @media (max-width: 768px) {
            .cards-wrapper { width: 100%; }
            .navbar { width: 100%; left: 0; }
            .carousel { width: 100%; }
            .text-left { width: 100%; left: 0; padding-left: 15px; }
            .alert-primary { width: 100%; }
            .btn-primary { right: 0; margin-top: 20px; }
        }
    </style>
</head>
<body>
    <nav class="navbar-custom">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <a href="home.php" class="navbar-brand-custom">🇵🇭 Lasa Filipina</a>
            <ul class="nav-links-custom">
                <li><a href="home.php" class="active">Home</a></li>
                <li><a href="dishes.php">Menu</a></li>
                <li><a href="aboutus.php">About Us</a></li>
                <li><a href="contactus.php">Contact Us</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="since-badge">SINCE 1920</span>
                <a href="cart.php" class="cart-icon-btn">
                    <i class="bi bi-cart"></i>
                    <span class="cart-count">0</span>
                </a>
                <div class="dropdown">
                    <button class="avatar-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: transparent; padding: 0;">
                        <img src="../images/logi.png" alt="User Avatar" class="avatar-img">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <li><a class="dropdown-item" href="myaccount.php">My Account</a></li>
                        <li><a class="dropdown-item" href="myorders.php">My Orders</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        <?php else: ?>
                        <li><a class="dropdown-item" href="loginpage.php">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
        </div>
    </nav>

    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="../images/carouselone.jpg" class="d-block w-100" alt="Filipino Foods">
            </div>
            <div class="carousel-item">
                <img src="../images/carouseltwo.jpg" class="d-block w-100" alt="Filipino Delicacies">
            </div>
            <div class="carousel-item">
                <img src="../images/carouselthree.jpg" class="d-block w-100" alt="Lechon">
            </div>
            <!-- <a href="best-sellers.php" class="btn btn-primary">Order Now</a> -->
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <div class="text-left mt-30 p-5">
        <h1>Main Categories</h1>
        <p class="lead">Experience the authentic flavors of the Philippines with us.</p>
    </div>

    <div class="cards-wrapper">
        <!-- Best Sellers Card -->
        <div class="card-container">
            <a href="best-sellers.php" class="card-link">
                <div class="card custom-card">
                    <img src="../images/carouselthree.jpg" class="card-img-top" alt="Best Sellers">
                    <div class="card-body">
                        <h5 class="card-title">Best Sellers</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Dishes Card -->
        <div class="card2-container">
            <a href="dishes.php" class="card-link">
                <div class="card custom-card">
                    <img src="../images/dishes.jpg" class="card-img-top" alt="Dishes">
                    <div class="card-body">
                        <h5 class="card-title">Dishes</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Beverages Card -->
        <div class="card3-container">
            <a href="beverages.php" class="card-link">
                <div class="card custom-card">
                    <img src="../images/beverages.jpg" class="card-img-top" alt="Beverages">
                    <div class="card-body">
                        <h5 class="card-title">Beverages</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Desserts Card -->
        <div class="card4-container">
            <a href="desserts.php" class="card-link">
                <div class="card custom-card">
                    <img src="../images/desserts.jpg" class="card-img-top" alt="Desserts">
                    <div class="card-body">
                        <h5 class="card-title">Desserts</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="alert alert-primary" role="alert">
        <h1 class="alert-heading">Lasa Filipina</h1>
        <p class="alert-subheading">Discover the rich tapestry of Filipino cuisine with us.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>