<!-- navbar.html -->
<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>
<header class="site-header">
    <div class="container">
        <nav class="navbar-custom">
            <div class="navbar-inner">
                <a href="home.php" class="navbar-brand-custom">🇵🇭 Lasa Filipina</a>
                <ul class="nav-links-custom">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="dishes.php">Menu</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                </ul>
                <div class="navbar-actions">
                    <span class="since-badge">SINCE 1920</span>
                    <a href="cart.php" class="cart-icon-btn">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <div class="dropdown">
                        <button class="avatar-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: transparent; padding: 0;">
                            <img src="../images/logi.png" alt="User Avatar" class="avatar-img">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="myaccount.php">My Account</a></li>
                            <li><a class="dropdown-item" href="myorders.php">My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <a href="loginpage.php" class="btn-login">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</header>