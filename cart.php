<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportify - Your Cart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <header>
        <div class="container nav-flex">
            <a href="homepage.php" class="logo">SPORTIFY</a>
            <div class="nav-icons">
                <a href="profile.php" style="color: black; margin-right: 15px;"><i class="fas fa-user"></i></a>
                <a href="cart.php" style="color: black; margin-right: 15px;"><i class="fas fa-shopping-cart"></i></a>
                <a href="logout.php" style="color: black;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </header>

    <main class="checkout-container">
        <h1>Your Cart</h1>

        <div class="cart-layout">
            <div class="cart-items-wrapper">

                <div class="cart-item">
                    <div class="item-details">
                        <img src="image/shoe1.webp" alt="Shoe">
                        <div class="item-info">
                            <h4>adidas Men's Barricade 14 Tennis Shoes</h4>
                            <p>Size: 9 US</p>
                            <p>Color: Black</p>
                            <p style="font-weight: 700; font-size: 18px; margin-top: 10px;">&#8369; 9,500.00</p>
                        </div>
                    </div>
                    <div class="item-price-qty">
                        <i class="fas fa-trash" style="color: red; cursor: pointer;"></i>
                        <div class="qty-selector">
                            <span>-</span><span>1</span><span>+</span>
                        </div>
                    </div>
                </div>

                <div class="cart-item">
                    <div class="item-details">
                        <img src="image/tsinelas1.webp" alt="Slides">
                        <div class="item-info">
                            <h4>Adidas Cloudfoam Flex Lounge Rapid Fit</h4>
                            <p>Size: 9 US</p>
                            <p>Color: Black</p>
                            <p style="font-weight: 700; font-size: 18px; margin-top: 10px;">&#8369; 1,950.00</p>
                        </div>
                    </div>
                    <div class="item-price-qty">
                        <i class="fas fa-trash" style="color: red; cursor: pointer;"></i>
                        <div class="qty-selector">
                            <span>-</span><span>1</span><span>+</span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="summary-box">
                <h3>Order Summary</h3>
                <div class="summary-row"><span>Subtotal</span><span style="font-weight:700;">&#8369; 11,450.00</span></div>
                <div class="summary-row"><span>Delivery Fee</span><span style="font-weight:700;">&#8369; 200.00</span></div>

                <div class="promo-section">
                    <input type="text" placeholder="Add promo code">
                    <button class="btn-apply">Apply</button>
                </div>

                <div class="summary-row total"><span>Total</span><span>&#8369; 11,650.00</span></div>

                <button class="btn-checkout" onclick="openCheckout()">Go to Checkout <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
    </main>

    <!-- Order Confirmation Modal -->
    <div id="checkoutModal" class="checkout-overlay" style="display:none;">
        <div class="big-check">&#10003;</div>
        <h2>Order Confirmed</h2>
        <p>Thank you for trusting Sportify.</p>
        <button class="btn-checkout" style="width: auto; padding: 15px 40px;" onclick="closeCheckout()">Close</button>
    </div>

    <script>
        function openCheckout() {
            document.getElementById('checkoutModal').style.display = 'flex';
        }

        function closeCheckout() {
            document.getElementById('checkoutModal').style.display = 'none';
        }
    </script>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="homepage.php" class="logo">SPORTIFY</a>
                    <p>We have clothes that suit your style and which you're proud to wear. From women to men.</p>
                </div>
                <div class="footer-col">
                    <h4>Company</h4>
                    <ul><li><a href="#">About</a></li><li><a href="#">Features</a></li><li><a href="#">Works</a></li><li><a href="#">Career</a></li></ul>
                </div>
                <div class="footer-col">
                    <h4>Help</h4>
                    <ul><li><a href="#">Customer Support</a></li><li><a href="#">Delivery Details</a></li><li><a href="#">Terms &amp; Conditions</a></li><li><a href="#">Privacy Policy</a></li></ul>
                </div>
                <div class="footer-col">
                    <h4>FAQ</h4>
                    <ul><li><a href="#">Account</a></li><li><a href="#">Manage Deliveries</a></li><li><a href="#">Orders</a></li><li><a href="#">Payments</a></li></ul>
                </div>
                <div class="footer-col">
                    <h4>Resources</h4>
                    <ul><li><a href="#">Free eBooks</a></li><li><a href="#">Development Tutorial</a></li><li><a href="#">How to - Blog</a></li><li><a href="#">Youtube Playlist</a></li></ul>
                </div>
            </div>
            <hr class="footer-divider">
            <p class="copyright">Sportify &copy; 2000-2026, All Rights Reserved</p>
        </div>
    </footer>

</body>
</html>
