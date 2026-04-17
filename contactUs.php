<?php
session_start();
require_once 'auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar navbar-expand navbar-light bg-none sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand">
                <img src="images/white.png" alt="SipFlask" height="49">
            </a>

            <div class="collapse navbar-collapse justify-content-end" id="sipNavbar">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item px-0"><a class="nav-link fw-bold" href="index.php">Home</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold" href="listings.php">Listings</a></li>
                    <li class="nav-item px-0"><a class="nav-link fw-bold" href="contactUs.php">Contact Us</a></li>
                    <li class="nav-item ms-tight px-0">
                        <a class="nav-link p-0" href="cart.php"><i class="bi bi-cart4 fs-5"></i></a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item px-0">
                            <a class="nav-link fw-bold" href="#">Hello, <?= htmlspecialchars(getUserName()) ?></a>
                        </li>
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
    
<main>
    <section class="hero text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <img src="images/contactsample.png" class="img-fluid rounded shadow" alt="SipFlask Products" style="height: 500px; object-fit: cover; width: 100%;">
                </div>
                <div class="col-lg-6">
                    <img src="images/hello3.png" class="img-fluid rounded shadow" alt="SipFlask Products" style="height: 500px; object-fit: cover; width: 100%;">
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-12 d-flex justify-content-center align-items-center">
                    <div class="text-center">
                        <h3 class="mb-3 fw-bold" style="color: rgb(255, 255, 255);">We are glad you are here!</h3>
                        <p class="lead mb-3">Have a question or want to chat about our products? We're always here to help.</p>
                        <p class="mb-0 fw-semibold"><strong>Email us @sipflask@gmail.com</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

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
            <div class="footer-bottom ">
                <div class="footer-credit">Website - SipFlask Website</div>
                <div class="footer-copyright">All Rights Reserved © 2026 SipFlask</div>
                <div class="footer-privacy"><a href="#" class="text-white text-decoration-none">Privacy</a></div>
            </div>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>